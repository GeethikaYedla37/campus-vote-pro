<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$student = require_student();

$summary = [
    'open' => (int) db()->query("SELECT COUNT(*) FROM election_categories WHERE status = 'active'")->fetchColumn(),
    'voted' => 0,
    'pending' => 0,
];

$categories = db()->prepare(
    'SELECT ec.*, COUNT(DISTINCT c.id) AS candidate_count,
            v.id AS vote_id, voted_candidate.name AS voted_candidate
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id AND c.status = "active"
     LEFT JOIN votes v ON v.category_id = ec.id AND v.student_id = ?
     LEFT JOIN candidates voted_candidate ON voted_candidate.id = v.candidate_id
     GROUP BY ec.id, ec.name, ec.description, ec.starts_at, ec.ends_at, ec.status, ec.created_at, v.id, voted_candidate.name
     ORDER BY ec.ends_at ASC'
);
$categories->execute([(int) $student['id']]);
$categoryRows = $categories->fetchAll();

foreach ($categoryRows as $category) {
    if ($category['vote_id']) {
        $summary['voted']++;
    } elseif (is_active_status($category)) {
        $summary['pending']++;
    }
}

$feedbackStatement = db()->prepare(
    'SELECT * FROM feedback_messages
     WHERE student_id = ?
     ORDER BY created_at DESC
     LIMIT 5'
);
$feedbackStatement->execute([(int) $student['id']]);
$feedbackRows = $feedbackStatement->fetchAll();

render_header('Student Dashboard', 'student');
?>

<section class="dashboard-hero student-hero">
    <div>
        <p class="eyebrow">Student dashboard</p>
        <h1>Hello, <?= e($student['name']) ?>.</h1>
        <p>Your active ballots, voting status, and results are available here.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('student/candidates.php') ?>"><i class="bi bi-check2-square"></i> Open Ballot</a>
</section>

<section class="metric-band dashboard-metrics">
    <div class="metric"><strong><?= $summary['open'] ?></strong><span>Open Categories</span></div>
    <div class="metric"><strong><?= $summary['voted'] ?></strong><span>Votes Completed</span></div>
    <div class="metric"><strong><?= $summary['pending'] ?></strong><span>Pending Ballots</span></div>
    <div class="metric"><strong><?= e($student['branch']) ?></strong><span>Branch</span></div>
</section>

<section class="section-wrap">
    <div class="section-heading">
        <p class="eyebrow">Ballot status</p>
        <h2>Your election categories</h2>
    </div>
    <div class="grid cards-grid">
        <?php foreach ($categoryRows as $category): ?>
            <?php $state = category_state($category); ?>
            <article class="info-card">
                <div class="card-topline">
                    <span class="badge badge-<?= $category['vote_id'] ? 'success' : status_label($state) ?>">
                        <?= $category['vote_id'] ? 'voted' : e($state) ?>
                    </span>
                    <span><?= (int) $category['candidate_count'] ?> candidates</span>
                </div>
                <h3><?= e($category['name']) ?></h3>
                <p><?= e($category['description']) ?></p>
                <?php if ($category['voted_candidate']): ?>
                    <div class="vote-confirm"><i class="bi bi-check-circle"></i> Selected <?= e($category['voted_candidate']) ?></div>
                <?php else: ?>
                    <a class="inline-link" href="<?= url('student/candidates.php') ?>">View candidates</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="split-section" id="feedback">
    <div>
        <p class="eyebrow">Feedback</p>
        <h2>Send feedback to the election team.</h2>
        <p>Use this section after login for voting access issues, candidate questions, or election feedback. Admin can review it and send you an email when the status changes.</p>
        <div class="process-list">
            <div><i class="bi bi-envelope-check"></i><span>Your feedback is stored in the local database for admin review.</span></div>
            <div><i class="bi bi-person-check"></i><span>Your student name is attached automatically.</span></div>
            <div><i class="bi bi-bell"></i><span>You receive email when admin marks it reviewed or resolved.</span></div>
        </div>
    </div>
    <form class="panel form-stack" action="<?= url('actions/save_feedback.php') ?>" method="post">
        <?= csrf_field() ?>
        <div class="panel-header">
            <h2>Submit feedback</h2>
            <span class="badge badge-neutral">Student</span>
        </div>
        <label>
            <span>Reply email</span>
            <input type="email" name="email" maxlength="150" required>
            <small class="field-help">Admin response status will be emailed to this address.</small>
        </label>
        <label>
            <span>Subject</span>
            <input type="text" name="subject" minlength="4" maxlength="160" required>
        </label>
        <label>
            <span>Message</span>
            <textarea name="message" rows="5" minlength="10" maxlength="2000" required></textarea>
        </label>
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-send"></i>
            Submit feedback
        </button>
    </form>
</section>

<section class="section-wrap tight-top">
    <div class="panel">
        <div class="panel-header">
            <h2>Your Feedback</h2>
            <span><?= count($feedbackRows) ?> recent</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Subject</th>
                    <th>Reply Email</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($feedbackRows as $feedback): ?>
                    <tr>
                        <td>
                            <strong><?= e($feedback['subject']) ?></strong>
                            <small class="table-note"><?= e($feedback['message']) ?></small>
                        </td>
                        <td><?= e($feedback['email']) ?></td>
                        <td><span class="badge badge-<?= status_label($feedback['status']) ?>"><?= e($feedback['status']) ?></span></td>
                        <td><?= format_datetime($feedback['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$feedbackRows): ?>
                    <tr>
                        <td colspan="4">No feedback submitted yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
