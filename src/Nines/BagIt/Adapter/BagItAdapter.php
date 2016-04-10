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

namespace Nines\BagIt\Adapter;

use Nines\BagIt\BagException;
use Nines\BagIt\Component\Component;
use Nines\BagIt\Component\Declaration;
use Nines\BagIt\Component\Fetch;
use Nines\BagIt\Component\Manifest\PayloadManifest;
use Nines\BagIt\Component\Manifest\TagManifest;
use Nines\BagIt\Component\Metadata;
use Nines\FileFind\Finder;
use Nines\FileFind\FinderAdapter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileInfo;
use SplFileObject;

/**
 * Base class for adapters to read and write to the file system.
 */
abstract class BagItAdapter implements LoggerAwareInterface {

	/**
	 * @var SplFileInfo
	 */
	protected $base;

	/**
	 *
	 * @var Finder
	 */
	protected $finder;

	/**
	 * @var LoggerInterface 
	 */
	protected $logger;

	public function __construct(SplFileInfo $base) {
		$this->base = $base;
		$this->finder = new Finder(FinderAdapter::getAdapter($base->getPathname()));
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
	 * Open a bag.
	 * 
	 * @param string $path the bag path
	 * 
	 * @return PharDataAdapter
	 */
	public static function open($path) {
		$fileInfo = new SplFileInfo($path);
		if ($fileInfo->isDir()) {
			$adapter = new DirectoryAdapter($fileInfo);
		} else {
			$adapter = new PharDataAdapter($fileInfo);
		}
		return $adapter;
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
		return $this->readComponent(Declaration::class, '/bagit.txt', 'Cannot find the required bag declaration file.');
	}

	public function getPayloadFiles() {
		$fileInfos = $this->finder->find();
		$payloadFiles = [];
		foreach($fileInfos as $info) {
			$payloadFiles[] = $info->getPathname();
		}
		return $payloadFiles;
	}

	public function getPayloadManifests() {
		$manifests = array();
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^manifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		$fileInfos = $this->finder->find($callback, array(
			'depth' => 1,
		));
		foreach($fileInfos as $info) {
			$manifest = $this->readComponent(PayloadManifest::class, $info->getBasename());
			$manifests[$manifest->getAlgorithm()] = $manifest;
		}
		return $manifests;
	}

	public function getTagManifests() {
		$manifests = array();
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^tagmanifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		$fileInfos = $this->finder->find($callback, array(
			'depth' => 1,
		));
		foreach($fileInfos as $info) {
			$manifest = $this->readComponent(TagManifest::class, $info->getBasename());
			$manifests[$manifest->getAlgorithm()] = $manifest;
		}
		return $manifests;
	}

	public function getMetadata() {
		return $this->readComponent(Metadata::class, '/bag-info.txt');
	}

	public function getFetch() {
		return $this->readComponent(Fetch::class, '/fetch.txt');
	}

	public function getTagFiles() {
		return $this->finder->find(null, array(
			'exclude' => array('data'),
		));
	}
}
