<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student/login.php');
}

verify_csrf();

$rollNumber = strtoupper(trim((string) ($_POST['roll_number'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

if (!is_valid_roll_number($rollNumber) || $password === '') {
    flash('danger', 'Enter a valid roll number and password.');
    redirect('student/login.php');
}

if (too_many_login_attempts('student', $rollNumber)) {
    flash('danger', 'Too many failed login attempts. Please wait 15 minutes and try again.');
    redirect('student/login.php');
}

$result = login_student($rollNumber, $password);
record_login_attempt('student', $rollNumber, $result === 'ok');

if ($result === 'ok') {
    flash('success', 'Student login successful.');
    redirect('student/dashboard.php');
}

if ($result === 'pending') {
    flash('warning', 'Your account is pending admin approval.');
    redirect('student/login.php');
}

if ($result === 'deactivated') {
    flash('danger', 'Your account is deactivated. Contact admin.');
    redirect('student/login.php');
}

flash('danger', 'Invalid roll number or password.');
redirect('student/login.php');
