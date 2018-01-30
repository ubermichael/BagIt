# BagIt

Simple, modern, implementation of the [BagIt spec](https://github.com/jkunze/bagitspec). So 
far it only reads Bag files.

## Installation

This library will eventually live on Packagist and be installable via Composer.
But for now it is incomplete. Fork and clone this repository.

## Development

For the repository to your own Github account, then clone it to your development
environment.

```sh
$ git clone git://github.com/<username>/bagit.git
```

Install the dependencies with Composer.

```sh
$ composer install --dev
```

Hack away at the code and do fun things with it. Add tests in the test 
directory. Add documentation. Make some commits, send a pull request.

```sh
# documentation:
$ ./vendor/bin/sami.php update sami.php
# unit tests:
$ ./vendor/bin/phpunit
# code style:
$ ./vendor/bin/phpcs
$ ./vendor/bin/phpcbf
```

## Command Line Usage

`bagit.php` is a thin wrapper around the command line components from Symfony.

```bash
 $ php bagit.php help
 $ php bagit.php list
```

Or as a .phar file. Build it using [Box](https://github.com/box-project/box2).

```bash
 $ box build
 $ ./bagit.phar help
 $ mv bagit.phar /usr/local/bin/bagit
 $ bagit list
```

## Library Usage

Read a BagIt file:

```php
require 'vendor/autoload.php';

use Nines\BagIt\Bag;

$bag = new Bag();

try {
	$bag->read($argv[1]);
} catch(Exception $e) {
	print $e->getMessage() . "\n";
	exit;
}
```

Once you have a bag object, you can call methods on it to do useful things:

```php

print "version: {$bag->getVersion()}\n";
print "encoding: {$bag->getEncoding()}\n";

print "metadata: \n";
if ($bag->hasMetadata()) {
	foreach ($bag->listMetadataKeys() as $key) {
		$value = $bag->getMetadataKey($key);
		if (is_array($value)) {
			foreach ($value as $v) {
				print "  {$key}: {$v}\n";
			}
		} else {
			print "  {$key}: {$value}\n";
		}
	}
}

print "fetch: \n";
if($bag->hasFetchFile()) {
	foreach($bag->listFetchFiles() as $fetch) {
		print "  $fetch\n";
		foreach($bag->getFetchUrls($fetch) as $url) {
			print "    {$bag->getFetchSize($fetch, $url)} $url\n";
		}
	}
}

print "payload manifests: \n";
foreach($bag->listPayloadManifests() as $alg) {
	print "  $alg\n";
	foreach($bag->listPayloadManifestContent($alg) as $path) {
		print "    $path - {$bag->getPayloadChecksum($alg, $path)}\n";
	}
}

print "tag manifests: \n";
foreach($bag->listTagManifests() as $alg) {
	print "  $alg\n";
	foreach($bag->listTagManifestContent($alg) as $path) {
		print "    $path - {$bag->getTagChecksum($alg, $path)}\n";
	}
}

print "payload: \n";
foreach($bag->listPayloadFiles() as $path) {
	print "  {$path}\n";
}
```

## Contributors

Michael Joyce <ubermichael@gmail.com>

## License

GPL 2.