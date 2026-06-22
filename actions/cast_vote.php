<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$student = require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student/candidates.php');
}

verify_csrf();

$candidateId = (int) ($_POST['candidate_id'] ?? 0);

if ($candidateId <= 0) {
    flash('danger', 'Choose a valid candidate.');
    redirect('student/candidates.php');
}

$statement = db()->prepare(
    'SELECT c.*, ec.name AS category_name, ec.starts_at, ec.ends_at, ec.status AS category_status
     FROM candidates c
     INNER JOIN election_categories ec ON ec.id = c.category_id
     WHERE c.id = ?'
);
$statement->execute([$candidateId]);
$candidate = $statement->fetch();

if (!$candidate || $candidate['status'] !== 'active') {
    flash('danger', 'Candidate is not available.');
    redirect('student/candidates.php');
}

$category = [
    'starts_at' => $candidate['starts_at'],
    'ends_at' => $candidate['ends_at'],
    'status' => $candidate['category_status'],
];

if (!is_active_status($category)) {
    flash('warning', 'Voting is closed for this category.');
    redirect('student/candidates.php');
}

try {
    $insert = db()->prepare('INSERT INTO votes (student_id, candidate_id, category_id) VALUES (?, ?, ?)');
    $insert->execute([(int) $student['id'], (int) $candidate['id'], (int) $candidate['category_id']]);

    log_activity('student', (int) $student['id'], 'vote_cast', 'Voted in ' . $candidate['category_name'] . '.');
    flash('success', 'Your vote has been recorded securely.');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('warning', 'You already voted in this category.');
    } else {
        flash('danger', 'Vote could not be recorded.');
    }
}

redirect('student/candidates.php');
