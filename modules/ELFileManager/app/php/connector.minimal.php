<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_ALL);
include_once __DIR__ .DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once __DIR__ .DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once __DIR__ .DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once __DIR__ .DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
function access($attr, $path) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => $_REQUEST["path"],
			//'URL'           => dirname($_SERVER['PHP_SELF']) . $_REQUEST["path"],
			'utf8fix'    	=> true,
               		//'accessControl' => 'access',
            		'quarantine' => '.tmb/.quarantine',
            		'acceptedName' => '/^/.[^\.].*/'
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

