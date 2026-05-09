<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\DB;
use App\Encryption\Encryptor;

class InstallerController
{
    private string $keyPath;
    private string $lockPath;

    public function __construct()
    {
        $this->keyPath  = dirname(__DIR__) . '/storage/app.key';
        $this->lockPath = dirname(__DIR__) . '/storage/installed.lock';
    }

    public function isInstalled(): bool
    {
        return file_exists($this->lockPath);
    }

    public function run(): void
    {
        session_start();

        $step = (int)($_GET['step'] ?? $_SESSION['install_step'] ?? 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($step);
            return;
        }

        $this->renderStep($step);
    }

    private function handlePost(int $step): void
    {
        switch ($step) {
            case 1:
                $this->handleStep1();
                break;
            case 2:
                $this->handleStep2();
                break;
            case 3:
                $this->handleStep3();
                break;
            case 4:
                $this->handleStep4();
                break;
            case 5:
                $this->finish();
                break;
        }
    }

    private function handleStep1(): void
    {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $port = (int)($_POST['db_port'] ?? 3306);
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_password'] ?? '';

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // Run schema
            $schema = file_get_contents(dirname(__DIR__) . '/src/Database/Schema.sql');
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $sql) {
                if ($sql) $pdo->exec($sql);
            }

            $_SESSION['install_db'] = compact('host', 'port', 'name', 'user', 'pass');
            $_SESSION['install_step'] = 2;
            header('Location: ?step=2');
            exit;
        } catch (PDOException $e) {
            $_SESSION['install_error'] = 'Datenbankfehler: ' . $e->getMessage();
            header('Location: ?step=1');
            exit;
        }
    }

    private function handleStep2(): void
    {
        $username = trim($_POST['admin_user'] ?? '');
        $password = $_POST['admin_password'] ?? '';
        $confirm  = $_POST['admin_confirm'] ?? '';

        if (strlen($username) < 3) {
            $_SESSION['install_error'] = 'Benutzername muss mindestens 3 Zeichen haben.';
            header('Location: ?step=2'); exit;
        }
        if (strlen($password) < 8) {
            $_SESSION['install_error'] = 'Passwort muss mindestens 8 Zeichen haben.';
            header('Location: ?step=2'); exit;
        }
        if ($password !== $confirm) {
            $_SESSION['install_error'] = 'Passwörter stimmen nicht überein.';
            header('Location: ?step=2'); exit;
        }

        $_SESSION['install_admin'] = [
            'username' => $username,
            'password' => $password,
        ];
        $_SESSION['install_step'] = 3;
        header('Location: ?step=3'); exit;
    }

    private function handleStep3(): void
    {
        $tenantId     = trim($_POST['tenant_id'] ?? '');
        $clientId     = trim($_POST['client_id'] ?? '');
        $clientSecret = trim($_POST['client_secret'] ?? '');

        if (!$tenantId || !$clientId || !$clientSecret) {
            $_SESSION['install_error'] = 'Alle Azure AD Felder sind erforderlich.';
            header('Location: ?step=3'); exit;
        }

        // Test connection
        try {
            $tenantName = $this->testGraphConnection($tenantId, $clientId, $clientSecret);
            $_SESSION['install_azure'] = compact('tenantId', 'clientId', 'clientSecret');
            $_SESSION['install_tenant_name'] = $tenantName;
            $_SESSION['install_step'] = 4;
            header('Location: ?step=4'); exit;
        } catch (\Exception $e) {
            $_SESSION['install_error'] = 'Azure AD Verbindung fehlgeschlagen: ' . $e->getMessage();
            header('Location: ?step=3'); exit;
        }
    }

    private function testGraphConnection(string $tenantId, string $clientId, string $clientSecret): string
    {
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => 'https://graph.microsoft.com/.default',
                'grant_type'    => 'client_credentials',
            ]),
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $data = json_decode($response, true);
            throw new \RuntimeException($data['error_description'] ?? 'HTTP ' . $httpCode);
        }

        $token = json_decode($response, true)['access_token'];

        // Fetch tenant name from /organization
        $ch2 = curl_init('https://graph.microsoft.com/v1.0/organization');
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token}"],
            CURLOPT_TIMEOUT => 10,
        ]);
        $orgResponse = curl_exec($ch2);
        curl_close($ch2);

        $org = json_decode($orgResponse, true);
        return $org['value'][0]['displayName'] ?? $tenantId;
    }

    private function handleStep4(): void
    {
        $appName  = trim($_POST['app_name'] ?? 'M365 Tenant Tool');
        $cacheTtl = (int)($_POST['cache_ttl'] ?? 15);
        $timezone = trim($_POST['timezone'] ?? 'Europe/Berlin');

        $_SESSION['install_settings'] = compact('appName', 'cacheTtl', 'timezone');
        $_SESSION['install_step'] = 5;
        header('Location: ?step=5'); exit;
    }

    private function finish(): void
    {
        // Generate encryption key
        Encryptor::generateKey($this->keyPath);
        $enc = new Encryptor($this->keyPath);

        // Connect to DB
        $db = $_SESSION['install_db'];
        DB::connect([
            'host'     => $db['host'],
            'port'     => $db['port'],
            'name'     => $db['name'],
            'user'     => $db['user'],
            'password' => $db['pass'],
        ]);

        // Save encrypted DB password + connection info
        $this->saveConfig('db_host',     $db['host'],  false, $enc);
        $this->saveConfig('db_port',     (string)$db['port'], false, $enc);
        $this->saveConfig('db_name',     $db['name'],  false, $enc);
        $this->saveConfig('db_user',     $db['user'],  false, $enc);
        $this->saveConfig('db_password', $db['pass'],  true,  $enc);

        // Save admin credentials (encrypted)
        $admin = $_SESSION['install_admin'];
        $hash  = password_hash($admin['password'], PASSWORD_BCRYPT);
        $this->saveConfig('admin_username', $admin['username'], false, $enc);
        $this->saveConfig('admin_password', $hash,             true,  $enc);

        // Save Azure credentials (encrypted)
        $azure = $_SESSION['install_azure'];
        $this->saveConfig('tenant_id',     $azure['tenantId'],     true, $enc);
        $this->saveConfig('client_id',     $azure['clientId'],     true, $enc);
        $this->saveConfig('client_secret', $azure['clientSecret'], true, $enc);

        // Save settings
        $settings = $_SESSION['install_settings'];
        $this->saveConfig('app_name',  $settings['appName'],           false, $enc);
        $this->saveConfig('cache_ttl', (string)$settings['cacheTtl'],  false, $enc);
        $this->saveConfig('timezone',  $settings['timezone'],          false, $enc);

        // Write bootstrap ini (DB connection info for index.php, password encrypted)
        $bootstrapPath = dirname(__DIR__) . '/storage/db_bootstrap.ini';
        $bootstrapContent = implode("\n", [
            "db_host={$db['host']}",
            "db_port={$db['port']}",
            "db_name={$db['name']}",
            "db_user={$db['user']}",
            "db_password_enc=" . $enc->encrypt($db['pass']),
        ]);
        file_put_contents($bootstrapPath, $bootstrapContent);
        chmod($bootstrapPath, 0600);

        // Write lock file
        file_put_contents($this->lockPath, date('Y-m-d H:i:s'));

        // Clear session
        session_destroy();

        header('Location: ../login');
        exit;
    }

    private function saveConfig(string $key, string $value, bool $encrypt, Encryptor $enc): void
    {
        $stored = $encrypt ? $enc->encrypt($value) : $value;
        DB::execute(
            'INSERT INTO app_config (`key`, value, is_encrypted) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value), is_encrypted = VALUES(is_encrypted)',
            [$key, $stored, $encrypt ? 1 : 0]
        );
    }

    private function renderStep(int $step): void
    {
        $error = $_SESSION['install_error'] ?? null;
        unset($_SESSION['install_error']);
        $tenantName = $_SESSION['install_tenant_name'] ?? null;

        require __DIR__ . '/views/layout.php';
    }
}
