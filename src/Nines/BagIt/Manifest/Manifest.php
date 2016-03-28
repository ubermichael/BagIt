<?php

namespace Nines\BagIt\Manifest;

use Nines\BagIt\BagComponent;
use Nines\BagIt\BagException;
use Psr\Log\LoggerInterface;
use SplFileInfo;

abstract class Manifest extends BagComponent {
	
	/**
	 * The hash algorithm for this manifest file.
	 * @var string
	 */
	protected $algorithm;

	/**
	 * An array mapping file paths in the payload directory to their hashes.
	 * @var array
	 */
	protected $hashes;
	
	public function __construct(LoggerInterface $logger = null) {
		$this->hashes = array();
		$this->logger = $logger;
		$this->algorithm = null;
	}
	
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	public function setAlgorithm($algorithm) {
		if(!in_array($algorithm, hash_algos())) {
			throw new BagException("Unsupported hash algorithm {$algorithm}");
		}
		$this->algorithm = $algorithm;
	}
	
	public function hasFile($file) {
		return array_key_exists($file, $this->hashes);
	}
	
	public function updateFile($file, $hash) {
		$this->hashes[$file] = $hash;
	}
	
	public function addFile($file, $hash = null) {
		if($hash === null) {
			$hash = hash_file($this->algorithm, $file);
		}
		$this->hashes[$file] = $hash;
	}

	public function isComplete() {
		$complete = true;
		foreach(array_keys($this->hashes) as $file) {
			if(!file_exists($file)) {
				$this->log('error', "Bag is incomplete: payload file {$file} is missing.");
				$complete = false;
			}
		}
		return $complete;
	}
	
	public function isValid() {
		$valid = true;
		foreach(array_keys($this->hashes) as $file => $hash) {
			if(!file_exists($file)) {
				$valid = false;
				$this->log('error', "Bag is incomplete: payload file {$file} is missing.");
				continue;
			}
			if(hash_file($this->algorithm, $file) !== $hash) {
				$valid = false;
				$this->log('error', "Bag is invalid: payload file {$file} checksum {$this->algorithm} does not match.");
			}
		}
		return $valid;
	}
	
	/**
	 * @todo updat this to support really big files via streaming/chunking.
	 */
	public function update() {
		foreach(array_keys($this->hashes) as $file => $hash) {
			if(!file_exists($file)) {
				$this->log('warning', "{$file} is not in the bag and will be removed from the manifest.");
				unset($this->hashes[$file]);
				continue;
			}
			$computed = hash_file($this->algorithm, $file);
			if($computed !== $hash) {
				$this->log('warning', "Hash for {$file} does not match and will be updated.");
				$this->hashes[$file] = $computed;
			}
		}
	}
	
	public function read(SplFileInfo $data) {
		$fh = $data->openFile('r');
		while($line = $fh->fgets()) {
			list($hash, $file) = explode(' ', $line);
			$this->addFile($file, $hash);
		}
	}
	
	public function serialize() {
		$str = "";
		foreach(array_keys($this->hashes) as $file => $hash) {
			$str .= "{$hash} {$file}\n";
		}
		return $str;
	}
}