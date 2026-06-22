<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_header(string $title, string $area = 'public'): void
{
    $admin = current_admin();
    $student = current_student();
    $flashes = consume_flashes();
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body class="app-body area-<?= e($area) ?>">
<header class="topbar">
    <a class="brand" href="<?= url('index.php') ?>" aria-label="<?= APP_NAME ?>">
        <span class="brand-mark">CV</span>
        <span>
            <strong><?= APP_NAME ?></strong>
            <small>College Election System</small>
        </span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>
    <nav class="main-nav" data-main-nav>
        <?php if ($area === 'admin' && $admin): ?>
            <a href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
            <a href="<?= url('admin/candidates.php') ?>">Candidates</a>
            <a href="<?= url('admin/students.php') ?>">Students</a>
            <a href="<?= url('admin/results.php') ?>">Results</a>
            <a href="<?= url('admin/logs.php') ?>">Logs</a>
            <a class="nav-action" href="<?= url('actions/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <?php elseif ($area === 'student' && $student): ?>
            <a href="<?= url('student/dashboard.php') ?>">Dashboard</a>
            <a href="<?= url('student/candidates.php') ?>">Vote</a>
            <a href="<?= url('student/results.php') ?>">Results</a>
            <a href="<?= url('student/profile.php') ?>">Profile</a>
            <a class="nav-action" href="<?= url('actions/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <?php else: ?>
            <a href="<?= url('index.php') ?>">Home</a>
            <a href="<?= url('admin/login.php') ?>">Admin</a>
            <a href="<?= url('student/login.php') ?>">Student</a>
            <a class="nav-action" href="<?= url('student/register.php') ?>"><i class="bi bi-person-plus"></i> Register</a>
        <?php endif; ?>
    </nav>
</header>

<?php if ($flashes): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($flashes as $flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?>">
                <i class="bi bi-info-circle"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<main>
    <?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="footer">
    <span><?= APP_NAME ?> &copy; <?= date('Y') ?></span>
    <span>Trusted college election portal</span>
</footer>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
    <?php
}
