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
use Nines\BagIt\Util\FetchItem;
use SplFileObject;

/**
 * Implementation of the optional fetch.txt part of a bag.
 */
class Fetch extends Component {

	/**
	 * Array of the fetch items.
	 *
	 * @var FetchItem[]
	 */
	private $data;

	/**
	 * Build an empty Fetch list.
	 */
	public function __construct() {
		parent::__construct();
		$this->data = array();
	}

	/**
	 * Get the fetch list filename.
	 * 
	 * @return string The fixed value 'fetch.txt'.
	 */
	public function getFilename() {
		return 'fetch.txt';
	}

	/**
	 * Remove all the fetch items.
	 */
	public function clear() {
        $this->data = array();
	}
	
	/**
	 * Add a new fetch item. Really just a wrapper around
	 * `$this->addItem(new FetchItem($url, $length, $filename)`
	 * 
	 * @param string $url
	 * @param null|int $length
	 * @param string $path Should start with 'data/' but that will be added
	 * if it does not.
	 */
	public function addData($url, $length, $path) {
		$item = new FetchItem();
		$item->setUrl($url);		
		$item->setSize($length);
		$item->setPath($path);
		$this->addItem($item);
	}
	
	/**
	 * Add a fetch item.
	 * 
	 * @param FetchItem $item
	 */
	public function addItem(FetchItem $item) {
		$path = $item->getPath();
		if(!$path) {
			throw new BagException('A fetch item path is required.');
		}
		if(!array_key_exists($path, $this->data)) {
			$this->data[$path] = array();
		}
		$this->data[$path][] = $item;
	}
	
	/**
	 * Count the remote files.
	 * 
	 * @return type
	 */
	public function countFiles() {
		return count($this->listFiles());
	}
	
	/**
	 * List the remote file paths in the fetch file.
	 * 
	 * @return string[]
	 */
	public function listFiles() {
		return array_keys($this->data);
	}

	/**
	 * Count the URLs for one path.
	 * 
	 * @param type $path
	 * @return int
	 */
	public function countUrls($path) {
		if(!array_key_exists($path, $this->data)) {
			return 0;
		}
		return count($this->data[$path]);
	}
	
	/**
	 * get the URLs for one path.
	 * 
	 * @param type $path
	 * @return array
	 */
	public function getUrls($path) {
		if(!array_key_exists($path, $this->data)) {
			return array();
		}
		$urls = array();
		foreach($this->data[$path] as $item) {
			$urls[] = $item->getUrl();
		}
		return $urls;
	}
	
	/**
	 * Get the file size for an item listed in the fetch.txt file.
	 * 
	 * If $url is null, then the first line from the fetch file for the URL
	 * will be used. Otherwise the path and URL must match.
	 * 
	 * @param string $path
	 * @param null|string $url 
	 * 
	 * @return null|int the size or null if the path's size is unknown or
	 * if the path is not in the fetch file.
	 */
	public function getSize($path, $url = null) {
		if(! array_key_exists($path, $this->data)) {
			return null;
		}
		if($url === null) {
			return $this->data[$path][0]->getSize();
		}
		foreach($this->data[$path] as $item) {
			if($item->getUrl() === $url) {
				return $item->getSize();
			}
		}
		return null;
	}
	
	/**
	 * Read the fetch data, optionally converting the encoding.
	 * 
	 * @param SplFileObject $data
	 * @param string $encoding an encoding supported by mb_convert_encoding()
	 */
	public function read(SplFileObject $data, $encoding = 'UTF-8') {
		while(! $data->eof()) {
			$line = trim($data->fgets());
			if(! $line) {
				continue;
			}
			if($encoding !== 'UTF-8') {
				$line = mb_convert_encoding($line, 'UTF-8', $encoding);
			}
			list($url, $length, $filename) = explode(' ', $line, 3);
			$this->addData($url, $length, $filename);
		}
	}

	/**
	 * Serialize the fetch data into a string suitable for a fetch.txt file. 
	 * Does not write the the data to a file.
	 * 
	 * @return string
	 */
	public function serialize() {
		$str = '';
		foreach($this->data as $item) {
			$str .= join(' ', array($item->getUrl(), $item->getSize(), $item->getPath()));
			$str .= "\n";
		}
		return $str;
	}
}
