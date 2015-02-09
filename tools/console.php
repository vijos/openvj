#!/usr/bin/env php
<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const MODE_TEST = false;
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->setPsr4('VJ\\Console\\', [__DIR__ . '/']);

$app = new \VJ\Core\Application();

$console = new \Symfony\Component\Console\Application();
$console->add(new \VJ\Console\KeywordImportCommand());
$console->run();