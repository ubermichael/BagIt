<?php

require 'vendor/autoload.php';

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src');

return new Sami($iterator, array(
    'title' => 'BagIt API',
    'build_dir' => __DIR__ . '/docs/api',
    'cache_dir' => __DIR__ . '/docs/api/cache',
));
