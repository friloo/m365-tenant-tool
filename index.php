<?php

define('BASE_PATH', __DIR__);

// Production error handler — hides details from users, logs to error_log
ini_set('display_errors', '0');
ini_set('log_errors', '1');

set_exception_handler(function (\Throwable $e): void {
    error_log('[M365Tool] Uncaught ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fehler</title></head>'
       . '<body style="font-family:system-ui;text-align:center;padding:80px;">'
       . '<h2>&#9888; Interner Fehler</h2>'
       . '<p>Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es erneut oder kontaktiere den Administrator.</p>'
       . '<p><a href="/">Zur Startseite</a></p></body></html>';
    exit;
});

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
    $ini = parse_ini_file($bootstrapFile, false, INI_SCANNER_RAW);
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

// Ensure m365_users table exists
DB::execute("CREATE TABLE IF NOT EXISTS m365_users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    azure_object_id  VARCHAR(100) DEFAULT NULL,
    upn              VARCHAR(255) NOT NULL,
    display_name     VARCHAR(255) DEFAULT NULL,
    role             ENUM('operator','admin') NOT NULL DEFAULT 'operator',
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    last_login       DATETIME DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_upn (upn),
    UNIQUE KEY uq_oid (azure_object_id)
)");

// Ensure user_notes table exists
DB::execute("CREATE TABLE IF NOT EXISTS user_notes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_azure_id VARCHAR(100) NOT NULL,
    note          TEXT NOT NULL,
    created_by    VARCHAR(255) NOT NULL DEFAULT '',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_azure_id)
)");

// Ensure access_reviews tables exist
DB::execute("CREATE TABLE IF NOT EXISTS access_reviews (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    type         VARCHAR(50) NOT NULL DEFAULT 'guests',
    status       ENUM('open','completed') DEFAULT 'open',
    created_by   VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL
)");

DB::execute("CREATE TABLE IF NOT EXISTS access_review_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    review_id    INT NOT NULL,
    user_id      VARCHAR(100) NOT NULL,
    user_upn     VARCHAR(255) NOT NULL,
    user_name    VARCHAR(255) NOT NULL,
    last_signin  DATETIME DEFAULT NULL,
    decision     ENUM('pending','approve','revoke') DEFAULT 'pending',
    decided_by   VARCHAR(255) DEFAULT NULL,
    decided_at   DATETIME DEFAULT NULL,
    FOREIGN KEY (review_id) REFERENCES access_reviews(id) ON DELETE CASCADE,
    INDEX idx_review (review_id)
)");

// Login brute-force protection table
DB::execute("CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
)");

// Internal application audit log
DB::execute("CREATE TABLE IF NOT EXISTS app_audit_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    actor      VARCHAR(255) NOT NULL DEFAULT '',
    action     VARCHAR(255) NOT NULL,
    module     VARCHAR(100) NOT NULL DEFAULT '',
    detail     TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_actor (actor)
)");

// Configure Config singleton with encryptor
$config = Config::getInstance();
$config->setEncryptor($encryptor);

// Set timezone
$tz = $config->get('timezone', 'Europe/Berlin');
date_default_timezone_set($tz);
DB::get()->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");

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
$router->get('/login',      [\App\Modules\Auth\AuthController::class, 'login']);
$router->post('/login',     [\App\Modules\Auth\AuthController::class, 'doLogin']);
$router->get('/login/2fa',  [\App\Modules\Auth\AuthController::class, 'twofa']);
$router->post('/login/2fa', [\App\Modules\Auth\AuthController::class, 'doTwofa']);
$router->get('/logout',     [\App\Modules\Auth\AuthController::class, 'logout']);
$router->get('/auth/microsoft',          [\App\Modules\Auth\MicrosoftAuthController::class, 'redirect']);
$router->get('/auth/microsoft/callback', [\App\Modules\Auth\MicrosoftAuthController::class, 'callback']);

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
$router->post('/users/{id}/notes',               [\App\Modules\Users\UsersController::class, 'addNote']);
$router->delete('/users/{id}/notes/{noteId}',    [\App\Modules\Users\UsersController::class, 'deleteNote']);

// OneDrive
$router->get('/onedrive',                         [\App\Modules\OneDrive\OneDriveController::class, 'index']);
$router->get('/onedrive/personal',                [\App\Modules\OneDrive\OneDriveController::class, 'personal']);
$router->post('/onedrive/provision/{id}',         [\App\Modules\OneDrive\OneDriveController::class, 'provision']);
$router->post('/onedrive/deprovision/{id}',       [\App\Modules\OneDrive\OneDriveController::class, 'deprovision']);

// SharePoint
$router->get('/sharepoint',                       [\App\Modules\SharePoint\SharePointController::class, 'index']);
$router->get('/sharepoint/{id}',                  [\App\Modules\SharePoint\SharePointController::class, 'site']);

