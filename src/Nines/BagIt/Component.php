<?php

namespace Nines\BagIt;

use Psr\Log\LoggerAwareInterface;
use SplFileInfo;

abstract class Component implements LoggerAwareInterface {
	
	use BagLogger;
	
	abstract function getFilename();
	
	abstract function read(SplFileInfo $data);
	
	abstract function serialize();
}
