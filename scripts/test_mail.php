<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can run only from the command line.');
}

require_once __DIR__ . '/../includes/auth.php';

$to = env_value('MAIL_TEST_TO');

if ($to === '') {
    $statement = db()->query('SELECT email FROM admins ORDER BY id LIMIT 1');
    $to = (string) ($statement->fetchColumn() ?: '');
}

if (!is_valid_email($to)) {
    exit("Set MAIL_TEST_TO or create an admin with a valid email first.\n");
}

if (SMTP_HOST === '' || SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
    exit("SMTP is not configured yet. Update .env with real SMTP_HOST, SMTP_USERNAME, and SMTP_PASSWORD.\n");
}

$subject = APP_NAME . ' email test';
$body = "This is a test email from " . APP_NAME . ".\n\nIf you received this, admin verification emails can be delivered.\n";

if (!send_mail_message($to, $subject, $body)) {
    exit("Test email failed. Check SMTP host, port, username, app password, encryption, and provider security settings.\n");
}

echo "Test email sent to {$to}.\n";
