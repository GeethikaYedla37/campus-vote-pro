<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$student = require_student();

$rows = db()->query(
    'SELECT ec.id AS category_id, ec.name AS category_name, ec.description, ec.starts_at, ec.ends_at,
            ec.status AS category_status, c.*
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id AND c.status = "active"
     ORDER BY ec.name, c.name'
)->fetchAll();

$votes = db()->prepare('SELECT category_id, candidate_id FROM votes WHERE student_id = ?');
$votes->execute([(int) $student['id']]);
$studentVotes = [];
foreach ($votes->fetchAll() as $vote) {
    $studentVotes[(int) $vote['category_id']] = (int) $vote['candidate_id'];
}

$grouped = [];
foreach ($rows as $row) {
    $grouped[$row['category_id']]['category'] = [
        'id' => $row['category_id'],
        'name' => $row['category_name'],
        'description' => $row['description'],
        'starts_at' => $row['starts_at'],
        'ends_at' => $row['ends_at'],
        'status' => $row['category_status'],
    ];

    if ($row['id']) {
        $grouped[$row['category_id']]['candidates'][] = $row;
    }
}

render_header('Vote', 'student');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Ballot</p>
        <h1>Cast your vote</h1>
        <p>Review candidate details carefully. Once submitted, one vote is locked for that category.</p>
    </div>
</section>

<section class="ballot-stack">
    <?php foreach ($grouped as $item): ?>
        <?php
        $category = $item['category'];
        $state = category_state($category);
        $isOpen = $state === 'open';
        $votedCandidateId = $studentVotes[(int) $category['id']] ?? null;
        $stateHelp = match ($state) {
            'scheduled' => 'Voting starts on ' . format_datetime($category['starts_at']) . '.',
            'closed' => 'Voting ended on ' . format_datetime($category['ends_at']) . '.',
            'draft' => 'Admin has not opened this category yet.',
            default => 'Voting is open now.',
        };
        ?>
        <article class="ballot-section">
            <div class="panel-header ballot-header">
                <div>
                    <h2><?= e($category['name']) ?></h2>
                    <p><?= e($category['description']) ?></p>
                </div>
                <span class="badge badge-<?= $votedCandidateId ? 'success' : status_label($state) ?>">
                    <?= $votedCandidateId ? 'voted' : e($state) ?>
                </span>
            </div>
            <?php if (!$votedCandidateId): ?>
                <p class="status-help"><?= e($stateHelp) ?></p>
            <?php endif; ?>

            <div class="candidate-grid">
                <?php foreach (($item['candidates'] ?? []) as $candidate): ?>
                    <?php $isSelected = $votedCandidateId === (int) $candidate['id']; ?>
                    <div class="candidate-card <?= $isSelected ? 'selected' : '' ?>">
                        <div class="candidate-media">
                            <?php if ($candidate['photo_path']): ?>
                                <img src="<?= url($candidate['photo_path']) ?>" alt="<?= e($candidate['name']) ?>">
                            <?php else: ?>
                                <span class="avatar avatar-large"><?= e(initials($candidate['name'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="candidate-body">
                            <span class="badge badge-neutral"><?= e($candidate['branch']) ?></span>
                            <h3><?= e($candidate['name']) ?></h3>
                            <p><?= e($candidate['manifesto']) ?></p>
                        </div>
                        <form action="<?= url('actions/cast_vote.php') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="candidate_id" value="<?= (int) $candidate['id'] ?>">
                            <button class="btn <?= $isSelected ? 'btn-success' : 'btn-primary' ?>" type="submit" <?= (!$isOpen || $votedCandidateId) ? 'disabled' : '' ?>>
                                <i class="bi <?= $isSelected ? 'bi-check-circle' : 'bi-check2-square' ?>"></i>
                                <?= $isSelected ? 'Selected' : 'Cast Vote' ?>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($item['candidates'])): ?>
                    <p class="muted">No candidates are available for this category.</p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php render_footer(); ?>
