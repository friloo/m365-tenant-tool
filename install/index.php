<?php

define('BASE_PATH', dirname(__DIR__));

if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
    die('<h2>Bitte zuerst <code>composer install</code> ausführen.</h2>');
}

require_once BASE_PATH . '/vendor/autoload.php';
require_once __DIR__ . '/InstallerController.php';

$installer = new InstallerController();

if ($installer->isInstalled()) {
    header('Location: ../login');
    exit;
}

$installer->run();
