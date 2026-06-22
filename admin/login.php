<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (current_admin()) {
    redirect('admin/dashboard.php');
}

render_header('Admin Login', 'public');
?>

<section class="auth-page">
    <div class="auth-panel">
        <p class="eyebrow">Administrator</p>
        <h1>Manage elections with confidence.</h1>
        <p>Approve students, add candidates, monitor votes, export results, and review election activity from one dashboard. Admin login uses password plus email verification.</p>
        <p class="helper-text">Admin accounts are created by the system owner during setup, not through public registration.</p>
        <form class="form-stack" action="<?= url('actions/admin_login.php') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                <span>Email</span>
                <input type="email" name="email" autocomplete="username" required>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button class="btn btn-primary" type="submit"><i class="bi bi-shield-check"></i> Login</button>
        </form>
        <p class="helper-text">A short verification code will be sent to the registered admin email after password verification.</p>
    </div>
    <aside class="auth-aside">
        <div class="glass-note">
            <strong>Admin tools</strong>
            <span>Student activation, candidate enrollment, live result review, CSV export, and audit history.</span>
        </div>
    </aside>
</section>

<?php render_footer(); ?>
