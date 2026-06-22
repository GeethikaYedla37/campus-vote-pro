<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

define('APP_NAME', 'CampusVote Pro');
define('APP_ENV', env_value('APP_ENV', 'local'));

$databaseUrl = env_value('DATABASE_URL');
$databaseParts = $databaseUrl ? parse_url($databaseUrl) : [];

define('DB_HOST', env_value('DB_HOST', $databaseParts['host'] ?? 'localhost'));
define('DB_PORT', env_value('DB_PORT', isset($databaseParts['port']) ? (string) $databaseParts['port'] : '3305'));
define('DB_NAME', env_value('DB_NAME', isset($databaseParts['path']) ? ltrim($databaseParts['path'], '/') : 'campus_vote_pro'));
define('DB_USER', env_value('DB_USER', $databaseParts['user'] ?? 'root'));
define('DB_PASS', env_value('DB_PASS', isset($databaseParts['pass']) ? urldecode($databaseParts['pass']) : ''));
define('DB_CHARSET', 'utf8mb4');

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', env_value('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'candidates'));
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);
define('EMAIL_CODE_TTL_MINUTES', 10);

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
