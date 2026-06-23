<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$categories = db()->query(
    'SELECT ec.*,
            COUNT(DISTINCT c.id) AS candidate_count,
            COUNT(DISTINCT v.id) AS vote_count
     FROM election_categories ec
     LEFT JOIN candidates c ON c.category_id = ec.id
     LEFT JOIN votes v ON v.category_id = ec.id
     GROUP BY ec.id, ec.name, ec.description, ec.starts_at, ec.ends_at, ec.status, ec.created_at
     ORDER BY ec.created_at DESC'
)->fetchAll();
$candidates = db()->query(
    'SELECT c.*, ec.name AS category_name, COUNT(v.id) AS vote_count
     FROM candidates c
     INNER JOIN election_categories ec ON ec.id = c.category_id
     LEFT JOIN votes v ON v.candidate_id = c.id
     GROUP BY c.id, c.category_id, c.name, c.roll_number, c.branch, c.manifesto, c.photo_path, c.status, c.created_at, ec.name
     ORDER BY ec.name, c.created_at DESC'
)->fetchAll();

render_header('Candidates', 'admin');
?>

<section class="page-title">
    <div>
        <p class="eyebrow">Ballot builder</p>
        <h1>Categories and candidates</h1>
        <p>Create election categories, enroll candidates, and control who appears on the ballot.</p>
    </div>
</section>

<section class="two-column forms-layout">
    <form class="panel form-stack" action="<?= url('actions/save_category.php') ?>" method="post">
        <?= csrf_field() ?>
        <div class="panel-header">
            <h2>New Category</h2>
            <span class="badge badge-success">Election</span>
        </div>
        <label>
            <span>Category Name</span>
            <input type="text" name="name" maxlength="120" pattern="[A-Za-z0-9][A-Za-z0-9 &'().-]{2,119}" required>
        </label>
        <label>
            <span>Description</span>
            <textarea name="description" rows="3" maxlength="500"></textarea>
        </label>
        <div class="form-grid compact-grid">
            <label>
                <span>Starts At</span>
                <input type="datetime-local" name="starts_at" required>
            </label>
            <label>
                <span>Ends At</span>
                <input type="datetime-local" name="ends_at" required>
            </label>
        </div>
        <label>
            <span>Status</span>
            <select name="status" required>
                <option value="">Select status</option>
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="closed">Closed</option>
            </select>
        </label>
        <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i> Create Category</button>
    </form>

    <form class="panel form-stack" action="<?= url('actions/save_candidate.php') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="panel-header">
            <h2>Enroll Candidate</h2>
            <span class="badge badge-warning">Ballot</span>
        </div>
        <label>
            <span>Category</span>
            <select name="category_id" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-grid compact-grid">
            <label>
                <span>Name</span>
                <input type="text" name="name" maxlength="120" pattern="[A-Za-z][A-Za-z .'-]{1,119}" required>
            </label>
            <label>
                <span>Roll Number</span>
                <input type="text" name="roll_number" maxlength="30" pattern="[A-Za-z0-9-]{3,30}">
            </label>
        </div>
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
            <span>Manifesto</span>
            <textarea name="manifesto" rows="4" minlength="20" maxlength="700" required></textarea>
        </label>
        <label>
            <span>Photo</span>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        </label>
        <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Save Candidate</button>
    </form>
</section>

<section class="section-wrap tight-top">
    <div class="panel">
        <div class="panel-header">
            <h2>Category List</h2>
            <span><?= count($categories) ?> records</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Candidates</th>
                    <th>Votes</th>
                    <th>Delete</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <?php $state = category_state($category); ?>
                    <tr>
                        <td>#<?= (int) $category['id'] ?></td>
                        <td>
                            <strong><?= e($category['name']) ?></strong>
                            <small class="table-note"><?= e($category['description'] ?? '') ?></small>
                        </td>
                        <td>
                            <small class="table-note">Start: <?= format_datetime($category['starts_at']) ?></small>
                            <small class="table-note">End: <?= format_datetime($category['ends_at']) ?></small>
                        </td>
                        <td><span class="badge badge-<?= status_label($state) ?>"><?= e($state) ?></span></td>
                        <td><?= (int) $category['candidate_count'] ?></td>
                        <td><?= (int) $category['vote_count'] ?></td>
                        <td>
                            <form action="<?= url('actions/delete_category.php') ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this category? Related candidates and votes will also be deleted.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                                <button class="btn btn-small btn-danger" type="submit"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-wrap tight-top">
    <div class="panel">
        <div class="panel-header">
            <h2>Candidate List</h2>
            <span><?= count($candidates) ?> records</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Category</th>
                    <th>Branch</th>
                    <th>Votes</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td>
                            <div class="person-cell">
                                <?php if ($candidate['photo_path']): ?>
                                    <img src="<?= url($candidate['photo_path']) ?>" alt="<?= e($candidate['name']) ?>">
                                <?php else: ?>
                                    <span class="avatar"><?= e(initials($candidate['name'])) ?></span>
                                <?php endif; ?>
                                <div>
                                    <strong><?= e($candidate['name']) ?></strong>
                                    <small><?= e($candidate['roll_number'] ?? '-') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($candidate['category_name']) ?></td>
                        <td><?= e($candidate['branch']) ?></td>
                        <td><?= (int) $candidate['vote_count'] ?></td>
                        <td><span class="badge badge-<?= status_label($candidate['status']) ?>"><?= e($candidate['status']) ?></span></td>
                        <td>
                            <form action="<?= url('actions/update_candidate_status.php') ?>" method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="candidate_id" value="<?= (int) $candidate['id'] ?>">
                                <input type="hidden" name="status" value="<?= $candidate['status'] === 'active' ? 'inactive' : 'active' ?>">
                                <button class="btn btn-small btn-light" type="submit">
                                    <?= $candidate['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
