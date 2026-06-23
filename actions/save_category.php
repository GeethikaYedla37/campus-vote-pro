<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/candidates.php');
}

verify_csrf();

$name = clean_text((string) ($_POST['name'] ?? ''), 120);
$description = clean_text((string) ($_POST['description'] ?? ''), 500);
$startsAt = trim((string) ($_POST['starts_at'] ?? ''));
$endsAt = trim((string) ($_POST['ends_at'] ?? ''));
$status = (string) ($_POST['status'] ?? '');

if (!is_valid_category_name($name) || $startsAt === '' || $endsAt === '' || !in_array($status, ['draft', 'active', 'closed'], true)) {
    flash('danger', 'Fill category details correctly.');
    redirect('admin/candidates.php');
}

try {
    $startDate = new DateTimeImmutable($startsAt);
    $endDate = new DateTimeImmutable($endsAt);
} catch (Throwable $throwable) {
    flash('danger', 'Choose valid start and end dates.');
    redirect('admin/candidates.php');
}

if ($endDate <= $startDate) {
    flash('danger', 'End date must be after start date.');
    redirect('admin/candidates.php');
}

$duplicateCheck = db()->prepare('SELECT id FROM election_categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1');
$duplicateCheck->execute([$name]);

if ($duplicateCheck->fetch()) {
    flash('danger', 'Category already exists. Use another category name.');
    redirect('admin/candidates.php');
}

try {
    $statement = db()->prepare(
        'INSERT INTO election_categories (name, description, starts_at, ends_at, status)
         VALUES (?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $name,
        $description ?: null,
        $startDate->format('Y-m-d H:i:s'),
        $endDate->format('Y-m-d H:i:s'),
        $status,
    ]);

    log_activity('admin', (int) $admin['id'], 'category_created', $name);
    flash('success', 'Election category created.');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('danger', 'Category already exists. Use another category name.');
    } else {
        flash('danger', 'Category could not be saved. Please check the details.');
    }
}

redirect('admin/candidates.php');
