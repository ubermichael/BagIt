#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Nines\BagIt\Console\Application;

$application = new Application();
$application->run();
