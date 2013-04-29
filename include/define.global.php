<?php

// Enviroment
define('ENV_REQUEST_TIME', isset($_SERVER['REQUEST_TIME_FLOAT'])
	? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(TRUE));
define('ENV_SSL'         , (bool)(isset($_SERVER['HTTPS']) &&
	$_SERVER['HTTPS'] !== 'off'));
define('ENV_HOST'        , $_SERVER['HTTP_HOST']);
define('ENV_HOST_URL'    , (ENV_SSL ? 'https' : 'http'). '://'.ENV_HOST);
define('ENV_CDN'         , '//www.vijos.org');

// Directories
define('ROOT_DIR'       , realpath(__DIR__.'/..').'/');
define('INCLUDE_DIR'    , ROOT_DIR.'include/');
define('CONTROLLER_DIR' , ROOT_DIR.'controller/');
define('VIEW_DIR'       , ROOT_DIR.'view/');

// System information   
define('SYS_VERSION'    , '2.0 beta');
define('SYS_SESSNAME'   , 'VJ_SESSION');
define('SYS_REVISION'   , '{$SVN_REVISION}');
