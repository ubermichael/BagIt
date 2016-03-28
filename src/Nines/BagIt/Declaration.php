<?php

namespace Nines\BagIt;

use SplFileInfo;

class Declaration {
	
	private $version;
	
	private $encoding;
	
	public function __construct() {
		$this->version = Bag::DEFAULT_BAGIT_VERSION;
		$this->encoding = Bag::DEFAULT_BAGIT_ENCODING;
	}
	
	public function setVersion($version) {
		if(! preg_match("/^\d+\.\d+$/", $version)) {
			throw new BagException("Malformed BagIt version: {$version}");
		}
		$this->version = $version;
	}
	
	public function getVersion() {
		return $this->version;
	}
	
	public function setEncoding($encoding) {
		if(!in_array($encoding, mb_list_encodings())) {
			throw new BagException("Unsupported encoding {$encoding} in this PHP.");
		}
		$this->encoding = $encoding;
	}
	
	public function getEncoding() {
		return $this->encoding;
	}
	
	public function getFilename() {
		return "bagit.txt";
	}
	
	public function read(SplFileInfo $data) {
		$fh = $data->openFile('r');
		
		$versionLine = $fh->fgets();
		$versionMatch = array();
		if(! preg_match("/^BagIt-Version:\s*(\d+\.\d+)$/", $versionLine, $versionMatch)) {
			throw new BagException("Malformed BagIt version in {$versionLine}");
		}
		$this->version = $versionMatch[1];
		
		$encodingLine = $fh->fgets();
		$encodingMatches = array();
		if(! preg_match("/^Tag-File-Character-Encoding:\s*([a-zA-Z0-9]]+)", $encodingLine, $encodingMatches)) {
			throw new BagException("Malformed tag encoding in {$encodingLine}");
		}
		$this->encoding = $encodingMatches[1];
	}
	
	public function serialize() {
		$str = "";
		$str .= "BagIt-Version: {$this->version}\n";
		$str .= "Tag-File-Character-Encoding: {$this->encoding}\n";
		return $str;
	}
}