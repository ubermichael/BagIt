<?php

namespace Nines\BagIt;

class Bag {
    
    private $bagItVersion;
    
    private $tagFileEncoding;
    
    private $manifests;
    
    private $fetch;

    private $metadata;
    
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

}