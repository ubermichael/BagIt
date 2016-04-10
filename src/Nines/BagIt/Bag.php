<?php

/*
 * The MIT License
 *
 * Copyright 2016 michael.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Nines\BagIt;

use Nines\BagIt\Adapter\BagItAdapter;
use Nines\BagIt\Component\Declaration;
use Nines\BagIt\Component\Fetch;
use Nines\BagIt\Component\Manifest\PayloadManifest;
use Nines\BagIt\Component\Manifest\TagManifest;
use Nines\BagIt\Component\Metadata;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use SplFileInfo;

/**
 * An implementation of the 
 * [BagIt Spec](https://tools.ietf.org/html/draft-kunze-bagit-13). The 
 * [Github Spec](https://github.com/jkunze/bagitspec) may have more recent 
 * information.
 * 
 * <h2>Differences from the specification</h2>
 * 
 * <h3>Metadata</h3>
 * 
 * According to 
 * [Section 2.1.1](https://tools.ietf.org/html/draft-kunze-bagit-13#section-2.1.1)
 * implementations should respect the ordering of metadata in the bag-info.txt 
 * file. This implemention does, except that duplicate metadata keys will be 
 * grouped together
 * 
 * <h3>Tag manifests</h3>
 * 
 * Manifest files are included in tag manifests. Tag manifest files are not
 * included in the tag manifest files. 
 * 
 * <h3>Fetch files</h3>
 * 
 * The specification allows the same payload file to be listed in the fetch
 * file multiple times and with multiple URLs. This implementation will attempt
 * to download fetch files with the URLs listed in the order given in the
 * fetch file. Once a fetch file receives an HTTP 200 with a matching file size
 * (and optionally a matching checksum in a manifest), the implementation
 * will move on to the next file, skipping any remaining URLs for the file.
 * 
 * <h2>Writing Changes to Disk</h2>
 * 
 * This implementation will not write files to disk unless specifically requested
 * to do so. Only downloadFetch(), removeFetchFiles(), and write() will make
 * changes to files on disk.
 * 
 */
class Bag implements LoggerAwareInterface {

	/**
	 * Default version of the BagIt spec.
	 */
	const DEFAULT_VERSION = '0.97';

	/**
	 * Default tag file encoding.
	 */
	const DEFAULT_ENCODING = 'UTF-8';

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Adapter to read from and write to the file system.
	 * 
	 * @var BagItAdapter
	 */
	private $adapter;

	/**
	 * Bag declaration
	 *
	 * @var Declaration
	 */
	private $declaration;
	
	/**
	 * Payload manifest files
	 *
	 * @var PayloadManifest[]
	 */
	private $payloadManifests;
	
	/**
	 * Bag metadata
	 * 
	 * @var Metadata
	 */
	private $metadata;
	
	/**
	 * Payload manifest files
	 *
	 * @var TagManifest[]
	 */
	private $tagManifests;	
	
	/**
	 * Remote bag data
	 * 
	 * @var Fetch
	 */
	private $fetch;

	public function __construct() {
		$this->logger = new NullLogger();
		$this->declaration = new Declaration();
		$this->payloadManifests = array();
		$this->metadata = new Metadata();
		$this->fetch = new Fetch();
		$this->tagManifests = array();
	}

	/**
	 * Set a logger for bag operations. 
	 * 
	 * @param LoggerInterface $logger The logger to use
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	//  ------------------------------------- 
	// Declaration elements

	/**
	 * Get the BagIt specification version for the bag from the bag 
	 * declaration file, if it exists.
	 * 
	 * @return string the BagIt version.
	 */
	public function getVersion() {
		return $this->declaration->getVersion();
	}

	/**
	 * Set the BagIt specification version for the bag.
	 * 
	 * @param string $version the version to use.
	 * 
	 * @throws BagException if the version is malformed.
	 */
	public function setVersion($version) {
		$this->declaration->setVersion($version);
	}

	/**
	 * Get the tag file encoding. Defaults to Bag::DEFAULT_VERSION.
	 * 
	 * @return string the encoding to use.
	 */
	public function getEncoding() {
		return $this->declaration->getEncoding();
	}

	/**
	 * Set the tag file encoding, used when reading or writing tag files.
	 * 
	 * @param string $encoding the encoding to use.
	 * 
	 * @throws BagException if the encoding is unknown or unsupported
	 */
	public function setEncoding($encoding) {
		$this->declaration->setEncoding($encoding);
	}

	//  ------------------------------------- 
	// Payload files

