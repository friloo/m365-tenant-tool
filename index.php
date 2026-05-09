<?php

define('BASE_PATH', __DIR__);

// Redirect to installer if not installed
if (!file_exists(__DIR__ . '/storage/installed.lock')) {
    if (!str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/install')) {
        header('Location: /install/');
        exit;
    }
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('<h2>Please run <code>composer install</code> first.</h2>');
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;
use App\Core\Config;
use App\Core\Router;
use App\Core\Session;
use App\Database\DB;
use App\Encryption\Encryptor;
use App\Graph\GraphClient;

// ── Bootstrap ──────────────────────────────────────────────

Session::start();

// Load encryption key + connect DB using stored config
$keyPath = __DIR__ . '/storage/app.key';
$encryptor = new Encryptor($keyPath);

// Bootstrap DB from a minimal config file (only needs host/name/user; password decrypted)
// We need to bootstrap the DB to read the rest of the config.
// First connect with the non-encrypted values (db_host, db_name, db_user stored plaintext,
// db_password stored encrypted). We read a bootstrap config from a small ini file written by installer.
$bootstrapFile = __DIR__ . '/storage/db_bootstrap.ini';
if (file_exists($bootstrapFile)) {
    $ini = parse_ini_file($bootstrapFile);
    $dbPassword = $encryptor->decrypt($ini['db_password_enc']);
    DB::connect([
        'host'     => $ini['db_host'],
        'port'     => $ini['db_port'] ?? 3306,
        'name'     => $ini['db_name'],
        'user'     => $ini['db_user'],
        'password' => $dbPassword,
    ]);
} else {
    // Fallback: try to read directly from app_config (DB connection must already exist)
    // This path is hit if db_bootstrap.ini wasn't written. Installer creates it.
    die('Setup incomplete. Please run the <a href="/install/">installer</a>.');
}

// Configure Config singleton with encryptor
$config = Config::getInstance();
$config->setEncryptor($encryptor);

// Set timezone
$tz = $config->get('timezone', 'Europe/Berlin');
date_default_timezone_set($tz);

// ── Service container helpers ──────────────────────────────
$graphCache   = new GraphCache((int)$config->get('cache_ttl', 15));
$tokenManager = new GraphTokenManager($encryptor);
$graphClient  = new GraphClient($tokenManager, $graphCache);

function app_graph(): GraphClient {
    global $graphClient;
    return $graphClient;
}

function app_service(string $class): object {
    global $graphClient;
    return new $class($graphClient);
}

// ── Router ────────────────────────────────────────────────
$router = new Router();

// Auth
$router->get('/login',  [\App\Modules\Auth\AuthController::class, 'login']);
$router->post('/login', [\App\Modules\Auth\AuthController::class, 'doLogin']);
$router->get('/logout', [\App\Modules\Auth\AuthController::class, 'logout']);

// Dashboard
$router->get('/',        [\App\Modules\Dashboard\DashboardController::class, 'index']);

// Users
$router->get('/users',           [\App\Modules\Users\UsersController::class, 'index']);
$router->get('/users/{id}',      [\App\Modules\Users\UsersController::class, 'show']);

// OneDrive
$router->get('/onedrive',        [\App\Modules\OneDrive\OneDriveController::class, 'index']);

// SharePoint
$router->get('/sharepoint',      [\App\Modules\SharePoint\SharePointController::class, 'index']);
$router->get('/sharepoint/{id}', [\App\Modules\SharePoint\SharePointController::class, 'site']);

// Sharing
$router->get('/sharing',         [\App\Modules\Sharing\SharingController::class, 'index']);

// Groups
$router->get('/groups',          [\App\Modules\Groups\GroupsController::class, 'index']);
$router->get('/groups/{id}',     [\App\Modules\Groups\GroupsController::class, 'show']);

// Licenses
$router->get('/licenses',        [\App\Modules\Licenses\LicensesController::class, 'index']);

// Security
$router->get('/security',        [\App\Modules\Security\SecurityController::class, 'index']);

// Devices
$router->get('/devices',         [\App\Modules\Devices\DevicesController::class, 'index']);

// ── Dispatch ──────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
