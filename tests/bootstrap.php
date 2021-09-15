<?php

use Nette\Loaders\RobotLoader;

$sep = DIRECTORY_SEPARATOR;

require __DIR__ . $sep . '..' . $sep . 'vendor' . $sep . 'autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__ . $sep . '..' . $sep);
$loader->setTempDirectory(__DIR__ . $sep . '..' . $sep . 'temp');
$loader->setAutoRefresh();
$loader->register();

Tester\Environment::setup();