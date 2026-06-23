<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (current_admin()) {
    redirect('admin/dashboard.php');
}

$adminCount = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$smtpReady = smtp_configured();

render_header('Admin Login', 'public');
?>

<section class="auth-page">
    <div class="auth-panel">
        <p class="eyebrow">Administrator</p>
        <h1>Manage elections with confidence.</h1>
        <p>Approve students, add candidates, monitor votes, export results, and review election activity from one dashboard. Admin login uses password plus email verification.</p>
        <p class="helper-text">Admin accounts are created by the system owner during setup, not through public registration.</p>
        <?php if ($adminCount === 0 && is_local_request()): ?>
            <div class="flash flash-warning">
                <i class="bi bi-person-plus"></i>
                <span>No admin account exists yet. Open local setup to create your real admin credentials.</span>
            </div>
            <a class="btn btn-light" href="<?= url('setup.php') ?>"><i class="bi bi-tools"></i> Create admin in setup</a>
        <?php endif; ?>
        <?php if (!$smtpReady): ?>
            <div class="flash flash-warning">
                <i class="bi bi-envelope-exclamation"></i>
                <span>Email verification is active, but SMTP is not configured in `.env`, so the code cannot be delivered yet.</span>
            </div>
        <?php endif; ?>
        <form class="form-stack" action="<?= url('actions/admin_login.php') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <label>
                <span>Email</span>
                <input type="email" name="admin_email" autocomplete="off" value="" data-lpignore="true" required>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="admin_password" autocomplete="new-password" value="" data-lpignore="true" required>
            </label>
            <button class="btn btn-primary" type="submit"><i class="bi bi-shield-check"></i> Login</button>
        </form>
        <p class="helper-text">A short verification code will be sent to the registered admin email after password verification.</p>
    </div>
    <aside class="auth-aside">
        <div class="glass-note">
            <strong>Admin security</strong>
            <span>Password is checked with secure hashing.</span>
            <span>A private email verification code is required after password login.</span>
            <span>Repeated failed logins are rate-limited.</span>
        </div>
    </aside>
</section>

<?php render_footer(); ?>
