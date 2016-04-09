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

namespace Nines\BagIt\Util;

use Nines\BagIt\BagException;

/**
 * An item in a bag fetch.txt file.
 */
class FetchItem {
	
	/**
	 * The item's URL
	 *
	 * @var string
	 */
	private $url;

	/**
	 * The items size in bytes, or null.
	 *
	 * @var null|int
	 */
	private $size;
	
	/**
	 * The local, in-bag file name. It should start with '/data'
	 *
	 * @var type 
	 */
	private $path;

	/**
	 * Set the download URL for the item.
	 * 
	 * @param type $url
	 * 
	 * @throws BagException for bad URLs.
	 */
	public function setUrl($url) {
		if(! filter_var($url, FILTER_VALIDATE_URL)) {
			throw new BagException("Invalid URL {$url}.");
		}
		$this->url = $url;
	}
	
	/**
	 * Get the download URL for the item.
	 * 
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * Set the size of an item. Accepts '-' as a null/unknown value following
	 * the BagIt spec.
	 * 
	 * @param int|string $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}
	
	/**
	 * Get the size of an item. Following the BagIt spec, returns '-' for
	 * unknown sizes.
	 * 
	 * @return int|string
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * Set the filename of an item.
	 * 
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * Get the filename of an item.
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}
