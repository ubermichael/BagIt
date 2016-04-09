<?php

/* 
 * The MIT License
 *
 * Copyright 2016 michael.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Nines\BagIt\Component;

use Nines\BagIt\BagException;
use SplFileObject;

/**
 * Optional bag metadata.
 * 
 * This class maps keys (called 'labels' in the spec) to one or more values. The
 * order of the keys and values is preserved except that the keys are all
 * grouped together.
 */
class Metadata extends Component {

	/**
	 * Metadata keys (called labels in the spec) and values.
	 * 
	 * @var array
	 */
	private $data;
	
	/**
	 * Construct a new empty metadata object.
	 */
	public function __construct() {
		parent::__construct();
		$this->data = array();
	}

	/**
	 * Get the file name for a metadata file.
	 * 
	 * @return string the fixed value "bag-info.txt"
	 */
	public function getFilename() {
		return "bag-info.txt";
	}
	
	/**
	 * Add some meta data. 
	 * 
	 * In theory, a value can be anything that can be 
	 * stringified by a call to __toString(), but that's not supported.
	 * 
	 * @param string $key the key to hold the data
	 * 
	 * @param string|array $value one or more metadata values
	 * 
	 * @throws BagException if $key is null or empty
	 */
	public function addData($key, $value) {
		if(!is_string($key)) {
			throw new BagException("Metadata keys must be strings.");
		}
		if($key === '') {
			throw new BagException("Metadata keys cannot be the empty string.");
		}
		if(! array_key_exists($key, $this->data)) {
			$this->data[$key] = $value;
			return;
		}
		if(! is_array($this->data[$key])) {
			$this->data[$key] = array($this->data[$key]);
		}
		if(is_array($value)) {
			$this->data[$key] = array_merge($this->data[$key], $value);
		} else {
			$this->data[$key][] = $value;
		}
	}
	
	/**
	 * Set some meta data. If $key had any data associated with it, that data
	 * will be lost.
	 * 
	 * In theory, a value can be anything that can be 
	 * stringified by a call to __toString(), but that's not supported.
	 * 
	 * @param string $key the key to hold the data
	 * 
	 * @param string|array $value one or more metadata values
	 * 
	 * @throws BagException if $key is null or empty
	 */
	public function setData($key, $value) {
		if(!is_string($key)) {
			throw new BagException("Metadata keys and values must be strings.");
		}
		if($key === '') {
			throw new BagException("Metadata keys cannot be the empty string.");
		}
		$this->data[$key] = $value;
	}
	
	/**
	 * Get the metadata associated with $key
	 * 
	 * @param string $key
	 * @return string|array
	 */
	public function getData($key) {
		return $this->data[$key];
	}
	
	/**
	 * Check that the metadata has a $key.
	 * 
	 * @param string $key
	 * @return boolean true if the key has data associated with it
	 */
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}
	
	/**
	 * Return a list of metadata keys.
	 * 
	 * @return string[]
	 */
	public function listKeys() {
		return array_keys($this->data);
	}
	
	/**
	 * Count the keys in the metadata
	 * 
	 * @return int the number of keys
	 */
	public function countKeys() {
		return count(array_keys($this->data));
	}
	
	/**
	 * Count the values in the metadata which are associated with $key.
	 * 
	 * @param string $key
	 * 
	 * @return int the number of values associated with $key
	 */
	public function countValues($key) {
		if(! array_key_exists($key, $this->data)) {
			return 0;
		}
		if(! is_array($this->data[$key])) {
			return 1;
		}
		return count($this->data[$key]);
	}
	
	/**
	 * Remove all metadata.
	 */
	public function clearData() {
		$this->data = array();
	}
	
	/**
	 * Remove one metadata key and all associated values.
	 * 
	 * @param type $key
	 */
	public function unsetData($key) {
		if(array_key_exists($key, $this->data)) {
			unset($this->data[$key]);
		}
	}
	
	/**
	 * Read a metadata object, converting from $encoding to UTF-8 internally. 
	 * 
	 * This implementation understands the optional continuation lines as 
	 * defined in the BagIt spec.
	 * 
	 * @param SplFileObject $data
	 * @param string $encoding
	 * @throws BagException
	 */
	public function read(SplFileObject $data, $encoding = 'UTF-8') {
		$content = '';
		while(! $data->eof()) {
			$line = $data->fgets();
			$content .= $line;
		}
		if($encoding !== 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}
		preg_replace("/\n\s+/", "", $content);
		foreach(explode("\n", $content) as $line) {
			if(! $line) {
				continue;
			}
			$matches = array();
			if(! preg_match('/^(?<label>[a-zA-Z0-9-]+)\s*:\s*(?<value>.*)$/', $line, $matches)) {
				throw new BagException("Malformed metadata line {$line}.");
			}
			$this->addData($matches['label'], $matches['value']);
		}
	}
	
	/**
	 * Serialize a key/value pair, word wrapping the result at 79 characters if
	 * possible.
	 * 
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
    private function serializePair($key, $value) {
        $line = "{$key}: {$value}";
        $wrapped = wordwrap($line, 79, "\n ");
        return $wrapped . "\n";
    }

	/**
	 * Serialize the metadata to a string and return it.
	 * 
	 * @return string
	 */
	public function serialize() {
		$str = '';
		foreach($this->data as $key => $value) {
			if(is_array($value)) {
				foreach($value as $v) {
					$str .= $this->serializePair($key, $v);
				}
			} else {
				$str .= $this->serializePair($key, $value);
			}
		}
		return $str;
	}
}