// Sharing
$router->get('/sharing',                          [\App\Modules\Sharing\SharingController::class, 'index']);
$router->get('/sharing/export',                   [\App\Modules\Sharing\SharingController::class, 'export']);
$router->get('/sharing/scan',                     [\App\Modules\Sharing\SharingController::class, 'scan']);
$router->post('/sharing/revoke',                  [\App\Modules\Sharing\SharingController::class, 'revoke']);

// Share Review (public — no auth)
$router->get('/review/demo',                      [\App\Modules\ShareReview\ShareReviewController::class, 'demoReview']);
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
$router->get('/groups/inactive',                  [\App\Modules\Groups\GroupsController::class, 'inactive']);
$router->get('/groups/inactive/export',           [\App\Modules\Groups\GroupsController::class, 'exportInactive']);
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
$router->get('/licenses/expiry',                  [\App\Modules\Licenses\LicensesController::class, 'expiry']);

// App Registrations & Enterprise Apps
$router->get('/appregistrations',                              [\App\Modules\AppRegistrations\AppRegistrationsController::class, 'index']);
$router->get('/appregistrations/{id}',                         [\App\Modules\AppRegistrations\AppRegistrationsController::class, 'show']);
$router->post('/appregistrations/{id}/add-secret',             [\App\Modules\AppRegistrations\AppRegistrationsController::class, 'addSecret']);
$router->post('/appregistrations/{id}/delete-secret',          [\App\Modules\AppRegistrations\AppRegistrationsController::class, 'deleteSecret']);

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

// Exchange Online Migration Readiness
$router->get('/exchangemigration',                [\App\Modules\ExchangeMigration\ExchangeMigrationController::class, 'index']);

// Conditional Access
$router->get('/conditionalaccess',                [\App\Modules\ConditionalAccess\ConditionalAccessController::class, 'index']);
$router->post('/conditionalaccess/create',        [\App\Modules\ConditionalAccess\ConditionalAccessController::class, 'create']);
$router->post('/conditionalaccess/{id}/toggle',   [\App\Modules\ConditionalAccess\ConditionalAccessController::class, 'toggleState']);
$router->post('/conditionalaccess/{id}/delete',   [\App\Modules\ConditionalAccess\ConditionalAccessController::class, 'deletePolicy']);

// Named Locations
$router->get('/namedlocations',                          [\App\Modules\NamedLocations\NamedLocationsController::class, 'index']);
$router->post('/namedlocations/create-country',          [\App\Modules\NamedLocations\NamedLocationsController::class, 'createCountry']);
$router->post('/namedlocations/create-ip',               [\App\Modules\NamedLocations\NamedLocationsController::class, 'createIp']);
$router->post('/namedlocations/{id}/delete',             [\App\Modules\NamedLocations\NamedLocationsController::class, 'delete']);

// License Costs
$router->get('/licensecosts',                     [\App\Modules\LicenseCosts\LicenseCostsController::class, 'index']);

// Offboarding
$router->get('/offboarding',                      [\App\Modules\Offboarding\OffboardingController::class, 'index']);
$router->get('/offboarding/search',               [\App\Modules\Offboarding\OffboardingController::class, 'search']);
$router->post('/offboarding/disable-account',     [\App\Modules\Offboarding\OffboardingController::class, 'disableAccount']);
$router->post('/offboarding/revoke-sessions',     [\App\Modules\Offboarding\OffboardingController::class, 'revokeSessions']);
$router->post('/offboarding/remove-licenses',     [\App\Modules\Offboarding\OffboardingController::class, 'removeLicenses']);
$router->post('/offboarding/remove-groups',       [\App\Modules\Offboarding\OffboardingController::class, 'removeGroups']);

// Teams Overview & Policies
$router->get('/teamspolicies',                    [\App\Modules\TeamsPolicies\TeamsPoliciesController::class, 'index']);

// Sensitivity Labels
$router->get('/sensitivitylabels',                [\App\Modules\SensitivityLabels\SensitivityLabelsController::class, 'index']);

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

// Message Center
$router->get('/msgcenter',                        [\App\Modules\MessageCenter\MessageCenterController::class, 'index']);

// Mail Flow & Schutz
$router->get('/mailflow',                         [\App\Modules\MailFlow\MailFlowController::class, 'index']);

// Security
$router->get('/security',                                    [\App\Modules\Security\SecurityController::class, 'index']);
$router->post('/security/ca/{policyId}/toggle',              [\App\Modules\Security\SecurityController::class, 'toggleCaPolicy']);

