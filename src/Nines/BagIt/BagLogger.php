<?php

namespace Nines\BagIt;

use Psr\Log\LoggerInterface;

trait BagLogger {

	/**
	 * @var LoggerInterface
	 */
	private $logger;
		
	public function __construct() {
		$this->logger = null;
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	public function getLogger() {
		return $this->logger;
	}
	
	public function log($level, $message, array $context = array()) {
		if($this->logger) {
			$this->logger->log($level, $message, $context);
		}
	}
}
