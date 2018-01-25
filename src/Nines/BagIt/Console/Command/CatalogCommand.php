<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nines\BagIt\Console\Command;

use Nines\BagIt\Bag;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of VerifyCommand
 */
class CatalogCommand extends BaseCommand {
    
    protected function configure() {
        parent::configure();
        $this->setName('catalog');
        $this->setDescription('List the contents of one or more bags');
        $this->addArgument('files', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'List of bags to verify.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $files = $input->getArgument('files');
        foreach($files as $file) {            
            $output->writeln($file);
            $bag = new Bag();
            $bag->read($file);
            $output->writeln("  Manifests: " . implode(', ', $bag->listPayloadManifests()));
            $output->writeln("  Tag Manifests: " . implode(', ', $bag->listTagManifests()));
            // $output->writeln("Tag Files: " . implode(', ', $bag->listTagFiles()));
            foreach($bag->listPayloadFiles() as $file) {
                $output->writeln('  payload: ' . $file);
            }
            foreach($bag->listFetchFiles() as $file) {
                $output->writeln('  fetch:   ' . $file);
            }
        }
    }
    
}
