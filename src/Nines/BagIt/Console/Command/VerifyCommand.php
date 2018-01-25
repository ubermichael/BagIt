<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nines\Bagit\Console\Command;

use Nines\BagIt\Bag;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of VerifyCommand
 */
class VerifyCommand extends BaseCommand {
    
    protected function configure() {
        parent::configure();
        $this->setName('verify');
        $this->setDescription('Verify a bag');
        $this->addArgument('files', InputArgument::IS_ARRAY, 'List of bags to verify.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $files = $input->getArgument('files');
        foreach($files as $file) {            
            $output->writeln($file, OutputInterface::VERBOSITY_VERBOSE);
            $bag = new Bag();
            $bag->read($file);
            dump($bag);
        }
    }
    
}
