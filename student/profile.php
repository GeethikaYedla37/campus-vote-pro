<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$student = require_student();

$history = db()->prepare(
    'SELECT ec.name AS category_name, c.name AS candidate_name, v.created_at
     FROM votes v
     INNER JOIN election_categories ec ON ec.id = v.category_id
     INNER JOIN candidates c ON c.id = v.candidate_id
     WHERE v.student_id = ?
     ORDER BY v.created_at DESC'
);
$history->execute([(int) $student['id']]);
$votes = $history->fetchAll();

render_header('Profile', 'student');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Account</p>
        <h1>Profile</h1>
        <p>Your student details and voting history.</p>
    </div>
</section>

<section class="two-column">
    <div class="panel profile-panel">
        <span class="avatar avatar-xl"><?= e(initials($student['name'])) ?></span>
        <h2><?= e($student['name']) ?></h2>
        <p><?= e($student['roll_number']) ?></p>
        <div class="profile-grid">
            <span>Branch</span><strong><?= e($student['branch']) ?></strong>
            <span>Year</span><strong><?= (int) $student['study_year'] ?></strong>
            <span>Semester</span><strong><?= (int) $student['semester'] ?></strong>
            <span>Status</span><strong><?= e($student['status']) ?></strong>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <h2>Voting History</h2>
            <span><?= count($votes) ?> votes</span>
        </div>
        <div class="stack-list">
            <?php foreach ($votes as $vote): ?>
                <div class="stack-item">
                    <div>
                        <strong><?= e($vote['category_name']) ?></strong>
                        <span><?= e($vote['candidate_name']) ?></span>
                    </div>
                    <span><?= format_datetime($vote['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (!$votes): ?>
                <p class="muted">No votes cast yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php render_footer(); ?>
