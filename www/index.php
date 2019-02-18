<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('ATT_DIR', WWW_DIR . '/../attachments'); // path to the application root

$container = require __DIR__ . '/../app/bootstrap.php';
$container->getByType('Nette\Application\Application')->run();
