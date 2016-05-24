<?php

namespace Nines\BagIt;

use Nines\BagIt\Component\Metadata;
use PHPUnit_Framework_TestCase;
use SplFileObject;

class MetadataTest extends PHPUnit_Framework_TestCase {
	
	protected $metadata;
	
	protected function setUp(){
		$this->metadata = new Metadata();
	}
	
	public function testDefaults() {
		$this->assertEquals(0, $this->metadata->countKeys());
	}

	public function testGetFilename() {
		$this->assertEquals('bag-info.txt', $this->metadata->getFilename());
	}
	
	public function testAddData() {
		$this->metadata->addData('foo', 'bar');
		$this->assertEquals(1, $this->metadata->countKeys());
	}
	
	public function testMultipleKeys() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('blix', 'baz');
		$this->assertEquals(2, $this->metadata->countKeys());
		$this->assertEquals(1, $this->metadata->countValues('foo'));
	}
	
	public function testDuplicateKeys() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('foo', 'baz');
		$this->assertEquals(1, $this->metadata->countKeys());
		$this->assertEquals(2, $this->metadata->countValues('foo'));
	}
	
	public function testManyKeys() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('foo', 'baz');
		$this->metadata->addData('foo', 'blur');
		$this->assertEquals(1, $this->metadata->countKeys());
		$this->assertEquals(3, $this->metadata->countValues('foo'));
	}

	/**
	 * @expectedException  \Nines\BagIt\BagException
	 */
	public function testAddDataKeyException() {
		$this->metadata->addData(array(), '');
	}
	
	/**
	 * @expectedException \Nines\BagIt\BagException
	 */
	public function testAddDataEmptyKey() {
		$this->metadata->addData('', 'boomerang');
	}
	
	public function testSetData() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->setData('foo', 'bean');
		$this->assertEquals(1, $this->metadata->countKeys());
		$this->assertEquals('bean', $this->metadata->getData('foo'));
	}
	
	/**
	 * @expectedException  \Nines\BagIt\BagException
	 */
	public function testSetDataKeyException() {
		$this->metadata->setData(array(), '');
	}
	
	/**
	 * @expectedException  \Nines\BagIt\BagException
	 */
	public function testSetDataEmptyKey() {
		$this->metadata->setData('', 'boomerang');
	}
	
	public function testGetData() {
		$this->metadata->setData('foo', 'bean');
		$this->assertEquals('bean', $this->metadata->getData('foo'));
	}
	
	public function testHasData() {
		$this->metadata->setData('foo', 'bean');
		$this->assertTrue($this->metadata->hasData('foo'));
	}
	
	public function testCountOneKey() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->setData('foo', 'bean');
		$this->assertEquals(1, $this->metadata->countKeys());
	}
	
	public function testCountMultipleKeys() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('foo', 'ddfs');
		$this->metadata->setData('blup', 'bean');
		$this->metadata->addData('foo', 'sdfs');
		$this->assertEquals(2, $this->metadata->countKeys());
	}
	
	public function testUnsetData() {
		$this->metadata->addData('foo', 'ddfs');
		$this->metadata->setData('blup', 'bean');
		$this->metadata->unsetData('foo');
		$this->assertEquals(1, $this->metadata->countKeys());
	}
	
	public function testRead() {
		$fi = new SplFileObject('php://temp', 'r+');
		$fi->fwrite("foo: bar\n");
		$fi->rewind();
		$this->metadata->read($fi);
		$this->assertEquals(1, $this->metadata->countKeys());
	}
	
	public function testReadMultipleKeys() {
		$fi = new SplFileObject('php://temp', 'r+');
		$fi->fwrite("foo: bar\n");
		$fi->fwrite("loo: bax\n");
		$fi->rewind();
		$this->metadata->read($fi);
		$this->assertEquals(2, $this->metadata->countKeys());		
	}
	
	public function testReadMultipleValues() {
		$fi = new SplFileObject('php://temp', 'r+');
		$fi->fwrite("foo: bar\n");
		$fi->fwrite("foo: bax\n");
		$fi->rewind();
		$this->metadata->read($fi);
		$this->assertEquals(1, $this->metadata->countKeys());		
	}
	
	public function testReadWithEncoding() {
		$content = "east: 東\n";
		$encoding = 'UTF-16';
		$utf16 = mb_convert_encoding($content, $encoding, 'UTF-8');
		$fi = new SplFileObject('php://temp', 'r+');
		$fi->fwrite($utf16);
		$fi->rewind();
		$this->metadata->read($fi, $encoding);
		$this->assertEquals('東', $this->metadata->getData('east'));
	}
	
	/**
	 * @expectedException  \Nines\BagIt\BagException
	 */
	public function testBadMetadata() {
		$fi = new SplFileObject('php://temp', 'r+');
		$fi->fwrite("foo-bar\n");
		$fi->rewind();
		$this->metadata->read($fi);
	}

	public function testSerialize() {
		$this->metadata->addData('foo', 'bar');
		$this->assertEquals("foo: bar\n", $this->metadata->serialize());	
	}
	
	public function testSerializeMultipleKeys() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('bax', 'bli');
		$this->assertEquals("foo: bar\nbax: bli\n", $this->metadata->serialize());	
	}
	
	public function testSerializeMultipleValues() {
		$this->metadata->addData('foo', 'bar');
		$this->metadata->addData('foo', 'bli');
		$this->assertEquals("foo: bar\nfoo: bli\n", $this->metadata->serialize());	
	}
}