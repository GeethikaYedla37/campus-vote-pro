<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/students.php');
}

verify_csrf();

$studentId = (int) ($_POST['student_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');

if ($studentId <= 0 || !in_array($status, ['pending', 'activated', 'deactivated'], true)) {
    flash('danger', 'Invalid student status request.');
    redirect('admin/students.php');
}

$statement = db()->prepare('UPDATE students SET status = ? WHERE id = ?');
$statement->execute([$status, $studentId]);

log_activity('admin', (int) $admin['id'], 'student_status_updated', 'Student #' . $studentId . ' set to ' . $status . '.');
flash('success', 'Student status updated.');
redirect('admin/students.php');
