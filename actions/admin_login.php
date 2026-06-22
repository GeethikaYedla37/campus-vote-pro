<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/login.php');
}

verify_csrf();

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

if (!is_valid_email($email) || $password === '') {
    flash('danger', 'Enter a valid admin email and password.');
    redirect('admin/login.php');
}

if (too_many_login_attempts('admin', $email)) {
    flash('danger', 'Too many failed login attempts. Please wait 15 minutes and try again.');
    redirect('admin/login.php');
}

$admin = verify_admin_credentials($email, $password);
record_login_attempt('admin', $email, (bool) $admin);

if (!$admin) {
    flash('danger', 'Invalid admin credentials.');
    redirect('admin/login.php');
}

if ((int) $admin['email_verification_enabled'] === 1) {
    if (!begin_admin_email_verification($admin)) {
        flash('danger', 'Admin password is correct, but the email verification code could not be sent. Check SMTP email settings.');
        redirect('admin/login.php');
    }

    flash('success', 'A verification code was sent to the admin email address.');
    redirect('admin/verify.php');
}

complete_admin_login($admin);
flash('success', 'Admin login successful.');
redirect('admin/dashboard.php');
