<?php

/*
 * The MIT License
 *
 * Copyright 2016 Michael Joyce <ubermichael@gmail.com>.
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

namespace Nines\BagIt\Adapter;

use DirectoryIterator;
use Nines\BagIt\BagException;
use Nines\BagIt\Component\Component;
use Nines\BagIt\Component\Declaration;
use Nines\BagIt\Component\Fetch;
use Nines\BagIt\Component\Manifest\PayloadManifest;
use Nines\BagIt\Component\Manifest\TagManifest;
use Nines\BagIt\Component\Metadata;
use Nines\FileFind\Finder;
use Nines\FileFind\FinderAdapter;
use PharData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SplFileObject;

/**
 * Base class for adapters to read and write to the file system.
 */
class BagItAdapter implements LoggerAwareInterface {

	/**
	 * @var SplFileInfo
	 */
	protected $base;

	/**
	 * @var LoggerInterface 
	 */
	protected $logger;

	public function __construct(SplFileInfo $base) {
		if($base->isDir()) {
			$this->base = $base;
		} else {
			$pd = new PharData($base->getRealPath());
			$this->base = $pd->getFileInfo();
		}
		$this->logger = new NullLogger();
	}

	/**
	 * Set the logger for the declaration.
	 * 
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * Read a component from a bag.
	 * 
	 * @param string $class The class name of a component
	 * @param string $path The component's path in the bag
	 * @param null|string $message If not null and the path doesn't exist, then throw a BagException with this message.
	 * 
	 * @return Component
	 */
	protected function readComponent($class, $path, $message = null) {
		$fileInfo = $this->getFile($this->base->getPathname() . '/' . $path, $message);
		$component = new $class();
		$component->setLogger($this->logger);
		if($fileInfo !== null) {
			$component->read($fileInfo->openFile('r'));
		}
		return $component;
	}
	
	/**
	 * Get a file inside a bag.
	 * 
	 * @param string $path
	 * 
	 * @param null|string $message Optional exception message to throw if the file is not available.
	 * 
	 * @return SplFileObject
	 * 
	 * @throws BagException if $message is not null and the file is not found.
	 */
	protected function getFile($path, $message = null) {
		if (file_exists($path)) {
			return new SplFileObject($path);
		}
		if ($message) {
			throw new BagException($message);
		}
		return null;
	}

	/**
	 * @return SplFileInfo[]
	 */
	public function getContents() {
		return $this->finder->find();
	}

	/**
	 * Get the bag declaration.
	 * 
	 * @return Declaration
	 * 
	 * @throws BagException if the declaration does not exist.
	 */
	public function getDeclaration() {
		return $this->readComponent('Nines\BagIt\Component\Declaration', '/bagit.txt', 'Cannot find the required bag declaration file.');
	}

	public function getPayloadFiles() {
		$payload = array();
		$rdi = new RecursiveDirectoryIterator($this->base->getPathname() . '/data');
		$rii = new RecursiveIteratorIterator($rdi);
		foreach($rii as $filename => $current) {
			if($current->isDir()) {
				continue;
			}
			$payload[] = $current->getPathname();
		}
		return $payload;
	}

	public function getPayloadManifests() {
		$manifests = array();
		$di = new DirectoryIterator($this->base->getPathname());
		foreach($di as $fileInfo) {
			if($fileInfo->isDot()) {
				continue;
			}
			if(preg_match('/^manifest-[a-zA-Z0-9-]*.txt$/', $fileInfo->getBasename())) {
				$manifest = $this->readComponent('Nines\BagIt\Component\Manifest\PayloadManifest', $fileInfo->getBasename());
				$manifests[$manifest->getAlgorithm()] = $manifest;
			}
		}
		return $manifests;
	}

	public function getTagManifests() {
		$manifests = array();
		$di = new DirectoryIterator($this->base->getPathname());
		foreach($di as $fileInfo) {
			if($fileInfo->isDot()) {
				continue;
			}
			if(preg_match('/^tagmanifest-[a-zA-Z0-9-]*.txt$/', $fileInfo->getBasename())) {
				$manifest = $this->readComponent('Nines\BagIt\Component\Manifest\TagManifest', $fileInfo->getBasename());
				$manifests[$manifest->getAlgorithm()] = $manifest;
			}
		}
		return $manifests;
	}

	public function getMetadata() {
		return $this->readComponent('Nines\BagIt\Component\Metadata', '/bag-info.txt');
	}

	public function getFetch() {
		return $this->readComponent('Nines\BagIt\Component\Fetch', '/fetch.txt');
	}

	public function getTagFiles() {
		return $this->finder->find(null, array(
			'exclude' => array('data'),
		));
	}
}
