<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$logs = db()->query('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 80')->fetchAll();

render_header('Audit Logs', 'admin');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Audit trail</p>
        <h1>System activity</h1>
        <p>Recent account, ballot, and voting events.</p>
    </div>
</section>

<section class="section-wrap">
    <div class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= format_datetime($log['created_at']) ?></td>
                        <td><?= e($log['actor_type']) ?> #<?= e((string) ($log['actor_id'] ?? '-')) ?></td>
                        <td><strong><?= e($log['action']) ?></strong></td>
                        <td><?= e($log['details'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
