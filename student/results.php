<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_student();

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

render_header('Results', 'student');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Results</p>
        <h1>Live election results</h1>
        <p>Current vote count for every category.</p>
    </div>
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
                <span><?= $total ?> total votes</span>
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
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php render_footer(); ?>
