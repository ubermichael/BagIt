<?php

namespace Nines\BagIt;

class Metadata {

    private $data;

    const FILENAME = 'bag-info.txt';

    public static function readMetadata($path) {
        $content = file_get_contents($path . DIRECTORY_SEPARATOR . self::FILENAME);
        $cleaned = preg_replace('/\n\s+', ' ', $content);
        $lines = explode("\n", $cleaned);
        $meta = new Metadata();
        foreach($lines as $line) {
            list($key, $value) = preg_split('/\s*:\s*/', $line, 2);
            $meta->addValue($key, $value);
        }
        return $meta;
    }

    public function __construct() {
        $this->data = array();
    }

    public function clear() {
        $this->data = array();
    }

    public function addValue($key, $value) {
        $this->data[] = array(
            'key' => $key,
            'value' => $value
        );
    }

    public function setValue($key, $value) {
        $this->removeKey($key);
        $this->addValue($key, $value);
    }

    public function removeKey($key) {
        $idx = [];
        for($i = 0; $i < count($this->data); $i++) {
            if($this->data[$i]['key'] === $key) {
                $idx[] = $i;
            }
        }
        foreach($idx as $i) {
            unset($this->data[$i]);
        }
    }

    public function removeValue($key, $value) {
        $idx = [];
        for($i = 0; $i < count($this->data); $i++) {
            if($this->data[$i]['key'] === $key &&
                    $this->data[$i]['value'] === $value) {
                $idx[] = $i;
            }
        }
        foreach($idx as $i) {
            unset($this->data[$i]);
        }
    }

    private function serializeValue($key, $value) {

        $line = "{$key}: {$value}";
        $wrapped = wordwrap($line, 79, "\n ");
        return $wrapped . "\n";
    }

    public function serialize() {
        $content = '';
        foreach ($this->data as $pair) {
            $content .= $this->serializeValue($pair['key'], $pair['value']);
        }
        return $content;
    }

    public function writeMetadata($path) {
        $fileName = $path . DIRECTORY_SEPARATOR . self::FILENAME;
        file_put_contents($fileName, $this->serialize());
    }

}
