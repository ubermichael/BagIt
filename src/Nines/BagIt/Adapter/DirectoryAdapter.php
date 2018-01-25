<?php

namespace Nines\BagIt\Adapter;

class DirectoryAdapter extends BagItAdapter {

    public function getDeclaration() {
        
    }

    public function getFetch() {
        
    }

    public function getFile($path) {
        
    }

    public function getMetadata() {
        $filePath = $this->fileInfo->getRealPath() . '/' . 'bagit-info.txt';
        
    }

    public function getPayloadFiles() {
        
    }

    public function getPayloadManifests() {
        
    }

    public function getTagFiles() {
        
    }

    public function getTagManifests() {
        
    }

    public function readFile($path, $blockSize, $callback) {
        
    }

}