<?php

namespace Nines\BagIt\Adapter;

use SplFileInfo;

class DirectoryAdapter extends BagItAdapter {
	
	public function getPayloadFiles() {
		
	}

	public function getDeclaration() {
		return $this->getFile($this->base->getPathname() . '/' . 'fetch.txt', 'Cannot find bag declaration bagit.txt');
	}

	public function getFetch() {
		return $this->getFile($this->base->getPathname() . '/' . 'fetch.txt');
	}

	public function getMetadata() {
		return $this->getFile($this->base->getPathname() . '/' . 'bag-info.txt');
	}

	public function getPayloadManifests() {
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^manifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		return $this->finder->find($callback, array(
			'depth' => 1,
		));
	}

	public function getTagFiles() {
		return $this->finder->find(null, array(
			'exclude' => array('data'),
		));
	}

	public function getTagManifests() {
		$callback = function(SplFileInfo $fi) {
			return preg_match('/^tagmanifest-[a-zA-Z0-9-]*.txt$/', $fi->getBasename());
		};
		return $this->finder->find($callback, array(
			'depth' => 1,
		));
	}

}
