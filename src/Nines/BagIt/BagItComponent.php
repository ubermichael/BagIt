<?php

namespace Nines\BagIt;

/**
 * The required and optional files in a BagIt package
 * are modeled by classes which implement this interface.
 */
interface BagItComponent {

    /**
     * The name of the file.
     */
    public function filename();

    /**
     * Consume the component file in $path and return
     * the object created from the file content.
     *
     * @param string $path
     * @return BagItCompoent
     */
    public static function read($path);

    /**
     * Serialize the component into a string.
     *
     * @return string
     */
    public function serialize();

    /**
     * Write the component's data to a file in $path.
     *
     * @param string $path
     */
    public function write($path);

}