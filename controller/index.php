<?php

require_once INCLUDE_DIR.'init.view.php';

$T = new View('empty.tpl');
$T->display(array('tpl_dir' => 'view/classic'));
