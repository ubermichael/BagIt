<?php

namespace Nines\BagIt;

use SplFileInfo;

class Metadata extends Component {
	
	private $data;
	
	public function __construct() {
		$this->data = array();
	}
	
	public function getFilename() {
		return "bag-info.txt";
	}
	
	public function addData($key, $value) {
		if(! array_key_exists($key, $this->data)) {
			$this->data[$key] = $value;
			return;
		}
		if(! is_array($this->data[$key])) {
			$this->data[$key] = array($this->data[$key]);
		}
		$this->data[$key][] = $value;
	}
	
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function getData($key) {
		return $this->data[$key];
	}
	
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}
	
	public function clearData() {
		$this->data = array();
	}
	
	public function unsetData($key) {
		if(array_key_exists($key, $this->data)) {
			unset($this->data[$key]);
		}
	}
	
	public function read(SplFileInfo $data) {
		$fh = $data->openFile('r');
		$content = $fh->fread($fh->getSize());
		preg_replace("/\n\s+/", "", $content);
		foreach(split("/\n/", $content) as $line) {
			$matches = array();
			if(! preg_match('/^(?<label>[a-zA-Z0-9-]+)\s*:\s*(?<value>.*)$/', $content, $matches)) {
				throw new BagException("Malformed metadata line {$line}.");
			}
			$this->addData($matches['label'], $matches['value']);
		}
	}
	
    private function serializePair($key, $value) {
        $line = "{$key}: {$value}";
        $wrapped = wordwrap($line, 79, "\n ");
        return $wrapped . "\n";
    }

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