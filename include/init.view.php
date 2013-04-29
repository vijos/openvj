<?php

//
// All PHP files which generate views should include this file
//

// Charset & XSS Protection
header('Content-Type: text/html;charset=utf-8');
header('X-XSS-Protection: 1;mode=block');

require_once __DIR__.'/init.php';
require_once INCLUDE_DIR.'class.view.php';

// TODO: Determine which view to use by session
chdir(VIEW_DIR.'classic');
