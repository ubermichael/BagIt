<?php

namespace Nines\BagIt\Adapter;

use SplFileInfo;

abstract class BagItAdapter {
	
	private $fileInfo;
	
	public static function open($path) {
		$fileInfo = new SplFileInfo($path);
		if($fileInfo->isDir()) {
			$adapter = new DirectoryAdapter();
		} else {
			$adapter = new PharDataAdapter();
		}
		$adapter->fileInfo = $fileInfo;
		return $adapter;
	}
	
	abstract function getDeclaration();
	
	abstract function getDataFiles();
	
	abstract function getPayloadManifests();
	
	abstract function getTagManifests();
	
	abstract function getMetadata();
	
	abstract function getFetch();
	
	abstract function getTagFiles();
}