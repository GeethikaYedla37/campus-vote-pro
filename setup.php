<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (!is_local_request()) {
    http_response_code(403);
    exit('Local setup is available only from this computer.');
}

$adminCount = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($adminCount > 0) {
        flash('warning', 'Admin setup is already complete. Please use the admin login page.');
        redirect('admin/login.php');
    }

    verify_csrf('setup.php');

    $name = clean_text((string) ($_POST['name'] ?? ''), 100);
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 150));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!is_valid_person_name($name, 100)) {
        $errors[] = 'Enter a valid admin name.';
    }

    if (!is_valid_email($email)) {
        $errors[] = 'Enter a valid admin email address.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirm password must match.';
    }

    $passwordErrors = password_errors($password);

    if ($passwordErrors) {
        $errors[] = 'Password must contain ' . implode(', ', $passwordErrors) . '.';
    }

    if (!$errors) {
        $statement = db()->prepare(
            'INSERT INTO admins (name, email, password_hash, email_verification_enabled)
             VALUES (?, ?, ?, 1)'
        );
        $statement->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        log_activity('system', null, 'local_admin_created', 'Local admin account created for ' . $email . '.');
        flash('success', 'Admin account created. Login with your email and password, then enter the email verification code.');
        redirect('setup.php?created=1');
    }
}

$smtpReady = smtp_configured();

render_header('Local Setup');
?>
<section class="auth-page">
    <div class="auth-panel">
        <p class="eyebrow">Local Apache Setup</p>
        <h1>Prepare CampusVote Pro for XAMPP.</h1>
        <p class="helper-text">Use this page only after importing <strong>database/schema.sql</strong> in phpMyAdmin.</p>

        <?php if ($errors): ?>
            <div class="flash flash-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <span><?= e(implode(' ', $errors)) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($adminCount === 0): ?>
            <form class="form-stack" method="post" action="<?= url('setup.php') ?>" autocomplete="off">
                <?= csrf_field() ?>
                <label>
                    <span>Admin name</span>
                    <input type="text" name="name" maxlength="100" autocomplete="off" value="" required>
                </label>
                <label>
                    <span>Admin email</span>
                    <input type="email" name="email" maxlength="150" autocomplete="off" value="" data-lpignore="true" required>
                    <small class="field-help">The login verification code will be sent to this email.</small>
                </label>
                <label>
                    <span>Admin password</span>
                    <input type="password" name="password" minlength="10" autocomplete="new-password" value="" data-lpignore="true" required>
                    <small class="field-help">Use 10+ characters with uppercase, lowercase, number, and special character.</small>
                </label>
                <label>
                    <span>Confirm password</span>
                    <input type="password" name="confirm_password" minlength="10" autocomplete="new-password" value="" data-lpignore="true" required>
                </label>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-shield-check"></i>
                        Create admin
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="form-stack">
                <div class="flash flash-success">
                    <i class="bi bi-check-circle"></i>
                    <span>Admin setup is complete. This page is now locked.</span>
                </div>
                <a class="btn btn-primary" href="<?= url('admin/login.php') ?>">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Go to admin login
                </a>
            </div>
        <?php endif; ?>
    </div>

    <aside class="auth-aside">
        <div class="glass-note">
            <strong>Local checklist</strong>
            <span>Database: <?= e(DB_NAME) ?> on <?= e(DB_HOST) ?>:<?= e(DB_PORT) ?></span>
            <span>phpMyAdmin: http://localhost:8080/phpmyadmin</span>
            <span>Email code: <?= $smtpReady ? 'SMTP configured' : 'Add SMTP values in .env before admin login' ?></span>
            <span>Security: passwords are hashed and admin setup locks after first admin.</span>
        </div>
    </aside>
</section>
<?php
render_footer();
