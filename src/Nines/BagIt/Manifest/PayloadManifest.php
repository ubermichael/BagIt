<?php

namespace Nines\BagIt\Manifest;

class PayloadManifest extends Manifest {
	
	public function filename() {
		if(! $this->algorithm) {
			throw new BagException("Cannot supply a filename before algorithm is set.");
		}
		return "manifest-{$this->algorithm}.txt";
	}	
}	
