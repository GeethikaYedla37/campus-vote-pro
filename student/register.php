<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

render_header('Student Registration', 'public');
?>

<section class="form-page">
    <div class="section-heading">
        <p class="eyebrow">Create account</p>
        <h1>Student registration</h1>
        <p>After registration, admin approval is required before login.</p>
    </div>
    <form class="form-card form-grid" action="<?= url('actions/register_student.php') ?>" method="post">
        <?= csrf_field() ?>
        <label>
            <span>Name</span>
            <input type="text" name="name" placeholder="Student full name" maxlength="120" pattern="[A-Za-z][A-Za-z .'-]{1,119}" required>
        </label>
        <label>
            <span>Roll Number</span>
            <input type="text" name="roll_number" maxlength="30" placeholder="College roll number" pattern="[A-Za-z0-9-]{3,30}" required>
        </label>
        <label>
            <span>Password</span>
            <input type="password" name="password" minlength="10" autocomplete="new-password" required>
            <small class="field-help">Use uppercase, lowercase, number, and special character.</small>
        </label>
        <label>
            <span>Branch</span>
            <select name="branch" required>
                <option value="">Select branch</option>
                <option value="CSE">CSE</option>
                <option value="IT">IT</option>
                <option value="ECE">ECE</option>
                <option value="EEE">EEE</option>
                <option value="ME">ME</option>
                <option value="CE">CE</option>
                <option value="AE">AE</option>
            </select>
        </label>
        <label>
            <span>Year</span>
            <select name="study_year" required>
                <option value="">Select year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
        </label>
        <label>
            <span>Semester</span>
            <select name="semester" required>
                <option value="">Select semester</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
            </select>
        </label>
        <div class="form-actions">
            <a class="btn btn-light" href="<?= url('student/login.php') ?>"><i class="bi bi-arrow-left"></i> Back</a>
            <button class="btn btn-primary" type="submit"><i class="bi bi-person-plus"></i> Register</button>
        </div>
    </form>
</section>

<?php render_footer(); ?>
