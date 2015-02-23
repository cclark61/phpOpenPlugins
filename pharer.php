#!/usr/bin/env php
<?php
//****************************************************************************************
//****************************************************************************************
// Quarry PHAR Builder
//****************************************************************************************
//****************************************************************************************

//================================================================================
// Source Directory
//================================================================================
$srcRoot = __DIR__;
$phar_name = 'phpOpenPlugins.phar';

//================================================================================
// Options
//================================================================================
$opts = 'o:'; // Output Directory
$options = getopt($opts);

//================================================================================
// Determine Output Location
//================================================================================
if (!empty($options['o'])) {
	$buildRoot = realpath($options['o']);
	if (!is_dir($buildRoot)) {
		die("Invalid output location '{$buildRoot}'.");
	}
	else if (!is_writable($options['o'])) {
		die("Output location '{$buildRoot}' is not writable.");
	}
	
}
else {
	$buildRoot = getcwd();
	if (!is_writable($buildRoot)) {
		die("Output location '{$buildRoot}' is not writable.");
	}
}

//================================================================================
// Build PHAR
//================================================================================
$phar = new Phar($buildRoot . "/{$phar_name}",
        FilesystemIterator::CURRENT_AS_FILEINFO |
        FilesystemIterator::KEY_AS_FILENAME, $phar_name);
$phar->buildFromDirectory($srcRoot);
$phar->setStub($phar->createDefaultStub('version.php'));

