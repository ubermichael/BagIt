<?php

namespace Nines\BagIt;

use PHPUnit_Framework_TestCase;
use SplFileObject;

class DeclarationTest extends PHPUnit_Framework_TestCase {
	
	protected $decl;
	
	protected function setUp(){
		$this->decl = new Declaration();
	}
	
	public function testGetFilename() {
		$this->assertEquals('bagit.txt', $this->decl->getFilename());
	}
	
	public function testDefaultVersion() {
		$this->assertEquals(Bag::DEFAULT_BAGIT_VERSION, $this->decl->getVersion());
	}
	
	public function testDefaultEncoding() {
		$this->assertEquals(Bag::DEFAULT_BAGIT_ENCODING, $this->decl->getEncoding());
	}
	
	public function testSetVersion() {
		$this->decl->setVersion("1.23");
		$this->assertEquals("1.23", $this->decl->getVersion());
	}
	
	/**
	 * @expectedException \Nines\BagIt\BagException
	 */
	public function testSetBadVersion() {
		$this->decl->setVersion("5");
	}
	
	public function testSetEncoding() {
		$this->decl->setEncoding('ISO-8859-1');
		$this->assertEquals('ISO-8859-1', $this->decl->getEncoding());
	}
	
	/**
	 * @expectedException \Nines\BagIt\BagException
	 */
	public function testSetBadEncoding() {
		$this->decl->setEncoding('LSKDJFLSDJF');
	}

	public function testSerialize() {
		$this->decl->setEncoding("UTF-16");
		$this->decl->setVersion("1.23");
		$this->assertEquals("BagIt-Version: 1.23\nTag-File-Character-Encoding: UTF-16\n", $this->decl->serialize());
	}
	
}