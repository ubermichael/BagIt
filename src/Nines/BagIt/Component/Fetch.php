<?php

namespace Nines\BagIt\Component;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use SplFileInfo;

class Fetch extends Component {

	private $data;

	private $certPath;

	public function __construct() {
		$this->data = array();
        $this->certPath = true;
	}
	
	public function getFilename() {
		return 'fetch.txt';
	}
	
	public function setCertPath($path) {
        $this->certPath = $path;
	}

	public function getCertPath() {
        return $this->certPath;
	}
	
	public function clear() {
        $this->data = array();
	}
	
	public function addData($url, $length, $filename) {
		$this->data[] = new FetchItem($url, $length, $filename);
	}
	
	public function addItem(FetchItem $item) {
		$this->data = $item;
	}

	public function read(SplFileObject $fh = null) {
		if($fh === null) {
			return;
		}
		while($line = $fh->fgets()) {
			list($url, $length, $filename) = split('/ /', $line, 3);
			$this->addData($url, $length, $filename);
		}
	}

	public function serialize() {
		$str = '';
		foreach($this->data as $item) {
			$str .= join(' ', array($item->getUrl(), $item->getSize(), $item->getFilename()));
			$str .= "\n";
		}
		return $str;
	}
}