// Devices
$router->get('/devices',                          [\App\Modules\Devices\DevicesController::class, 'index']);
$router->get('/devices/export',                   [\App\Modules\Devices\DevicesController::class, 'export']);
$router->get('/devices/{id}',                     [\App\Modules\Devices\DevicesController::class, 'show']);
$router->post('/devices/{id}/sync',               [\App\Modules\Devices\DevicesController::class, 'sync']);
$router->post('/devices/{id}/retire',             [\App\Modules\Devices\DevicesController::class, 'retire']);
$router->post('/devices/{id}/wipe',               [\App\Modules\Devices\DevicesController::class, 'wipe']);

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
$router->get('/mailboxes',                               [\App\Modules\Mailboxes\MailboxController::class, 'index']);
$router->get('/mailboxes/export',                        [\App\Modules\Mailboxes\MailboxController::class, 'export']);
$router->get('/mailboxes/external-forwards',             [\App\Modules\Mailboxes\MailboxController::class, 'externalForwards']);
$router->get('/mailboxes/external-forwards/export',      [\App\Modules\Mailboxes\MailboxController::class, 'exportExternalForwards']);
$router->post('/mailboxes/external-forwards/remove',     [\App\Modules\Mailboxes\MailboxController::class, 'removeForwardingExternal']);
$router->get('/mailboxes/shared',                        [\App\Modules\Mailboxes\MailboxController::class, 'sharedMailboxes']);
$router->get('/mailboxes/{id}',                          [\App\Modules\Mailboxes\MailboxController::class, 'show']);
$router->post('/mailboxes/{id}/forwarding',              [\App\Modules\Mailboxes\MailboxController::class, 'setForwarding']);
$router->post('/mailboxes/{id}/auto-reply',              [\App\Modules\Mailboxes\MailboxController::class, 'setAutoReply']);

// Admin Roles
$router->get('/adminroles',                                   [\App\Modules\AdminRoles\AdminRolesController::class, 'index']);
$router->post('/adminroles/assign',                           [\App\Modules\AdminRoles\AdminRolesController::class, 'assignRole']);
$router->post('/adminroles/{assignmentId}/remove',            [\App\Modules\AdminRoles\AdminRolesController::class, 'removeAssignment']);

// Tenant Sign-in Log
$router->get('/signinlog',                        [\App\Modules\SignInLog\SignInLogController::class, 'index']);
$router->get('/signinlog/export',                 [\App\Modules\SignInLog\SignInLogController::class, 'export']);

// Adoption Dashboard
$router->get('/adoption',                         [\App\Modules\Adoption\AdoptionController::class, 'index']);

// Mailboxes — create shared
$router->post('/mailboxes/create-shared',         [\App\Modules\Mailboxes\MailboxController::class, 'createSharedMailbox']);

// Service Health
$router->get('/servicehealth',                    [\App\Modules\ServiceHealth\ServiceHealthController::class, 'index']);

// Users bulk actions
$router->post('/users/bulk-action',               [\App\Modules\Users\UsersController::class, 'bulkAction']);

// Manual
$router->get('/manual',                           [\App\Modules\Settings\SettingsController::class, 'manual']);

// Settings
$router->get('/settings',                         [\App\Modules\Settings\SettingsController::class, 'index']);
$router->post('/settings/save',                   [\App\Modules\Settings\SettingsController::class, 'save']);
$router->get('/settings/clear-cache',             [\App\Modules\Settings\SettingsController::class, 'clearCache']);
$router->get('/settings/test-mail',               [\App\Modules\Settings\SettingsController::class, 'testMail']);
$router->get('/settings/permissions',             [\App\Modules\Settings\SettingsController::class, 'permissions']);
$router->get('/settings/refresh-token',           [\App\Modules\Settings\SettingsController::class, 'refreshToken']);
$router->get('/settings/license-prices',          [\App\Modules\Settings\SettingsController::class, 'licensePrice']);
$router->post('/settings/license-prices/save',    [\App\Modules\Settings\SettingsController::class, 'saveLicensePrice']);
$router->get('/settings/app-audit',              [\App\Modules\Settings\SettingsController::class, 'appAudit']);
$router->get('/settings/2fa',                    [\App\Modules\Settings\SettingsController::class, 'twofa']);
$router->post('/settings/2fa/setup',             [\App\Modules\Settings\SettingsController::class, 'twofaSetup']);
$router->post('/settings/2fa/verify',            [\App\Modules\Settings\SettingsController::class, 'twofaVerify']);
$router->post('/settings/2fa/disable',           [\App\Modules\Settings\SettingsController::class, 'twofaDisable']);
$router->post('/settings/2fa/regen-codes',       [\App\Modules\Settings\SettingsController::class, 'twofaRegenCodes']);
$router->post('/settings/2fa/cancel',            function() { \App\Core\Session::remove('_totp_setup_secret'); \App\Core\Redirect::to('/settings/2fa'); });

