#!/usr/bin/env php
<?php

const MODE_TEST = false;
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->setPsr4('VJ\\Console\\', [__DIR__ . '/']);

$app = new \VJ\Core\Application();

$console = new \Symfony\Component\Console\Application();
$console->add(new \VJ\Console\KeywordImportCommand());
$console->run();