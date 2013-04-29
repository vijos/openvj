<?php

// 
// This file contains global initialization of the system
// 
// All top-level PHP files which does not include any files should include
// this file, which implies that this file is ultimately included
//
// Specific initialization should not go here, for example
//
//     header('Content-Type: text/html;charset=utf-8');
//
// should not be put here, since not all PHP files generate HTML.
// Instead, it should be put into init.html.php
//

// Record starting time
global $_START_TIME;
$_START_TIME = microtime(TRUE);

// Check extensions
if (!extension_loaded('apc'))
	exit('Extension \'apc\' is not loaded');
	
if (!extension_loaded('blitz'))
	exit('Extension \'blitz\' is not loaded');

if (!ini_get('apc.enabled'))
	exit('APC is not enabled');

require_once __DIR__.'/const/config.php';
require_once __DIR__.'/const/global.php';

require_once INCLUDE_DIR.'func.apc.php';

// Frame protection
header('X-Frame-Options: SAMEORIGIN');

// Check whether the requested hostname is in the allowed host list, which is
// defined in define/global.php. If not, generate a HTTP 403 error
if (!in_array(ENV_HOST, $_ALLOWED_HOSTS)) {
	header('HTTP/1.1 403 Forbidden');
	exit('Bad Request: Header field "host" invalid.');
}
