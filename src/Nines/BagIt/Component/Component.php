<?php

/* 
 * The MIT License
 *
 * Copyright 2016 Michael Joyce <ubermichael@gmail.com>.
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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileObject;

/**
 * Base class for all Bag components, which provides basic logging and a few
 * other common functions.
 */
abstract class Component implements LoggerAwareInterface {
	
	/**
	 * @var LoggerInterface 
	 */
	protected $logger;
	
	/**
	 * Build a new, empty declaration
	 */
	public function __construct() {
		$this->logger = new NullLogger();
	}
	
	/**
	 * Set the logger for the declaration.
	 * 
	 * @param LoggerInterface $logger the PSR3-compatible logger.
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * Get the file name for a component.
	 * 
	 * @return string the file name for this component
	 */
	abstract function getFilename();

	/**
	 * Read a component's data from the file system or other source.
	 */
	abstract function read(SplFileObject $data);

	/**
	 * Serialize the component's data into a string. Does not write to the 
	 * file system.
	 * 
	 * @return string the serialized data.
	 */
	abstract function serialize();
}