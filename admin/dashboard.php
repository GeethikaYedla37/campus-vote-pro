<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$admin = require_admin();

$stats = [
    'pending' => (int) db()->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetchColumn(),
    'students' => (int) db()->query("SELECT COUNT(*) FROM students WHERE status = 'activated'")->fetchColumn(),
    'candidates' => (int) db()->query("SELECT COUNT(*) FROM candidates WHERE status = 'active'")->fetchColumn(),
    'votes' => (int) db()->query('SELECT COUNT(*) FROM votes')->fetchColumn(),
];

$recentStudents = db()->query('SELECT * FROM students ORDER BY created_at DESC LIMIT 5')->fetchAll();
$categories = db()->query(
    "SELECT ec.*, COUNT(c.id) AS candidate_count
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id
     GROUP BY ec.id, ec.name, ec.description, ec.starts_at, ec.ends_at, ec.status, ec.created_at
     ORDER BY ec.created_at DESC
     LIMIT 4"
)->fetchAll();

render_header('Admin Dashboard', 'admin');
?>

<section class="dashboard-hero">
    <div>
        <p class="eyebrow">Admin dashboard</p>
        <h1>Welcome, <?= e($admin['name']) ?>.</h1>
        <p>Election operations, approvals, candidates, and results are ready from this control room.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/candidates.php') ?>"><i class="bi bi-person-badge"></i> Enroll Candidate</a>
</section>

<section class="metric-band dashboard-metrics">
    <div class="metric"><strong><?= $stats['pending'] ?></strong><span>Pending Students</span></div>
    <div class="metric"><strong><?= $stats['students'] ?></strong><span>Activated Students</span></div>
    <div class="metric"><strong><?= $stats['candidates'] ?></strong><span>Active Candidates</span></div>
    <div class="metric"><strong><?= $stats['votes'] ?></strong><span>Total Votes</span></div>
</section>

<section class="two-column">
    <div class="panel">
        <div class="panel-header">
            <h2>Election Categories</h2>
            <a href="<?= url('admin/candidates.php') ?>">Manage</a>
        </div>
        <div class="stack-list">
            <?php foreach ($categories as $category): ?>
                <div class="stack-item">
                    <div>
                        <strong><?= e($category['name']) ?></strong>
                        <span><?= (int) $category['candidate_count'] ?> candidates</span>
                    </div>
                    <span class="badge badge-<?= status_label($category['status']) ?>"><?= e($category['status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <h2>Recent Students</h2>
            <a href="<?= url('admin/students.php') ?>">View all</a>
        </div>
        <div class="stack-list">
            <?php foreach ($recentStudents as $student): ?>
                <div class="stack-item">
                    <div>
                        <strong><?= e($student['name']) ?></strong>
                        <span><?= e($student['roll_number']) ?> · <?= e($student['branch']) ?></span>
                    </div>
                    <span class="badge badge-<?= status_label($student['status']) ?>"><?= e($student['status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_footer(); ?>
