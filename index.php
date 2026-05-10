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
$router->get('/users',                            [\App\Modules\Users\UsersController::class, 'index']);
$router->get('/users/export',                     [\App\Modules\Users\UsersController::class, 'export']);
$router->get('/users/{id}',                       [\App\Modules\Users\UsersController::class, 'show']);
$router->post('/users/{id}/toggle-enabled',       [\App\Modules\Users\UsersController::class, 'toggleEnabled']);
$router->post('/users/{id}/reset-mfa',            [\App\Modules\Users\UsersController::class, 'resetMfa']);
$router->post('/users/{id}/assign-license',       [\App\Modules\Users\UsersController::class, 'assignLicense']);
$router->post('/users/{id}/remove-license',       [\App\Modules\Users\UsersController::class, 'removeLicense']);
$router->get('/users/{id}/edit',                  [\App\Modules\Users\UsersController::class, 'editForm']);
$router->post('/users/{id}/update',               [\App\Modules\Users\UsersController::class, 'updateUser']);
$router->post('/users/{id}/offboarding',          [\App\Modules\Users\UsersController::class, 'offboarding']);

// OneDrive
$router->get('/onedrive',                         [\App\Modules\OneDrive\OneDriveController::class, 'index']);

// SharePoint
$router->get('/sharepoint',                       [\App\Modules\SharePoint\SharePointController::class, 'index']);
$router->get('/sharepoint/{id}',                  [\App\Modules\SharePoint\SharePointController::class, 'site']);

// Sharing
$router->get('/sharing',                          [\App\Modules\Sharing\SharingController::class, 'index']);
$router->get('/sharing/export',                   [\App\Modules\Sharing\SharingController::class, 'export']);
$router->post('/sharing/revoke',                  [\App\Modules\Sharing\SharingController::class, 'revoke']);

// Share Review (public — no auth)
$router->get('/review/{token}',                   [\App\Modules\ShareReview\ShareReviewController::class, 'review']);
$router->post('/review/{token}',                  [\App\Modules\ShareReview\ShareReviewController::class, 'submitReview']);

// Share Review Monitor (admin)
$router->get('/sharing/monitor',                  [\App\Modules\ShareReview\ShareReviewController::class, 'admin']);
$router->get('/sharing/monitor/scan',             [\App\Modules\ShareReview\ShareReviewController::class, 'scan']);
$router->post('/sharing/monitor/revoke/{id}',     [\App\Modules\ShareReview\ShareReviewController::class, 'revoke']);
$router->post('/sharing/monitor/remind/{id}',     [\App\Modules\ShareReview\ShareReviewController::class, 'remind']);

// Sharing Policies (global settings per module)
$router->get('/sharing/policies',                 [\App\Modules\SharingPolicies\SharingPoliciesController::class, 'index']);
$router->post('/sharing/policies/sharepoint',     [\App\Modules\SharingPolicies\SharingPoliciesController::class, 'updateSharePoint']);
$router->post('/sharing/policies/site',           [\App\Modules\SharingPolicies\SharingPoliciesController::class, 'updateSite']);

// Groups
$router->get('/groups',                           [\App\Modules\Groups\GroupsController::class, 'index']);
$router->get('/groups/export',                    [\App\Modules\Groups\GroupsController::class, 'export']);
$router->get('/groups/{id}',                      [\App\Modules\Groups\GroupsController::class, 'show']);
$router->post('/groups/{id}/add-member',          [\App\Modules\Groups\GroupsController::class, 'addMember']);
$router->post('/groups/{id}/remove-member/{uid}', [\App\Modules\Groups\GroupsController::class, 'removeMember']);
$router->post('/groups/create',                   [\App\Modules\Groups\GroupsController::class, 'create']);
$router->post('/groups/{id}/delete',              [\App\Modules\Groups\GroupsController::class, 'delete']);
$router->post('/groups/{id}/add-owner',           [\App\Modules\Groups\GroupsController::class, 'addOwner']);
$router->post('/groups/{id}/remove-owner/{uid}',  [\App\Modules\Groups\GroupsController::class, 'removeOwner']);

// Licenses
$router->get('/licenses',                         [\App\Modules\Licenses\LicensesController::class, 'index']);
$router->get('/licenses/export',                  [\App\Modules\Licenses\LicensesController::class, 'export']);

// App Registrations & Enterprise Apps
$router->get('/appregistrations',                           [\App\Modules\AppRegistrations\AppRegistrationsController::class, 'index']);

// Risky Sign-ins
$router->get('/riskysignins',                               [\App\Modules\RiskySignIns\RiskySignInsController::class, 'index']);
$router->post('/riskysignins/{userId}/confirm-compromised',  [\App\Modules\RiskySignIns\RiskySignInsController::class, 'confirmCompromised']);
$router->post('/riskysignins/{userId}/dismiss-risk',         [\App\Modules\RiskySignIns\RiskySignInsController::class, 'dismissRisk']);

