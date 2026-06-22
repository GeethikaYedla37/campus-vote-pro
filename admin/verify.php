<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$admin = pending_admin();

if (!$admin) {
    flash('warning', 'Admin verification session expired. Please login again.');
    redirect('admin/login.php');
}

render_header('Admin Email Verification', 'public');
?>

<section class="auth-page">
    <div class="auth-panel">
        <p class="eyebrow">Second step</p>
        <h1>Enter email code.</h1>
        <p>We sent a 6-digit verification code to <?= e(mask_email((string) $admin['email'])) ?>.</p>
        <form class="form-stack" action="<?= url('actions/admin_verify.php') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                <span>Email Verification Code</span>
                <input type="text" name="email_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
            </label>
            <button class="btn btn-primary" type="submit"><i class="bi bi-shield-lock"></i> Verify Code</button>
        </form>
        <p class="helper-text">The code expires in <?= EMAIL_CODE_TTL_MINUTES ?> minutes. Login again if you need a new code.</p>
    </div>
    <aside class="auth-aside">
        <div class="glass-note">
            <strong>Email verification enabled</strong>
            <span>Admin access requires the password plus the private code delivered to the registered admin email.</span>
        </div>
    </aside>
</section>

<?php render_footer(); ?>
