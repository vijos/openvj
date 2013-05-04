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

// Check extensions
if (!extension_loaded('apc'))
    trigger_error('Extension \'apc\' is not loaded', E_USER_ERROR);

if (!extension_loaded('blitz'))
    trigger_error('Extension \'blitz\' is not loaded', E_USER_ERROR);

if (!ini_get('apc.enabled'))
    trigger_error('APC is not enabled', E_USER_WARNING);

// Frame protection
header('X-Frame-Options: SAMEORIGIN');

require_once __DIR__.'/define.config.php';
require_once __DIR__.'/define.global.php';

// Error Reporting
if (!SVR_DEBUG)
	error_reporting(0);
else
	error_reporting(E_ALL | E_STRICT);

// Check whether the requested hostname is in the allowed host list, which is
// defined in define/global.php. If not, generate a HTTP 403 error
if (!in_array(ENV_HOST, $_ALLOWED_HOSTS)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Bad Request: Header field "host" invalid.');
}

// Using UTF-8 as default mbstring encoding
mb_internal_encoding('UTF-8');

// Start buffer
ob_start();

// Set timezone
date_default_timezone_set($_CONFIG['TZ']);