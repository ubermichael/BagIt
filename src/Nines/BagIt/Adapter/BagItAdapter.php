<?php

namespace Nines\BagIt\Adapter;

use Nines\BagIt\BagException;
use Nines\FileFind\Finder;
use Nines\FileFind\FinderAdapter;
use Psr\Log\LoggerAwareInterface;
use SplFileInfo;
use SplFileObject;
use Nines\BagIt\BagLogger;

abstract class BagItAdapter implements LoggerAwareInterface {
	
	use BagLogger;

	/**
	 * @var SplFileInfo
	 */
	protected $base;
	
	/**
	 *
	 * @var Finder
	 */
	protected $finder;
	
	public function __construct(SplFileInfo $base) {
		$this->base = $base;
		$this->finder = new Finder(FinderAdapter::getAdapter($base->getPathname()));
	}
	
	public static function open($path) {
		$fileInfo = new SplFileInfo($path);
		if($fileInfo->isDir()) {
			$adapter = new DirectoryAdapter($fileInfo);
		} else {
			$adapter = new PharDataAdapter($fileInfo);
		}
		return $adapter;
	}
	
	protected function getFile($path, $message = null) {
		if(file_exists($path)) {
			return new SplFileObject($path);
		}
		if($message) {
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
	
	abstract function getDeclaration();
	
	abstract function getPayloadFiles();
	
	abstract function getPayloadManifests();
	
	abstract function getTagManifests();
	
	abstract function getMetadata();
	
	abstract function getFetch();
	
	abstract function getTagFiles();
}