<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$statement = db()->query(
    'SELECT ec.name AS category, c.name AS candidate, c.branch, COUNT(v.id) AS votes
     FROM candidates c
     INNER JOIN election_categories ec ON ec.id = c.category_id
     LEFT JOIN votes v ON v.candidate_id = c.id
     GROUP BY c.id, ec.name, c.name, c.branch
     ORDER BY ec.name, votes DESC, c.name'
);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="campus-vote-results.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Category', 'Candidate', 'Branch', 'Votes']);

foreach ($statement as $row) {
    fputcsv($output, [$row['category'], $row['candidate'], $row['branch'], $row['votes']]);
}

fclose($output);
exit;
