<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$rows = db()->query(
    'SELECT ec.id AS category_id, ec.name AS category_name, ec.status AS category_status,
            c.id AS candidate_id, c.name AS candidate_name, c.branch, COUNT(v.id) AS votes
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id
     LEFT JOIN votes v ON v.candidate_id = c.id
     GROUP BY ec.id, ec.name, ec.status, c.id, c.name, c.branch
     ORDER BY ec.name, votes DESC, c.name'
)->fetchAll();

$grouped = [];
foreach ($rows as $row) {
    $grouped[$row['category_id']]['name'] = $row['category_name'];
    $grouped[$row['category_id']]['status'] = $row['category_status'];
    if ($row['candidate_id']) {
        $grouped[$row['category_id']]['candidates'][] = $row;
    }
}

render_header('Results', 'admin');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Live analysis</p>
        <h1>Election results</h1>
        <p>Votes are counted directly from the vote records, so no separate analyze table is needed.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('actions/export_results.php') ?>"><i class="bi bi-download"></i> Export CSV</a>
</section>

<section class="results-grid">
    <?php foreach ($grouped as $category): ?>
        <?php
        $candidates = $category['candidates'] ?? [];
        $total = array_sum(array_map(static fn ($item) => (int) $item['votes'], $candidates));
        ?>
        <article class="result-card">
            <div class="panel-header">
                <h2><?= e($category['name']) ?></h2>
                <span class="badge badge-<?= status_label($category['status']) ?>"><?= e($category['status']) ?></span>
            </div>
            <div class="result-bars">
                <?php foreach ($candidates as $candidate): ?>
                    <?php
                    $votes = (int) $candidate['votes'];
                    $percent = $total > 0 ? round(($votes / $total) * 100) : 0;
                    ?>
                    <div class="result-row">
                        <div class="result-label">
                            <strong><?= e($candidate['candidate_name']) ?></strong>
                            <span><?= $votes ?> votes · <?= $percent ?>%</span>
                        </div>
                        <div class="bar"><span style="width: <?= $percent ?>%"></span></div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$candidates): ?>
                    <p class="muted">No candidates enrolled yet.</p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php render_footer(); ?>
