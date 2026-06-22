<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/candidates.php');
}

verify_csrf();

$categoryId = (int) ($_POST['category_id'] ?? 0);

if ($categoryId <= 0) {
    flash('danger', 'Invalid category delete request.');
    redirect('admin/candidates.php');
}

$statement = db()->prepare('SELECT name FROM election_categories WHERE id = ?');
$statement->execute([$categoryId]);
$category = $statement->fetch();

if (!$category) {
    flash('danger', 'Category not found.');
    redirect('admin/candidates.php');
}

$delete = db()->prepare('DELETE FROM election_categories WHERE id = ?');
$delete->execute([$categoryId]);

log_activity('admin', (int) $admin['id'], 'category_deleted', $category['name'] . ' deleted with related candidates and votes.');
flash('success', 'Category deleted successfully.');
redirect('admin/candidates.php');
