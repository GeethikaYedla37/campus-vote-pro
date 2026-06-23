<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$messages = db()->query('SELECT * FROM feedback_messages ORDER BY created_at DESC')->fetchAll();

render_header('Support Messages', 'admin');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Support</p>
        <h1>Student feedback messages</h1>
        <p>Review feedback submitted from inside the student dashboard. When you update the status, the reply email receives a notification.</p>
    </div>
</section>

<section class="section-wrap">
    <div class="panel">
        <div class="panel-header">
            <h2>Inbox</h2>
            <span><?= count($messages) ?> messages</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td>
                            <strong><?= e($message['name']) ?></strong>
                            <small class="table-note"><?= e($message['email']) ?></small>
                        </td>
                        <td><?= e($message['subject']) ?></td>
                        <td><?= e($message['message']) ?></td>
                        <td><span class="badge badge-<?= status_label($message['status']) ?>"><?= e($message['status']) ?></span></td>
                        <td><?= format_datetime($message['created_at']) ?></td>
                        <td>
                            <form action="<?= url('actions/update_feedback_status.php') ?>" method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="feedback_id" value="<?= (int) $message['id'] ?>">
                                <select name="status" required>
                                    <option value="">Update</option>
                                    <option value="new" <?= selected($message['status'], 'new') ?>>New</option>
                                    <option value="reviewed" <?= selected($message['status'], 'reviewed') ?>>Reviewed</option>
                                    <option value="resolved" <?= selected($message['status'], 'resolved') ?>>Resolved</option>
                                </select>
                                <button class="btn btn-small btn-light" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$messages): ?>
                    <tr>
                        <td colspan="6">No support messages yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
