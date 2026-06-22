<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can run only from the command line.');
}

require_once __DIR__ . '/../includes/auth.php';

$name = env_value('ADMIN_NAME', 'Election Administrator');
$email = strtolower(env_value('ADMIN_EMAIL'));
$password = env_value('ADMIN_PASSWORD');

if (!is_valid_person_name($name, 100)) {
    exit("Invalid ADMIN_NAME.\n");
}

if (!is_valid_email($email)) {
    exit("Set a valid ADMIN_EMAIL environment variable.\n");
}

$passwordErrors = password_errors($password);

if ($passwordErrors) {
    exit("ADMIN_PASSWORD must contain " . implode(', ', $passwordErrors) . ".\n");
}

$statement = db()->prepare(
    'INSERT INTO admins (name, email, password_hash, email_verification_enabled)
     VALUES (?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       password_hash = VALUES(password_hash),
       email_verification_enabled = 1,
       email_code_hash = NULL,
       email_code_expires_at = NULL,
       email_code_sent_at = NULL'
);

$statement->execute([
    $name,
    $email,
    password_hash($password, PASSWORD_DEFAULT),
]);

log_activity('system', null, 'admin_created_or_updated', 'Production admin account configured for ' . $email . '.');

echo "Admin account configured: {$email}\n";
echo "Email verification codes will be sent to this admin email during login.\n";
echo "No email is sent by this setup command.\n";
