<?php

namespace Nines\BagIt;

use SplFileInfo;

class Manifest implements BagItComponent {

    use Logging;

    private $data;

    private $algorithm;
    
    private $isTagManifest;

    public function filename() {
        $filename = '';
        if($this->isTagManifest) {
            $filename = 'tag';
        }
        $filename .= "manifest-{$this->algorithm}.txt";
        return $filename;
    }

    /**
     * @param string $path
     * @throws BagItException
     */
    public static function read($path) {
        $fileInfo = new SplFileInfo($path);
        
        $matches = array();
        if( ! preg_match('/^(?:tag)?manifest-([a-zA-Z0-9]+)\.txt$/', $fileInfo->getBasename(), $matches)) {
            throw new BagItException("Malformed manifest file name: {$fileInfo->getBasename()}");
        }
        $algorithm = $matches[1];
        
        $manifest = new Manifest($algorithm);
        if(substr($fileInfo->getBasename(), 0, 3) === 'tag') {
            $manifest->setIsTagManifest(true);
        }
        
        $fileHandle = fopen($path, 'r');
        while($line = fgets($fileHandle)) {
            list($checksum, $filename) = preg_split('/\s+?/', $line, 2, PREG_SPLIT_NO_EMPTY);
            if($checksum && $filename) {
                $manifest->setFile(trim($filename), $checksum);
            }
        }
        return $manifest;
    }
    
    /**
     * @param string $algorithm
     * @throws BagItException
     */
    public function __construct($algorithm, $isTagManifest = false) {
        if( !array_search($algorithm, hash_algos())) {
            throw new BagItException("Unknown or unsupported hash algorithm: {$algorithm}");
        }        
        $this->algorithm = $algorithm;
        $this->isTagManifest = $isTagManifest;
    }
    
    public function getAlgorithm() {
        return $this->algorithm;
    }
    
    public function isTagManifest() {
        return $this->isTagManifest;
    }
    
    public function setTagManifest($isTagManifest) {
        $this->isTagManifest = $isTagManifest;
    }
    
    public function checksum($data) {
        return hash($this->algorithm, $data);
    }
    
    public function checksumFile($path) {
        $context = hash_init($this->algorithm);
        $fileHandle = fopen($path, 'r');
        while($data = fread($fileHandle, 4096)) {
            hash_update($context, $data);
        }
        return hash_final($context);
    }
    
    public function addFile($path) {
        $this->setFile($path, $this->checksum($path));
    }
    
    public function setFile($path, $checksum) {
        $this->data[$path] = strtolower($checksum);
    }
    
    public function hasFile($path) {
        return array_key_exists($path, $this->data);
    }
    
    public function removeFile($path) {
        if(array_key_exists($path, $this->data)) {
            unset($this->data[$path]);
        }
    }
    
    public function update() {
        foreach(array_keys($this->data) as $path) {
            $this->setFile($path, $this->checksum($path));
        }
    }
    
    public function validate() {
        $invalid = array();
        foreach($this->data as $path => $value) {
            if($this->checksum($path) !== $value) {
                $invalid[] = $path;
            }
        }
        return $invalid;
    }
    
    public function serialize() {
        $content = '';
        foreach($this->data as $path => $value) {
            if($value === null) {
                throw new BagItException("Manifest has not been updated.");
            }
            $content .= "{$value} {$path}\n";            
        }
        return $content;
    }
    
    public function write($path) {
        $fileName = "manifest-{$this->algorithm}.txt";
        if($this->isTagManifest()) {
            $fileName = 'tag' . $fileName;
        }
        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, $this->serialize());
    }
    
}