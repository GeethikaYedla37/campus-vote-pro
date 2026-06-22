<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$students = db()->query('SELECT * FROM students ORDER BY created_at DESC')->fetchAll();

render_header('Students', 'admin');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Voter access</p>
        <h1>Students</h1>
        <p>Approve registrations and control voter access.</p>
    </div>
</section>

<section class="section-wrap">
    <div class="panel">
        <div class="panel-header">
            <h2>Registered Students</h2>
            <span><?= count($students) ?> records</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Branch</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><strong><?= e($student['name']) ?></strong></td>
                        <td><?= e($student['roll_number']) ?></td>
                        <td><?= e($student['branch']) ?></td>
                        <td><?= (int) $student['study_year'] ?></td>
                        <td><?= (int) $student['semester'] ?></td>
                        <td><span class="badge badge-<?= status_label($student['status']) ?>"><?= e($student['status']) ?></span></td>
                        <td>
                            <div class="button-row">
                                <?php foreach (['activated' => 'Activate', 'deactivated' => 'Deactivate', 'pending' => 'Pending'] as $status => $label): ?>
                                    <form action="<?= url('actions/update_student_status.php') ?>" method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                                        <input type="hidden" name="status" value="<?= e($status) ?>">
                                        <button class="btn btn-small <?= $student['status'] === $status ? 'btn-muted' : 'btn-light' ?>" type="submit"><?= e($label) ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
