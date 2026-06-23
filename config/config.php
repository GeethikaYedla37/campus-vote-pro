<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

function load_local_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) || getenv($key) !== false) {
            continue;
        }

        $value = trim($value);

        if ($value !== '' && (
            ($value[0] === '"' && substr($value, -1) === '"')
            || ($value[0] === "'" && substr($value, -1) === "'")
        )) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

load_local_env(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

define('APP_NAME', 'CampusVote Pro');
define('APP_ENV', env_value('APP_ENV', 'local'));

define('DB_HOST', env_value('DB_HOST', 'localhost'));
define('DB_PORT', env_value('DB_PORT', '3305'));
define('DB_NAME', env_value('DB_NAME', 'campus_vote_pro'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', env_value('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'candidates'));
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);
define('EMAIL_CODE_TTL_MINUTES', 10);

$sessionPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0775, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

define('MAIL_TRANSPORT', strtolower(env_value('MAIL_TRANSPORT', 'smtp')));
define('MAIL_FROM_ADDRESS', env_value('MAIL_FROM_ADDRESS', ''));
define('MAIL_FROM_NAME', env_value('MAIL_FROM_NAME', APP_NAME));
define('SMTP_HOST', env_value('SMTP_HOST', ''));
define('SMTP_PORT', (int) env_value('SMTP_PORT', '587'));
define('SMTP_USERNAME', env_value('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env_value('SMTP_PASSWORD', ''));
define('SMTP_ENCRYPTION', strtolower(env_value('SMTP_ENCRYPTION', 'tls')));

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_name('campusvote_session');
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net data:; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = preg_replace('#/(admin|student|actions)$#', '', $scriptDir);
$scriptDir = $scriptDir === '/' ? '' : rtrim((string) $scriptDir, '/');
define('BASE_URL', $scriptDir);
