<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

global $T_START;
$T_START = microtime(true);

const MODE_TEST = false;
require __DIR__ . '/../vendor/autoload.php';

\VJ\VJ::bootstrap();
\VJ\Core\Application::route();

// profiler
$T_END = microtime(true);
$app::debug($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' ' . round(($T_END - $T_START) * 1000) . 'ms');