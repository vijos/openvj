<?php

// Directories
define('ROOT_DIR'       , realpath(__DIR__.'/..').'/');
define('INCLUDE_DIR'    , ROOT_DIR.'include/');
define('CONTROLLER_DIR' , ROOT_DIR.'controller/');
define('LIB_DIR'        , ROOT_DIR.'lib/');
define('VIEW_DIR'       , ROOT_DIR.'view/');

// Enviroment
define('ENV_SSL'        , (bool)(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
define('ENV_HOST'       , $_SERVER['HTTP_HOST']);
define('ENV_HOST_URL'   , (ENV_SSL ? 'https' : 'http'). '://'.ENV_HOST);
define('ENV_CDN'        , '//www.vijos.org');

// System information   
define('SYS_VERSION'    , '2.0 beta');
define('SYS_SESSNAME'   , 'VJ_SESSION');