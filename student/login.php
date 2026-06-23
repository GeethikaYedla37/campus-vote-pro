<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (current_student()) {
    redirect('student/dashboard.php');
}

render_header('Student Login', 'public');
?>

<section class="auth-page student-auth">
    <div class="auth-panel">
        <p class="eyebrow">Student portal</p>
        <h1>Login and cast your vote.</h1>
        <p>Use your roll number and password after admin activation.</p>
        <form class="form-stack" action="<?= url('actions/student_login.php') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <label>
                <span>Roll Number</span>
                <input type="text" name="student_roll_number" autocomplete="off" value="" data-lpignore="true" required>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="student_password" autocomplete="new-password" value="" data-lpignore="true" required>
            </label>
            <button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
        </form>
        <a class="inline-link registration-link" href="<?= url('student/register.php') ?>">
            <i class="bi bi-person-plus"></i>
            New student registration
        </a>
    </div>
    <aside class="auth-aside">
        <div class="glass-note">
            <strong>Student security</strong>
            <span>Activated students can vote once in each open election category.</span>
            <span>Passwords are stored as hashes, not plain text.</span>
            <span>Every vote is protected by database rules.</span>
        </div>
    </aside>
</section>

<?php render_footer(); ?>
