<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/login.php');
}

verify_csrf();

$admin = pending_admin();

if (!$admin) {
    flash('warning', 'Admin verification session expired. Please login again.');
    redirect('admin/login.php');
}

$code = preg_replace('/\D/', '', (string) ($_POST['email_code'] ?? '')) ?? '';
$identifier = 'admin:' . (int) $admin['id'];

if (too_many_login_attempts('admin_verification', $identifier, 5, 15)) {
    flash('danger', 'Too many verification failures. Please wait 15 minutes and login again.');
    unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_started_at']);
    clear_admin_email_code((int) $admin['id']);
    redirect('admin/login.php');
}

if (!verify_admin_email_code($admin, $code)) {
    record_login_attempt('admin_verification', $identifier, false);
    flash('danger', 'Invalid or expired verification code.');
    redirect('admin/verify.php');
}

record_login_attempt('admin_verification', $identifier, true);
complete_admin_login($admin);
flash('success', 'Admin login successful.');
redirect('admin/dashboard.php');
