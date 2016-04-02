<?php

namespace Nines\BagIt\Component;

class FetchItem {
	
	private $url;
	
	private $size;
	
	private $filename;
	
	public function __construct() {
		
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setSize($size) {
		$this->size = $size;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	public function getFilename() {
		return $this->filename;
	}
}
