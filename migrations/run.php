<?php

use Nextras\Migrations\Bridges;
use Nextras\Migrations\Controllers;
use Nextras\Migrations\Drivers;
use Nextras\Migrations\Extensions;

$container = require __DIR__ . '/../app/bootstrap.php';

$conn = $container->getByType(Nextras\Dbal\Connection::class);
$dbal = new Bridges\NextrasDbal\NextrasAdapter($conn);
$driver = new Drivers\MySqlDriver($dbal);

$controllerClass = 'Nextras\\Migrations\\Controllers\\' . (PHP_SAPI === 'cli' ? 'Console' : 'Http') . 'Controller';
$controller = new $controllerClass($driver);
$controller->addGroup('structures', __DIR__ . '/../migrations/structures');
$controller->addGroup('basic-data', __DIR__ . '/../migrations/basic-data', ['structures']);
$controller->addGroup('dummy-data', __DIR__ . '/../migrations/dummy-data', ['basic-data']);
$controller->addExtension('sql', new Extensions\SqlHandler($driver));

$controller->run();
