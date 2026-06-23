<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$student = require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student/dashboard.php#feedback');
}

verify_csrf('student/dashboard.php#feedback');

$email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 150));
$subject = clean_text((string) ($_POST['subject'] ?? ''), 160);
$message = clean_text((string) ($_POST['message'] ?? ''), 2000);

if (!is_valid_email($email) || strlen($subject) < 4 || strlen($message) < 10) {
    flash('danger', 'Please enter a valid email, subject, and feedback message.');
    redirect('student/dashboard.php#feedback');
}

try {
    $statement = db()->prepare(
        'INSERT INTO feedback_messages (student_id, name, email, subject, message)
         VALUES (?, ?, ?, ?, ?)'
    );
    $statement->execute([
        (int) $student['id'],
        $student['name'],
        $email,
        $subject,
        $message,
    ]);

    log_activity('student', (int) $student['id'], 'feedback_submitted', 'Feedback submitted with reply email ' . $email . '.');
    flash('success', 'Your feedback was submitted. You will receive email when admin updates the status.');
} catch (PDOException $exception) {
    flash('danger', 'Feedback could not be submitted. Please try again.');
}

redirect('student/dashboard.php#feedback');
