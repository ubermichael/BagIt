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

namespace Nines\BagIt\Component\Manifest;

use Nines\BagIt\BagException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileInfo;
use SplFileObject;

/**
 * Abstract implementation of a BagIt manifest.
 */
abstract class Manifest implements LoggerAwareInterface {
	
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
	
	/**
	 * @var LoggerInterface 
	 */
	protected $logger;
	
	/**
	 * Return the filename for the manifest. Something like manifest-sha1.txt
	 * or tagmanifest-md5.txt.
	 * 
	 * @return string the file name.
	 */
	abstract public function getFilename();
	
	/**
	 * Construct a manifest. Subclasses should call parent::__construct().
	 */
	public function __construct() {
		$this->hashes = array();
		$this->algorithm = null;
		$this->logger = new NullLogger();
	}

	/**
	 * Set the logger for the manifest.
	 * 
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * Get the algorithm this manifest uses.
	 * 
	 * @return string
	 */
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	/**
	 * Set the algorithm this manifest should use.
	 * 
	 * @param string $algorithm
	 * @throws BagException if the algorithm isn't listed in hash_algos().
	 */
	public function setAlgorithm($algorithm) {
		if(!in_array($algorithm, hash_algos())) {
			throw new BagException("Unsupported hash algorithm '{$algorithm}'");
		}
		$this->algorithm = $algorithm;
	}
	
	/**
	 * Return true if $file is listed in the manifest.
	 * 
	 * @param string $path path relative to the bag.
	 * @return type
	 */
	public function hasFile($path) {
		return array_key_exists($path, $this->hashes);
	}
	
	/**
	 * Return the checksum for the file, as listed in the manifest.
	 * 
	 * @param type $path
	 */
	public function getHash($path) {
		if(! $this->hasFile($path)) {
			return null;
		}
		return $this->hashes[$path];
	}
	
	/**
	 * Returns an array of the files listed in the manifest.
	 * @return string[]
	 */
	public function listFiles() {
		return array_keys($this->hashes);
	}
	
	/**
	 * Counts the files listed in the manifest.
	 * 
	 * @return int
	 */
	public function countFiles() {
		return count($this->listFiles());
	}
	
	/**
	 * Updates the checksum for a file. This method does not write changes to
	 * disk.
	 * 
	 * @param string $path relative path to the file
	 * @param string $hash the checksum. If null, then the checksum is computed.
	 */
	public function updateFile($path, $hash = null) {
		if($hash === null) {
			$hash = hash_file($this->algorithm, $path);
		}
		$this->hashes[$path] = $hash;
	}
	
	/**
	 * Add a file to the manifest. Paths which start with an asterisk will have
	 * the asterisk removed.
	 * 
	 * @param string $path relative path to the file
	 * @param string $hash the checksum. If null, then the checksum is computed.
	 * @throws BagItException if the file already exists.
	 */
	public function addFile($path, $hash = null) {
		if(substr($path, 0, 1) === '*') {
			$path = substr($path, 1);
		}
		if(array_key_exists($path, $this->hashes)) {
			throw new BagException("File '{$path}' already exists in {$this->getFilename()}.");
		}
		if($hash === null) {
			$hash = hash_file($this->algorithm, $path);
		}
		$this->hashes[$path] = $hash;
	}

	/**
	 * Checks that all the files listed in the manifest exist in the bag.
	 * @return boolean true if the manifest is complete.
	 */
	public function isComplete() {
		$complete = true;
		foreach(array_keys($this->hashes) as $path) {
			if(!file_exists($path)) {
				$complete = false;
				$this->logger->warning(
					"File {path} is listed in manifest {filename} but does not exist in the bag.",
					array(
						'path' => $path,
						'filename' => $this->getFilename()
					)
				);
			}
		}
		return $complete;
	}
	
	/**
	 * Check that a manifest is valid.
	 * 
	 * @return boolean true if the bag is valid.
	 */
	public function isValid() {
		$valid = true;
		foreach(array_keys($this->hashes) as $path => $hash) {
			if(!file_exists($path)) {
				$valid = false;
				$this->logger->warning(
					"File {path} is listed in manifest {filename} but does not exist in the bag.",
					array(
						'path' => $path,
						'filename' => $this->getFilename()
					)
				);
				continue;
			}
			if(hash_file($this->algorithm, $path) !== $hash) {
				$valid = false;
				$this->logger->error(
					"File {path} checksum in {filename} does not match the file.",
					array(
						'path' => $path,
						'filename' => $this->getFilename()
					)
				);
			}
		}
		return $valid;
	}
	
	/**
	 * Update the hashes in the manifest.
	 * 
	 * @todo updat this to support really big files via streaming/chunking.
	 */
	public function update() {
		foreach(array_keys($this->hashes) as $path => $hash) {
			if(!file_exists($path)) {
				$this->logger->warning(
					"File {path} is not in the bag and will be removed from manifest {filename}.", 
					array(
						'path' => $path,
						'filename' => $this->getFilename()
                    )
                );
				unset($this->hashes[$path]);
				continue;
			}
			$computed = hash_file($this->algorithm, $path);
			if($computed !== $hash) {
				$this->logger->notice(
					"Hash for {path} does not match {filename} and will be updated.", 
					array(
						'path' => $path,
						'filename' => $this->getFilename()
                    )
                );
				$this->hashes[$path] = $computed;
			}
		}
	}
	
	/**
	 * Read a manifest file. 
	 * 
	 * Blank lines are silently ignored.
	 * 
	 * @param SplFileObject $data the file to read.
	 * @param string $encoding an encoding supported by mb_convert_encoding()
	 */
	public function read(SplFileObject $data, $encoding = 'UTF-8') {
		$matches = array();
		if( ! preg_match('/^(?:tag)?manifest-([a-zA-Z0-9-]+)\.txt$/', $data->getBasename(), $matches)) {
			throw new BagException("Cannot determine manifest algorithm from filename '{$data->getBasename()}'");
		}
		$this->setAlgorithm($matches[1]);
		while(! $data->eof()) {
			$line = trim($data->fgets());
			if(! $line) {
				continue;
			}
			if($encoding !== 'UTF-8') {
				$line = mb_convert_encoding($line, 'UTF-8', $encoding);
			}
			list($hash, $file) = preg_split('/\s+/', $line, 2);
			$this->addFile($file, $hash);
		}
	}
	
	/**
	 * Serialize this manifest data into a string. This method does not change
	 * the file content on disk.
	 * 
	 * @return string the serialized hash data
	 */
	public function serialize() {
		$str = "";
		foreach(array_keys($this->hashes) as $file => $hash) {
			$str .= "{$hash} {$file}\n";
		}
		return $str;
	}
}
