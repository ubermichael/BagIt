<?php

namespace Nines\BagIt;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use Psr\Log\LoggerInterface;

class Fetch {

    private $data;

    private $certPath;

    /**
     * PSR-3 compatible logger
     *
     * @var LoggerInterface
     */
    private $logger;

    const FILENAME = 'fetch.txt';

    public static function readFetch($path) {
        $content = file_get_contents($path . DIRECTORY_SEPARATOR . self::FILENAME);
        $lines = explode("\n", $content);
        $fetch = new Fetch();
        foreach($lines as $line) {
            list($url, $size, $path) = preg_split('/\s+/', $line, 3);
            $fetch->setEntry($url, $size, $path);
        }
        return $fetch;
    }

    public function __construct() {
        $this->data = array();
        $this->certPath = true;
    }

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

    public function setCertPath($path) {
        $this->certPath = $path;
    }

    public function getCertPath() {
        return $this->certPath;
    }

    public function clear() {
        $this->data = array();
    }

    public function setEntry($url, $size, $path) {
        $this->data[$url] = array(
            'size' => $size,
            'path' => $path,
        );
    }

    public function hasEntry($url) {
        return array_key_exists($url, $this->data);
    }

    public function removeEntry($url) {
        if(array_key_exists($url, $this->data)) {
            unset($this->data[$url]);
        }
    }

    public function getEntrySize($url) {
        if(array_key_exists($url, $this->data)) {
            return $this->data[$url]['size'];
        }
        return null;
    }

    public function getEntryPath($url) {
        if(array_key_exists($url, $this->data)) {
            return $this->data[$url]['path'];
        }
        return null;
    }

    public function serialize() {
        $content = '';
        foreach($this->data as $url => $entry) {
            $content = "{$url} {$entry['size']} {$entry['path']}\n";
        }
        return $content;
    }

    public function writeFetch($path) {
        $fileName = $path . DIRECTORY_SEPARATOR . self::FILENAME;
        file_put_contents($fileName, $this->serialize());
    }

    protected function checkDownloadSize($client, $url, $entry) {
        if($entry['size'] !== '-') {
            $response = $client->head($url);
            $length = $response->getHeader('Content-Length');
            if($length !== $entry['size']) {
                $this->log("Download file size does not match. Expected {$entry['size']}, got {$length}", array(), 'warning');
            }
        }
    }

    public function download($path) {
        foreach($this->data as $url => $entry) {
            $filePath = $path . DIRECTORY_SEPARATOR . $entry['path'];
            $handle = fopen($filePath, 'w');
            $stream = Stream::factory($handle);
            $client = new Client();

            $this->checkDownloadSize($client, $url, $entry);

            $options = array(
                'verify' => $this->certPath,
                'save_to' => $stream,
            );
            $client->get($url, $options);
        }
    }
}
