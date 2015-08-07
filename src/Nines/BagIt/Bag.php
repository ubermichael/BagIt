<?php

namespace Nines\BagIt;

class Bag {
    
    private $bagItVersion;
    
    private $tagFileEncoding;
    
    /**
     * @var Manifest[]
     */
    private $manifests;
    
    /**
     * @var Manifest[]
     */
    private $tagManifests;

    /**
     * @var Fetch
     */
    private $fetch;

    /**
     * @var Metadata
     */
    private $metadata;

    private $tagFiles;
    
    private $dataFiles;
    
    /**
     * Open an existing BagIt file or directory.
     * 
     * @param string $path
     * @return Bag
     */
    public static function open($path) {
        return new Bag();
    }
    
    public function __construct() {
    }

    public function setBagItVersion($version) {
        $this->bagItVersion = $version;
    }
    
    public function getBagItVersion() {
        return $this->bagItVersion;                
    }
    
    public function setTagFileEncoding($encoding) {
         $this->tagFileEncoding = $encoding;
    }
    
    public function getTagFileEncoding() {
        return $this->tagFileEncoding;
    }
    
    public function addManifest(Manifest $manifest) {
        $this->manifests[$manifest->getAlgorithm()] = $manifest;
    }
    
    public function getManifest($algorithm) {
        return $this->manifests[$algorithm];
    }
    
    public function removeManifest($algorithm) {
        if(array_key_exists($algorithm, $this->manifests)) {
            unset($this->manifests[$algorithm]);
        }
    }
    
    public function getManifestAlgorithms() {
        return array_keys($this->manifests);
    }
    
    public function addTagManifest(Manifest $manifest) {
        $this->tagManifests[$manifest->getAlgorithm()] = $manifest;
    }
    
    public function getTagManifest($algorithm) {
        return $this->tagManifests[$algorithm];
    }
    
    public function removeTagManifest($algorithm) {
        if(array_key_exists($algorithm, $this->tagManifests)) {
            unset($this->tagManifests[$algorithm]);
        }
    }
    
    public function getTagManifestAlgorithms() {
        return array_keys($this->tagManifests);
    }
    
}