// User management (M365 users with tool access)
$router->get('/settings/users',                 [\App\Modules\Settings\UserManagementController::class, 'index']);
$router->get('/settings/users/search',          [\App\Modules\Settings\UserManagementController::class, 'search']);
$router->post('/settings/users',                [\App\Modules\Settings\UserManagementController::class, 'add']);
$router->post('/settings/users/{id}/update',    [\App\Modules\Settings\UserManagementController::class, 'update']);
$router->post('/settings/users/{id}/delete',    [\App\Modules\Settings\UserManagementController::class, 'delete']);

// Update system
$router->get('/settings/update',                  [\App\Modules\Update\UpdateController::class, 'index']);
$router->post('/settings/update/check',           [\App\Modules\Update\UpdateController::class, 'check']);
$router->post('/settings/update/install',         [\App\Modules\Update\UpdateController::class, 'install']);
$router->get('/settings/update/progress',         [\App\Modules\Update\UpdateController::class, 'progress']);
$router->post('/settings/update/channel',         [\App\Modules\Update\UpdateController::class, 'setChannel']);
$router->post('/settings/update/migrations',      [\App\Modules\Update\UpdateController::class, 'runMigrations']);

// Search API
$router->get('/api/search',                       [\App\Modules\Search\SearchController::class, 'api']);

// Cron & Queue management
$router->get('/cron',                             [\App\Modules\Cron\CronController::class, 'index']);
$router->post('/cron/update-job/{key}',           [\App\Modules\Cron\CronController::class, 'updateJob']);
$router->post('/cron/run-job/{key}',              [\App\Modules\Cron\CronController::class, 'runJob']);
$router->post('/cron/queue/retry',                [\App\Modules\Cron\CronController::class, 'retryFailed']);
$router->post('/cron/queue/prune',                [\App\Modules\Cron\CronController::class, 'pruneQueue']);

// Access Reviews
$router->get('/accessreview',                [\App\Modules\AccessReview\AccessReviewController::class, 'index']);
$router->post('/accessreview',               [\App\Modules\AccessReview\AccessReviewController::class, 'create']);
$router->get('/accessreview/{id}',           [\App\Modules\AccessReview\AccessReviewController::class, 'show']);
$router->post('/accessreview/{id}/decide/{itemId}', [\App\Modules\AccessReview\AccessReviewController::class, 'decide']);
$router->post('/accessreview/{id}/bulk',     [\App\Modules\AccessReview\AccessReviewController::class, 'bulkDecide']);
$router->post('/accessreview/{id}/apply',    [\App\Modules\AccessReview\AccessReviewController::class, 'apply']);

// ── Domain Health ──────────────────────────────────────────
$router->get('/domainhealth',               [\App\Modules\DomainHealth\DomainHealthController::class, 'index']);

// ── Teams Governance ───────────────────────────────────────
$router->get('/teamsgovernance',            [\App\Modules\TeamsGovernance\TeamsGovernanceController::class, 'index']);

// ── Usage Reports ──────────────────────────────────────────
$router->get('/usagereports',               [\App\Modules\UsageReports\UsageReportsController::class, 'index']);

// ── Deleted Objects ────────────────────────────────────────
$router->get('/deletedobjects',             [\App\Modules\DeletedObjects\DeletedObjectsController::class, 'index']);
$router->post('/deletedobjects/{id}/restore',         [\App\Modules\DeletedObjects\DeletedObjectsController::class, 'restore']);
$router->post('/deletedobjects/{id}/permanent-delete', [\App\Modules\DeletedObjects\DeletedObjectsController::class, 'permanentDelete']);

// ── Onboarding Wizard ──────────────────────────────────────
$router->get('/onboarding',                 [\App\Modules\Onboarding\OnboardingController::class, 'wizard']);
$router->post('/onboarding',                [\App\Modules\Onboarding\OnboardingController::class, 'create']);

// ── DLP Policies ───────────────────────────────────────────
$router->get('/dlppolicies',                [\App\Modules\DlpPolicies\DlpPoliciesController::class, 'index']);

// ── Retention Policies ─────────────────────────────────────
$router->get('/retentionpolicies',          [\App\Modules\RetentionPolicies\RetentionPoliciesController::class, 'index']);

// ── KI-Sicherheitsberater ──────────────────────────────────
$router->get('/ai',              [\App\Modules\AiAdvisor\AiAdvisorController::class, 'index']);
$router->post('/ai/analyze',     [\App\Modules\AiAdvisor\AiAdvisorController::class, 'analyze']);
$router->post('/ai/clear-cache', [\App\Modules\AiAdvisor\AiAdvisorController::class, 'clearCache']);

// ── Dispatch ──────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

// Support _method override for DELETE via HTML forms
if ($method === 'POST' && isset($_POST['_method'])) {
    $override = strtoupper(trim($_POST['_method']));
    if (in_array($override, ['DELETE', 'PUT', 'PATCH'], true)) {
        $method = $override;
    }
}

$router->dispatch($method, $uri);
