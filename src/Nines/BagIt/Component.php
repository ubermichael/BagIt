<?php

namespace Nines\BagIt;

use SplFileObject;
use Psr\Log\LoggerAwareInterface;

abstract class Component implements LoggerAwareInterface {
	
	use BagLogger;
	
	abstract function getFilename();
	
	abstract function read(SplFileObject $data);
	
	abstract function serialize();
}
