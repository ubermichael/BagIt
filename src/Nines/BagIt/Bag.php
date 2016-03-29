<?php

namespace Nines\BagIt;

use Nines\BagIt\Adapter\BagItAdapter;
use Nines\BagIt\Manifest\Manifest;
use Psr\Log\LoggerInterface;
use SplFileInfo;

class Bag extends Component {
	
	const DEFAULT_BAGIT_VERSION = "0.98";

	const DEFAULT_BAGIT_ENCODING = "UTF-8";
	
	/**
	 * @var BagItAdapter
	 */
	private $adapter;
	
	/**
	 * @var Declaration;
	 */
	private $declaration;
	
	/**
	 * @var string
	 */
	private $base;
	
	/**
	 * @var Manifest[]
	 */
	private $manifests;
	
	/**
	 * @var Metadata
	 */
	private $metadata;
	
	/**
	 * @var Fetch
	 */
	private $fetch;
	
	public function __construct() {
		$this->declaration = new Declaration();		
		$this->manifests = array();
		$this->metadata = new Metadata();
		$this->fetch = new Fetch();
	}

	public static function open($path) {
		$bag = new Bag();
		$bag->base = $path;
		$bag->adapter = BagItAdapter::open($path);
		
		$bag->declaration = new Declaration();
		$bag->declaration->read($bag->adapter->getDeclaration());
		
		$bag->metadata = new Metadata();
		$bag->metadata->read($bag->adapter->getMetadata());
		
		$bag->fetch = new Fetch();
		$bag->fetch->read($bag->adapter->getFetch());
		
		return $bag;
	}
	
	public function setLogger(LoggerInterface $logger) {
		
	}

	public function getFilename() {
		
	}

	public function read(SplFileObject $data) {
		
	}

	/**
	 * @return SplFileInfo[]
	 */
	public function getContents() {
		return $this->adapter->getContents();
	}
	
	public function isComplete() {
		
	}
	
	public function isValid() {
		// if( ! $this->isComplete()) { return false;}s
	}
	
	public function serialize() {
		
	}
}