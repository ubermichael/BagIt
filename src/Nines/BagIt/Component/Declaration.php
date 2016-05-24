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

namespace Nines\BagIt\Component;

use Nines\BagIt\Bag;
use Nines\BagIt\BagException;
use SplFileInfo;
use SplFileObject;

class Declaration extends Component {

	/**
	 * @var string
	 */
	private $version;
	
	/**
	 * @var string the tag file encoding.
	 */
	private $encoding;

	/**
	 * Build a new, empty declaration
	 */
	public function __construct() {
		parent::__construct();
		$this->version = Bag::DEFAULT_VERSION;
		$this->encoding = Bag::DEFAULT_ENCODING;
	}

	/**
	 * Set the BagIt spec version for the bag. The version must match the 
	 * regular expression `^\d+\.\d+$`
	 * 
	 * @param string $version Spec version
	 * @throws BagException if the version isn't formatted correctly.
	 */
	public function setVersion($version) {
		if (!preg_match("/^\d+\.\d+$/", $version)) {
			throw new BagException("Malformed BagIt version: {$version}");
		}
		$this->version = $version;
	}

	/**
	 * Get the BagIt spec version.
	 * 
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Set the tag file encoding. Must be one of the encodings reported by 
	 * `mb_list_encodings()`.
	 * 
	 * @see mb_list_encodings()
	 * 
	 * @param string $encoding the encoding name.
	 * 
	 * @throws BagException if the encoding isn't supported.
	 */
	public function setEncoding($encoding) {
		if (!in_array($encoding, mb_list_encodings())) {
			throw new BagException("Unsupported encoding {$encoding} in this PHP.");
		}
		$this->encoding = $encoding;
	}

	/**
	 * Get the tag file encoding.
	 * 
	 * @return type
	 */
	public function getEncoding() {
		return $this->encoding;
	}

	/**
	 * Get the file name or the declaration.
	 * 
	 * @return string always "bagit.txt" as required by the specification.
	 */
	public function getFilename() {
		return "bagit.txt";
	}

	/**
	 * Read a bag declaration.
	 * 
	 * @param SplFileInfo $data the file to read.
	 * 
	 * @throws BagException if the version or character encoding are bad.
	 */
	public function read(SplFileObject $data) {
		$versionLine = $data->fgets();
		$versionMatch = array();
		if (!preg_match("/^BagIt-Version:\s*(\d+\.\d+)$/", $versionLine, $versionMatch)) {
			throw new BagException("Malformed BagIt version in {$versionLine}");
		}
		$this->version = $versionMatch[1];

		$encodingLine = $data->fgets();
		$encodingMatches = array();
		if (!preg_match("/^Tag-File-Character-Encoding:\s*([a-zA-Z0-9-]+)$/", $encodingLine, $encodingMatches)) {
			throw new BagException("Malformed tag encoding in {$encodingLine}");
		}
		$this->encoding = $encodingMatches[1];
		while( ! $data->eof()) {
			$content = trim($data->fgets());
			if(strlen($content) > 0) {
				throw new BagException("Extra junk in declaration.");
			}
		}
	}

	/**
	 * Serialize this declaration into a string and return it. This method does 
	 * not write to the file system.
	 * 
	 * @return string formatted declaration.
	 */
	public function serialize() {
		$str = "";
		$str .= "BagIt-Version: {$this->version}\n";
		$str .= "Tag-File-Character-Encoding: {$this->encoding}\n";
		return $str;
	}
}
