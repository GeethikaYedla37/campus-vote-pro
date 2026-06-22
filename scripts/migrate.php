<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can run only from the command line.');
}

require_once __DIR__ . '/../config/database.php';

$schemaPath = __DIR__ . '/../database/production_schema.sql';

if (!is_file($schemaPath)) {
    exit("Production schema not found.\n");
}

$schema = file_get_contents($schemaPath);
$schema = preg_replace('/CREATE DATABASE.*?;\s*/is', '', $schema ?? '');
$schema = preg_replace('/USE\s+[`"]?[A-Za-z0-9_]+[`"]?;\s*/i', '', $schema ?? '');
$statements = array_filter(array_map('trim', explode(';', $schema ?? '')));

$pdo = db();

foreach ($statements as $statement) {
    if ($statement !== '') {
        $pdo->exec($statement);
    }
}

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?'
    );
    $statement->execute([$table, $column]);

    return (int) $statement->fetchColumn() > 0;
}

if (!column_exists($pdo, 'admins', 'email_verification_enabled')) {
    $pdo->exec('ALTER TABLE admins ADD COLUMN email_verification_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER password_hash');
}

if (column_exists($pdo, 'admins', 'mfa_enabled')) {
    $pdo->exec('UPDATE admins SET email_verification_enabled = mfa_enabled');
    $pdo->exec('ALTER TABLE admins DROP COLUMN mfa_enabled');
}

if (column_exists($pdo, 'admins', 'mfa_code_hash') && !column_exists($pdo, 'admins', 'email_code_hash')) {
    $pdo->exec('ALTER TABLE admins CHANGE COLUMN mfa_code_hash email_code_hash VARCHAR(255) NULL');
} elseif (!column_exists($pdo, 'admins', 'email_code_hash')) {
    $pdo->exec('ALTER TABLE admins ADD COLUMN email_code_hash VARCHAR(255) NULL AFTER email_verification_enabled');
}

if (column_exists($pdo, 'admins', 'mfa_code_expires_at') && !column_exists($pdo, 'admins', 'email_code_expires_at')) {
    $pdo->exec('ALTER TABLE admins CHANGE COLUMN mfa_code_expires_at email_code_expires_at DATETIME NULL');
} elseif (!column_exists($pdo, 'admins', 'email_code_expires_at')) {
    $pdo->exec('ALTER TABLE admins ADD COLUMN email_code_expires_at DATETIME NULL AFTER email_code_hash');
}

if (column_exists($pdo, 'admins', 'mfa_code_sent_at') && !column_exists($pdo, 'admins', 'email_code_sent_at')) {
    $pdo->exec('ALTER TABLE admins CHANGE COLUMN mfa_code_sent_at email_code_sent_at DATETIME NULL');
} elseif (!column_exists($pdo, 'admins', 'email_code_sent_at')) {
    $pdo->exec('ALTER TABLE admins ADD COLUMN email_code_sent_at DATETIME NULL AFTER email_code_expires_at');
}

if (column_exists($pdo, 'admins', 'mfa_secret')) {
    $pdo->exec('ALTER TABLE admins DROP COLUMN mfa_secret');
}

try {
    $pdo->exec("ALTER TABLE login_attempts MODIFY attempt_type ENUM('admin','admin_mfa','admin_verification','student') NOT NULL");
    $pdo->exec("UPDATE login_attempts SET attempt_type = 'admin_verification' WHERE attempt_type = 'admin_mfa'");
    $pdo->exec("ALTER TABLE login_attempts MODIFY attempt_type ENUM('admin','admin_verification','student') NOT NULL");
} catch (Throwable $throwable) {
    // Fresh databases already have the correct enum.
}

echo "Production database tables are ready.\n";
