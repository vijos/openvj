<?php

require_once INCLUDE_DIR.'init.html.php';
require_once INCLUDE_DIR.'class.view.php';

$T = new View();
$T->load('<h1>It {{ $verb }}!</h1>
<p>Execution time: {{ execution_time() }}ms</p>');
$T->assign('verb', 'works');

usleep(123456);

$T->display();
