<?php

namespace Nines\BagIt;

use Psr\Log\LoggerInterface;

/**
 * Logging trait, provides functionality to use a PSR-4 logger in a class.
 */
trait Logging {

    /**
     * PSR-3 compatible logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Add a logger to the file finder. It must be a PSR-3 compatible
     * logger, like monolog/monolog.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Get the logger. It must be a PSR-3 compatible
     * logger, like monolog/monolog.
     *
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Log a message to the logger, if there is one.
     *
     * @param string $message
     * @param string $context
     * @param string $level
     */
    public function log($message, $context = array(), $level = 'info') {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

}
