<?php


//if(file_exists($path = __DIR__ . '/../../autoload.php')) {
//    require_once $a;
//} else {
    require_once __DIR__ . '/vendor/autoload.php';
//}

use Nines\Bagit\Console\Application;

$application = new Application();
$application->run();