<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nines\Bagit\Console;

use Nines\Bagit\Console\Command\ListCommand;
use Nines\Bagit\Console\Command\VerifyCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Description of Application
 */
class Application extends BaseApplication {

    public function __construct() {
        error_reporting(-1);        
        parent::__construct('BagIt');
        $this->add(new VerifyCommand());
        $this->add(new ListCommand());
    }

    
}
