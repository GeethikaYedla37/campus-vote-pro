<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/candidates.php');
}

verify_csrf();

$candidateId = (int) ($_POST['candidate_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');

if ($candidateId <= 0 || !in_array($status, ['active', 'inactive'], true)) {
    flash('danger', 'Invalid candidate status request.');
    redirect('admin/candidates.php');
}

$statement = db()->prepare('UPDATE candidates SET status = ? WHERE id = ?');
$statement->execute([$status, $candidateId]);

log_activity('admin', (int) $admin['id'], 'candidate_status_updated', 'Candidate #' . $candidateId . ' set to ' . $status . '.');
flash('success', 'Candidate status updated.');
redirect('admin/candidates.php');