	/**
	 * Add a payload file from $source.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param null|string|SplFileInfo $source null if the file exists, a string
	 * of data, or an SplFileInfo object to copy the data from.
	 */
	public function addPayloadFile($path, $source = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Replace a payload file from $source.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param string|SplFileInfo $source a string of data, or an SplFileInfo 
	 * object to copy the data from.
	 */
	public function replacePayloadFile($path, $source) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if a payload file exists.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param boolean $includeFetch if true and the $path does not exist, then check
	 * if it is listed in the fetch file. This doesn't actually check if the
	 * file can be downloaded.
	 * 
	 * @return boolean true if the payload file exists.
	 */
	public function hasPayloadFile($path, $includeFetch = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Get an array of payload files, each prefixed with 'data/'. 
	 * 
	 * @param boolean $includeFetch if true then include files listed in the
	 * fetch file. This doesn't actually check if the files can be downloaded.
	 * 
	 * @return SplFileInfo[] list of files
	 */
	public function listPayloadFiles($includeFetch = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Count the payload files.
	 * 
	 * @param boolean $includeFetch if true then include files listed in the
	 * fetch file. This doesn't actually check if the files can be downloaded.
	 * 
	 * @return int the number of payload files.
	 */
	public function countPayloadFiles($includeFetch = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Remove a payload file. 
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param boolean $includeFetch if true then the file will also be removed
	 * from the fetch file.
	 */
	public function removePayloadFile($path, $includeFetch = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Get a payload file, optionally downloading it from url listed in the 
	 * fetch file.
	 * 
	 * @return SplFileInfo describing the file.
	 */
	public function getPayloadFile($path, $fetch = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	//  ------------------------------------- 
	// Payload manifests
	/**
	 * Add a payload manifest with the given algorithm. If $path is not null, 
	 * attempt to read the data from the manifest file.
	 * 
	 * @see http://php.net/manual/en/function.hash-algos.php
	 * 
	 * @param string $algorithm one of the algorithms returned by hash_algos.
	 * 
	 * @param string $path path to the manifest. If provided, it will be read.
	 * 
	 * @throws BagException if the algorithm is not supported by PHP.
	 */
	public function addPayloadManifest($algorithm, $path = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Remove a payload manifest.
	 * 
	 * @param string $algorithm The manifest to remove.
	 */
	public function removePayloadManifest($algorithm) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if a bag has a payload manifest based on $algorithm.
	 * 
	 * @param string $algorithm the manifest algorithm to remove.
	 * 
	 * @return boolean true if the bag contains a manifest based on $algorithm.
	 */
	public function hasPayloadManifest($algorithm) {
		return array_key_exists($algorithm, $this->payloadManifests);
	}

	/**
	 * List the manifest algorithms used by the bag.
	 * 
	 * @return string[] list of the payload manifests
	 */
	public function listPayloadManifests() {
		return array_keys($this->payloadManifests);
	}

	/**
	 * Count the payload manifests. Really just a convience for 
	 * `count($bag->listPayloadManifests())`
	 * 
	 * @return int the number of payload manifests.
	 */
	public function countPayloadManifests() {
		return count($this->listPayloadManifests());
	}
	
	/**
	 * List the content in one manifest.
	 * 
	 * @param type $algorithm
	 * @return string[]
	 */
	public function listPayloadManifestContent($algorithm) {
		if(! $this->hasPayloadManifest($algorithm)) {
			return array();
		}
		return $this->payloadManifests[$algorithm]->listFiles();
	}
	
	public function getPayloadChecksum($algorithm, $path) {
		if(! $this->hasPayloadManifest($algorithm)) {
			return null;
		}
		return $this->payloadManifests[$algorithm]->getHash($path);
	}

	/**
	 * Update the payload manifest checksums from the contents of the data/
	 * directory. If the fetch files should be included in the manifests, they
	 * must be downloaded first.
	 */
	public function updatePayloadManifests() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	//  ------------------------------------- 
	// Tag manifests
	/**
	 * Add a tag manifest with the given algorithm. If $path is not null, 
	 * attempt to read the data from the manifest file.
	 * 
	 * @see http://php.net/manual/en/function.hash-algos.php
	 * 
	 * @param string $algorithm one of the algorithms returned by hash_algos.
	 * 
	 * @param string $path path to the manifest. If provided, it will be read.
	 * 
	 * @throws BagException if the algorithm is not supported by PHP.
	 */
	public function addTagManifest($algorithm, $path = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Remove a tag manifest.
	 * 
	 * @param string $algorithm The manifest to remove.
	 */
	public function removeTagManifest($algorithm) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if a bag has a tag manifest based on $algorithm.
	 * 
	 * @param string $algorithm the manifest algorithm to remove.
	 * 
	 * @return boolean true if the bag contains a manifest based on $algorithm.
	 */
	public function hasTagManifest($algorithm) {
		return array_key_exists($algorithm, $this->tagManifests);
	}

	/**
	 * List the manifest algorithms used by the bag.
	 * 
	 * @return string[] list of the tag manifests
	 */
	public function listTagManifests() {
		return array_keys($this->tagManifests);
	}

	/**
	 * Count the tag manifests. Really just a convience for 
	 * `count($bag->listTagManifests())`
	 * 
	 * @return int the number of tag manifests.
	 */
	public function countTagManifests() {
		return count($this->listTagManifests());
	}
	
	/**
	 * List the content in one manifest.
	 * 
	 * @param type $algorithm
	 * @return string[]
	 */
	public function listTagManifestContent($algorithm) {
		if(! $this->hasTagManifest($algorithm)) {
			return array();
		}
		return $this->tagManifests[$algorithm]->listFiles();
	}
	
	public function getTagChecksum($algorithm, $path) {
		if(! $this->hasTagManifest($algorithm)) {
			return null;
		}
		return $this->tagManifests[$algorithm]->getHash($path);
	}

	/**
	 * Update the tag manifest checksums from the contents of the data/
	 * directory. If the fetch files should be included in the manifests, they
	 * must be downloaded first.
	 */
	public function updateTagManifests() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}
	
	//  ------------------------------------- 
	// Metadata
	/**
	 * Check if the bag has a metadata file.
	 * @return boolean
	 */
	public function hasMetadata() {
		return $this->metadata->countKeys() > 0;
	}
	
	/**
	 * Add a value to a key, or create a new key if it doesn't already exist.
	 * @param string $key Metadata key to add
	 * @param string|array $value Value to add
	 */
	public function addMetadataKey($key, $value) {
		$this->metadata->addData($key, $value);
	}
	
	/**
	 * Set the value of a metadata key. Previous value(s) will be lost.
	 * @param string $key Metadata key to add
	 * @param string|array $value Value to add
	 */
	public function setMetadataKey($key, $value) {
		$this->metadata->setData($key, $value);
	}
	
	/**
	 * Get the value associated with a metadata key
	 * 
	 * @param string $key the key to fetch
	 * 
	 * @return null|string|array
	 */
	public function getMetadataKey($key) {
		return $this->metadata->getData($key);
	}
	
	/**
	 * Remove the values associated with a metadata key, and remove the key.
	 * 
	 * @param string $key
	 */
	public function removeMetadataKey($key) {
		$this->metadata->removeData($key);
	}
	
	/**
	 * Check if the metadata contains the given key
	 * 
	 * @param string $key the key to check
	 */
	public function hasMetadataKey($key) {
		return $this->metadata->hasData($key);
	}
	
	/**
	 * Count the metadata keys
	 * 
	 * @return int
	 */
	public function countMetadataKeys() {
		return $this->metadata->countKeys();
	}
	
	/**
	 * Count the metadata values associated with a key
	 * 
	 * @param string key
	 * 
	 * @return int
	 */
	public function countMetadataValues($key) {
		return $this->metadata->countValues($key);
	}
	
	/**
	 * List all the metadata keys. There will be no duplicates in the list.
	 * @return string[]
	 */
	public function listMetadataKeys() {
		return $this->metadata->listKeys();
	}
	
	/**
	 * Remove all the metadata keys and values.
	 */
	public function clearMetadata() {
		$this->metadata->clearData();
	}
	
	//  ------------------------------------- 
	// Tag files
	/**
	 * Add a tag file from $source.
	 * 
	 * @param string $path path to the tag file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param null|string|SplFileInfo $source null if the file exists, a string
	 * of data, or an SplFileInfo object to copy the data from.
	 */
	public function addTagFile($path, $source = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Replace a tag file from $source.
	 * 
	 * @param string $path path to the tag file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param string|SplFileInfo $source a string of data, or an SplFileInfo 
	 * object to copy the data from.
	 */
	public function replaceTagFile($path, $source) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if a tag file exists.
	 * 
	 * @param string $path path to the tag file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @return boolean true if the tag file exists.
	 */
	public function hasTagFile($path) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Get an array of tag files.
	 * 
	 * return SplFileInfo[]
	 */
	public function listTagFiles() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Remove a tag file. 
	 */
	public function removeTagFile($path) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Get a tag file.
	 * 
	 * @return SplFileInfo
	 */
	public function getTagFile($path) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	//  ------------------------------------- 
	// Fetch file
	/**
	 * Check if the bag has a fetch file.
	 * 
	 * @return boolean true if the fetch file exists.
	 */
	public function hasFetchFile() {
		if(! $this->fetch) {
			return false;
		}
		return $this->fetch->countFiles() > 0;
	}

	/**
	 * Remove the fetch file from the bag, perhaps after downloading the 
	 * remote content.
	 */
	public function removeFetchFile() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}
	
	/**
	 * List the remote files. Files listed more than once are only reported
	 * once.
	 */
	public function listFetchFiles() {
		return $this->fetch->listFiles();
	}
	
	/**
	 * Count the remote files. Files listed more than once are only counted
	 * once.
	 * 
	 * Really a wrapper for `count($bag->listFetchFiles())`
	 */
	public function countFetchFiles() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}
	
	/**
	 * Get the URL(s) for a remote file.
	 * 
	 * @param string $path the path to the file inside the bag.
	 */
	public function getFetchUrls($path) {
		return $this->fetch->getUrls($path);
	}
	
	/**
	 * Get the expected size of a remote file. 
	 * 
	 * @param string $path the path to the file inside the bag.
	 * @param string $url Return the expected size for the file at the URL.
	 */
	public function getFetchSize($path, $url = null) {
		return $this->fetch->getSize($path, $url);
	}

	/**
	 * Add an entry to the fetch file. Length is optional. This only adds the
	 * entry, it does not upload the file anywhere.
	 * 
	 * @param string $path the path to the file. Should include the 'data/' 
	 * prefix, but it will be added if necessary.
	 * 
	 * @param string $url the URL for the fetch file.
	 * 
	 * @param int|null $length the optional length of the file.
	 */
	public function addFetch($path, $url, $length = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if there is a fetch entry for a path.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @param string $url There can be multiple listings for a path in the fetch
	 * file. $url is not null, check for a matching URL, otherwise any URL is
	 * acceptable.
	 */
	public function hasFetch($path, $url = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Count the fetch entries. If $path is null, count all fetch entries
	 * otherwise only count the entries for $path.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary.
	 * 
	 * @return int the number of fetch files
	 */
	public function countFetch($path = null) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Download the file for $path, or for all paths. Since a fetch file can
	 * contain multiple URLs, only the first successful download will be kept. 
	 * If $checkManifest is true and the checksum for the file doesn't match
	 * one of the payload manifests, it will be considered a failed download.
	 * 
	 * @param string $path path to the payload file inside the bag. Should
	 * include the 'data/' prefix, but it will be added if necessary. If null, 
	 * then all fetch files will be downloaded.
	 * 
	 * @param boolean $checkManifest ensure the downloaded file's checksum
	 * matches one of the payload manifests.
	 */
	public function downloadFetch($path = null, $checkManifest = false) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Remove the files listed in the fetch list from the bag, perhaps after 
	 * updating or validating checksums. This method does not update manifests.
	 */
	public function removeFetchFiles() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	//  ------------------------------------- 
	// Other actions
	/**
	 * Check if the bag is complete.
	 * 
	 * @return boolean true if the bag is complete.
	 */
	public function isComplete() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Check if the bag is valid. A bag is valid if it is complete and all 
	 * manifest checksums match.
	 * 
	 * @return boolean true if the bag is valid.
	 */
	public function isValid() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Update all the tag and payload manifests to match the contents. This 
	 * method does not alter the contents on disk.
	 */
	public function update() {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	//  ------------------------------------- 
	// Other actions
	/**
	 * Write/serialize the bag to disk. This method will change contents 
	 * on disk.
	 * 
	 * @param string $path
	 */
	public function write($path) {
		throw new RuntimeException(__METHOD__ . " not implemented.");
	}

	/**
	 * Read/unserialize a bag from disk. $path may point to a directory or 
	 * compressed file.
	 * 
	 * @param string $path
	 */
	public function read($path) {
		$this->adapter = BagItAdapter::open($path);
		$this->adapter->setLogger($this->logger);
		$this->declaration = $this->adapter->getDeclaration();
		$this->payloadManifests = $this->adapter->getPayloadManifests();
		$this->metadata = $this->adapter->getMetadata();
		$this->tagManifests = $this->adapter->getTagManifests();
		$this->fetch = $this->adapter->getFetch();
	}
}
