<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/support.php');
}

verify_csrf('admin/support.php');

$feedbackId = (int) ($_POST['feedback_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');

if ($feedbackId <= 0 || !in_array($status, ['new', 'reviewed', 'resolved'], true)) {
    flash('danger', 'Invalid feedback update.');
    redirect('admin/support.php');
}

$feedbackStatement = db()->prepare('SELECT * FROM feedback_messages WHERE id = ?');
$feedbackStatement->execute([$feedbackId]);
$feedback = $feedbackStatement->fetch();

if (!$feedback) {
    flash('danger', 'Feedback message was not found.');
    redirect('admin/support.php');
}

$statement = db()->prepare('UPDATE feedback_messages SET status = ? WHERE id = ?');
$statement->execute([$status, $feedbackId]);

log_activity('admin', (int) $admin['id'], 'feedback_status_updated', 'Feedback #' . $feedbackId . ' marked ' . $status . '.');

$subject = APP_NAME . ' feedback status update';
$body = "Hello " . $feedback['name'] . ",\n\n"
    . "Your feedback status has been updated to: " . strtoupper($status) . "\n\n"
    . "Subject: " . $feedback['subject'] . "\n\n"
    . "Message reviewed by the election administration team.\n";

if (send_mail_message((string) $feedback['email'], $subject, $body)) {
    flash('success', 'Feedback status updated and email notification sent.');
} else {
    flash('warning', 'Feedback status updated, but email could not be sent. Check SMTP settings.');
}

redirect('admin/support.php');
