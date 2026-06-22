<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student/register.php');
}

verify_csrf();

$name = clean_text((string) ($_POST['name'] ?? ''), 120);
$rollNumber = strtoupper(trim((string) ($_POST['roll_number'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$branch = strtoupper(trim((string) ($_POST['branch'] ?? '')));
$year = (int) ($_POST['study_year'] ?? 0);
$semester = (int) ($_POST['semester'] ?? 0);

$allowedBranches = allowed_branches();

if (!is_valid_person_name($name) || !is_valid_roll_number($rollNumber) || $password === '' || $branch === '' || $year < 1 || $year > 4 || $semester < 1 || $semester > 2) {
    flash('danger', 'Fill all registration details correctly.');
    redirect('student/register.php');
}

if (!in_array($branch, $allowedBranches, true)) {
    flash('danger', 'Choose a valid branch.');
    redirect('student/register.php');
}

$passwordErrors = password_errors($password);

if ($passwordErrors) {
    flash('danger', 'Password must contain ' . implode(', ', $passwordErrors) . '.');
    redirect('student/register.php');
}

try {
    $statement = db()->prepare(
        'INSERT INTO students (name, roll_number, password_hash, branch, study_year, semester, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $name,
        $rollNumber,
        password_hash($password, PASSWORD_DEFAULT),
        $branch,
        $year,
        $semester,
        'pending',
    ]);

    log_activity('system', null, 'student_registered', $rollNumber . ' registered and is waiting for activation.');
    flash('success', 'Registration submitted. Admin must activate your account.');
    redirect('student/login.php');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('danger', 'This roll number is already registered.');
        redirect('student/register.php');
    }

    flash('danger', 'Registration failed. Try again.');
    redirect('student/register.php');
}
