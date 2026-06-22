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

<?php render_footer(); ?>
