<?php

//
// All PHP files which generate HTML should include this file
//

// Charset & XSS Protection
header('Content-Type: text/html;charset=utf-8');
header('X-XSS-Protection: 1;mode=block');

require_once __DIR__.'/init.php';