// Stale Accounts
$router->get('/staleaccounts',                              [\App\Modules\StaleAccounts\StaleAccountsController::class, 'index']);
$router->get('/staleaccounts/export',                       [\App\Modules\StaleAccounts\StaleAccountsController::class, 'export']);
$router->post('/staleaccounts/{userId}/remove-license',     [\App\Modules\StaleAccounts\StaleAccountsController::class, 'removeLicense']);

// License Advisor
$router->get('/licenseadvisor',                   [\App\Modules\LicenseAdvisor\LicenseAdvisorController::class, 'index']);
$router->post('/licenseadvisor/save-criteria',    [\App\Modules\LicenseAdvisor\LicenseAdvisorController::class, 'saveCriteria']);
$router->get('/licenseadvisor/export',            [\App\Modules\LicenseAdvisor\LicenseAdvisorController::class, 'exportUncovered']);

// MFA Methods
$router->get('/mfamethods',                       [\App\Modules\MfaMethods\MfaMethodsController::class, 'index']);

// Password Expiry
$router->get('/passwordexpiry',                   [\App\Modules\PasswordExpiry\PasswordExpiryController::class, 'index']);

// Defender Alerts
$router->get('/defenderalerts',                   [\App\Modules\DefenderAlerts\DefenderAlertsController::class, 'index']);
$router->post('/defenderalerts/{alertId}/resolve', [\App\Modules\DefenderAlerts\DefenderAlertsController::class, 'resolve']);

// Security Posture
$router->get('/securityposture',                  [\App\Modules\SecurityPosture\SecurityPostureController::class, 'index']);

// Teams Usage
$router->get('/teamsusage',                       [\App\Modules\TeamsUsage\TeamsUsageController::class, 'index']);

// Security
$router->get('/security',                         [\App\Modules\Security\SecurityController::class, 'index']);

// Devices
$router->get('/devices',                          [\App\Modules\Devices\DevicesController::class, 'index']);
$router->get('/devices/export',                   [\App\Modules\Devices\DevicesController::class, 'export']);

// Guest Users
$router->get('/guestusers',                       [\App\Modules\GuestUsers\GuestUsersController::class, 'index']);
$router->get('/guestusers/export',                [\App\Modules\GuestUsers\GuestUsersController::class, 'export']);
$router->post('/guestusers/{id}/disable',         [\App\Modules\GuestUsers\GuestUsersController::class, 'disable']);
$router->post('/guestusers/{id}/remove',          [\App\Modules\GuestUsers\GuestUsersController::class, 'remove']);

// Audit Log
$router->get('/auditlog',                         [\App\Modules\AuditLog\AuditLogController::class, 'index']);
$router->get('/auditlog/export',                  [\App\Modules\AuditLog\AuditLogController::class, 'export']);

// Secure Score
$router->get('/securescore',                      [\App\Modules\SecureScore\SecureScoreController::class, 'index']);

// Mailboxes
$router->get('/mailboxes',                        [\App\Modules\Mailboxes\MailboxController::class, 'index']);
$router->get('/mailboxes/export',                 [\App\Modules\Mailboxes\MailboxController::class, 'export']);
$router->get('/mailboxes/{id}',                   [\App\Modules\Mailboxes\MailboxController::class, 'show']);
$router->post('/mailboxes/{id}/forwarding',       [\App\Modules\Mailboxes\MailboxController::class, 'setForwarding']);
$router->post('/mailboxes/{id}/auto-reply',       [\App\Modules\Mailboxes\MailboxController::class, 'setAutoReply']);

// Service Health
$router->get('/servicehealth',                    [\App\Modules\ServiceHealth\ServiceHealthController::class, 'index']);

// Users bulk actions
$router->post('/users/bulk-action',               [\App\Modules\Users\UsersController::class, 'bulkAction']);

// Settings
$router->get('/settings',                         [\App\Modules\Settings\SettingsController::class, 'index']);
$router->post('/settings/save',                   [\App\Modules\Settings\SettingsController::class, 'save']);
$router->get('/settings/clear-cache',             [\App\Modules\Settings\SettingsController::class, 'clearCache']);
$router->get('/settings/test-mail',               [\App\Modules\Settings\SettingsController::class, 'testMail']);

// Cron & Queue management
$router->get('/cron',                             [\App\Modules\Cron\CronController::class, 'index']);
$router->post('/cron/update-job/{key}',           [\App\Modules\Cron\CronController::class, 'updateJob']);
$router->post('/cron/run-job/{key}',              [\App\Modules\Cron\CronController::class, 'runJob']);
$router->post('/cron/queue/retry',                [\App\Modules\Cron\CronController::class, 'retryFailed']);
$router->post('/cron/queue/prune',                [\App\Modules\Cron\CronController::class, 'pruneQueue']);

// ── Dispatch ──────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
