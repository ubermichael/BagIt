<?php

namespace Nines\BagIt\Adapter;

use SplFileInfo;
use SplFileObject;

class PharDataAdapter extends BagItAdapter {
	
	private $phar;
	
	public function __construct(SplFileInfo $base) {
		parent::__construct($base);
		$this->phar = new \PharData($base->getPathname());
	}
	
	/**
	 * @return SplFileObject[]
	 */
	public function getPayloadFiles() {
		
	}

	/**
	 * @return SplFileObject
	 */
	public function getDeclaration() {
		return $this->getFile($this->phar->getPathname() . '/' . 'fetch.txt', 'Cannot find the required bag declaration bagit.txt');
	}

	/**
	 * @return null|SplFileObject
	 */
	public function getFetch() {
		return $this->getFile($this->phar->getPathname() . '/fetch.txt');
	}

	/**
	 * @return null|SplFileObject
	 */
	public function getMetadata() {
		return $this->getFile($this->phar->getPathname() . '/bag-info.txt');
	}

	/**
	 * @return SplFileObject[]
	 */
	public function getPayloadManifests() {
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^manifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		return $this->finder->find($callback, array(
			'depth' => 2,
		));
	}


	/**
	 * @return SplFileObject[]
	 */
	public function getTagFiles() {
		return $this->finder->find(null, array(
			'exclude' => array('data'),
		));
	}

	/**
	 * @return SplFileObject[]
	 */
	public function getTagManifests() {
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^tagmanifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		return $this->finder->find($callback, array(
			'depth' => 2,
		));
	}
}