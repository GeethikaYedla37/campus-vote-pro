<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$stats = [
    'students' => (int) db()->query("SELECT COUNT(*) FROM students WHERE status = 'activated'")->fetchColumn(),
    'candidates' => (int) db()->query("SELECT COUNT(*) FROM candidates WHERE status = 'active'")->fetchColumn(),
    'categories' => (int) db()->query("SELECT COUNT(*) FROM election_categories WHERE status = 'active'")->fetchColumn(),
    'votes' => (int) db()->query('SELECT COUNT(*) FROM votes')->fetchColumn(),
];

$categories = db()->query(
    "SELECT ec.*, COUNT(c.id) AS candidate_count
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id AND c.status = 'active'
     GROUP BY ec.id, ec.name, ec.description, ec.starts_at, ec.ends_at, ec.status, ec.created_at
     ORDER BY ec.starts_at DESC
     LIMIT 4"
)->fetchAll();

render_header('Online College Election', 'public');
?>

<section class="hero public-hero" style="background-image: linear-gradient(90deg, rgba(13, 36, 49, .90), rgba(17, 121, 93, .40)), url('<?= asset('img/campus-hero.jpg') ?>');">
    <div class="hero-inner">
        <p class="eyebrow">Verified student election portal</p>
        <h1>Secure online voting for campus leadership.</h1>
        <p class="hero-copy">CampusVote Pro helps colleges run transparent student elections with admin approval, protected voting, live results, and a clear audit trail.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= url('student/login.php') ?>"><i class="bi bi-check2-square"></i> Student Voting</a>
            <a class="btn btn-light" href="<?= url('admin/login.php') ?>"><i class="bi bi-shield-lock"></i> Admin Login</a>
        </div>
        <div class="hero-trust">
            <span><i class="bi bi-lock"></i> One vote per category</span>
            <span><i class="bi bi-person-check"></i> Admin-approved voters</span>
            <span><i class="bi bi-clipboard-data"></i> Live result counting</span>
        </div>
    </div>
</section>

<section class="metric-band">
    <div class="metric"><strong><?= $stats['students'] ?></strong><span>Activated Students</span></div>
    <div class="metric"><strong><?= $stats['categories'] ?></strong><span>Election Categories</span></div>
    <div class="metric"><strong><?= $stats['candidates'] ?></strong><span>Candidates</span></div>
    <div class="metric"><strong><?= $stats['votes'] ?></strong><span>Votes Cast</span></div>
</section>

<section class="section-wrap">
    <div class="section-heading">
        <p class="eyebrow">Current ballots</p>
        <h2>Election categories open to students</h2>
        <p>Students can vote only after their account is activated by the election administrator.</p>
    </div>
    <div class="grid cards-grid">
        <?php foreach ($categories as $category): ?>
            <?php $state = category_state($category); ?>
            <article class="info-card">
                <div class="card-topline">
                    <span class="badge badge-<?= status_label($state) ?>"><?= e($state) ?></span>
                    <span><?= (int) $category['candidate_count'] ?> candidates</span>
                </div>
                <h3><?= e($category['name']) ?></h3>
                <p><?= e($category['description']) ?></p>
                <div class="card-meta">
                    <span><i class="bi bi-calendar-event"></i> <?= format_datetime($category['starts_at']) ?></span>
                    <span><i class="bi bi-hourglass-split"></i> <?= format_datetime($category['ends_at']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="public-band">
    <div class="section-heading">
        <p class="eyebrow">How it works</p>
        <h2>A simple process for students and administrators</h2>
    </div>
    <div class="timeline-grid">
        <article>
            <span>01</span>
            <h3>Student registers</h3>
            <p>Student submits name, roll number, branch, year, semester, and a strong password.</p>
        </article>
        <article>
            <span>02</span>
            <h3>Admin verifies</h3>
            <p>Admin reviews the student list and activates only valid voters.</p>
        </article>
        <article>
            <span>03</span>
            <h3>Student votes</h3>
            <p>Each activated student can cast one vote in each open category.</p>
        </article>
        <article>
            <span>04</span>
            <h3>Results update</h3>
            <p>Votes are counted directly from the database and shown on the results page.</p>
        </article>
    </div>
</section>

<section class="split-section security-section">
    <div>
        <p class="eyebrow">Election integrity</p>
        <h2>A voting experience students can understand and trust.</h2>
        <p>The portal keeps the process clear: only approved students can vote, each voter gets one chance per category, and results are shown from recorded votes.</p>
    </div>
    <div class="process-list">
        <div><i class="bi bi-person-check"></i><span>Students vote only after admin approval.</span></div>
        <div><i class="bi bi-check2-square"></i><span>One student can vote once in each category.</span></div>
        <div><i class="bi bi-clock-history"></i><span>Each election category has a clear open and close time.</span></div>
        <div><i class="bi bi-bar-chart"></i><span>Results update from the official vote records.</span></div>
    </div>
</section>

<section class="role-section">
    <div class="role-card admin-role">
        <p class="eyebrow">Admin</p>
        <h2>Election control room</h2>
        <p>Create categories, enroll candidates, activate students, monitor results, export CSV reports, and review audit logs.</p>
        <a class="btn btn-light" href="<?= url('admin/login.php') ?>"><i class="bi bi-shield-check"></i> Admin Portal</a>
    </div>
    <div class="role-card student-role">
        <p class="eyebrow">Student</p>
        <h2>Verified voter portal</h2>
        <p>Register once, wait for approval, login securely, view candidates, cast votes, and track results.</p>
        <a class="btn btn-primary" href="<?= url('student/login.php') ?>"><i class="bi bi-person-check"></i> Student Portal</a>
    </div>
</section>

<?php render_footer(); ?>
