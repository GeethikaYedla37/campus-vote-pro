<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/candidates.php');
}

verify_csrf();

$categoryId = (int) ($_POST['category_id'] ?? 0);
$name = clean_text((string) ($_POST['name'] ?? ''), 120);
$rollNumber = strtoupper(trim((string) ($_POST['roll_number'] ?? '')));
$branch = strtoupper(trim((string) ($_POST['branch'] ?? '')));
$manifesto = clean_text((string) ($_POST['manifesto'] ?? ''), 700);
$photoPath = null;

if ($categoryId <= 0 || !is_valid_person_name($name) || !in_array($branch, allowed_branches(), true) || strlen($manifesto) < 20) {
    flash('danger', 'Fill candidate details correctly.');
    redirect('admin/candidates.php');
}

if ($rollNumber !== '' && !is_valid_roll_number($rollNumber)) {
    flash('danger', 'Enter a valid candidate roll number.');
    redirect('admin/candidates.php');
}

$categoryCheck = db()->prepare('SELECT id FROM election_categories WHERE id = ?');
$categoryCheck->execute([$categoryId]);

if (!$categoryCheck->fetch()) {
    flash('danger', 'Selected category does not exist.');
    redirect('admin/candidates.php');
}

$duplicateSql = 'SELECT id FROM candidates WHERE category_id = ? AND LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1';
$duplicateValues = [$categoryId, $name];

if ($rollNumber !== '') {
    $duplicateSql = 'SELECT id FROM candidates
                     WHERE category_id = ?
                       AND (LOWER(TRIM(name)) = LOWER(TRIM(?)) OR LOWER(TRIM(roll_number)) = LOWER(TRIM(?)))
                     LIMIT 1';
    $duplicateValues[] = $rollNumber;
}

$duplicateCheck = db()->prepare($duplicateSql);
$duplicateCheck->execute($duplicateValues);

if ($duplicateCheck->fetch()) {
    flash('danger', 'Candidate already exists in this category. Check the name or roll number.');
    redirect('admin/candidates.php');
}

if (!empty($_FILES['photo']['name'])) {
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        flash('danger', 'Candidate photo upload failed.');
        redirect('admin/candidates.php');
    }

    if ($_FILES['photo']['size'] > MAX_UPLOAD_BYTES) {
        flash('danger', 'Candidate photo must be below 2 MB.');
        redirect('admin/candidates.php');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['photo']['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        flash('danger', 'Upload a JPG, PNG, or WebP image.');
        redirect('admin/candidates.php');
    }

    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0775, true);
    }

    $filename = 'candidate_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $targetPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
        flash('danger', 'Could not save candidate photo.');
        redirect('admin/candidates.php');
    }

    @chmod($targetPath, 0644);

    $photoPath = 'uploads/candidates/' . $filename;
}

try {
    $statement = db()->prepare(
        'INSERT INTO candidates (category_id, name, roll_number, branch, manifesto, photo_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $categoryId,
        $name,
        $rollNumber ?: null,
        $branch,
        $manifesto,
        $photoPath,
        'active',
    ]);

    log_activity('admin', (int) $admin['id'], 'candidate_created', $name);
    flash('success', 'Candidate enrolled successfully.');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('danger', 'Candidate already exists in this category. Check the name or roll number.');
    } else {
        flash('danger', 'Candidate could not be saved.');
    }
}

redirect('admin/candidates.php');
