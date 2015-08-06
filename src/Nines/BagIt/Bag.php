<?php

namespace Nines\BagIt;

class Bag {

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
        // don't do anythign yet.
    }
    
}