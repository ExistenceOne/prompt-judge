<?php
require __DIR__ . '/../src/bootstrap.php';

$search = trim((string) ($_GET['q'] ?? ''));

// List problems with submission / accepted counts. Search by id or title.
$sql = "
    SELECT p.id, p.title,
           COUNT(s.id) AS total,
           SUM(CASE WHEN s.result = 'AC' THEN 1 ELSE 0 END) AS accepted
    FROM problems p
    LEFT JOIN submissions s ON s.problem_id = p.id
";
$params = [];
if ($search !== '') {
    $sql .= ' WHERE p.title LIKE ? OR CAST(p.id AS CHAR) = ? ';
    $params[] = '%' . $search . '%';
    $params[] = $search;
}
$sql .= ' GROUP BY p.id, p.title ORDER BY p.id';

$problems = db_run($sql, $params)->fetchAll();

render_header('Problems');
?>
<h1>Problem List</h1>

<form method="get" class="searchbar">
    <input type="text" name="q" placeholder="Search by Problem ID or Title" value="<?= e($search) ?>">
    <button class="btn" type="submit">Search</button>
</form>

<table class="data">
    <thead>
        <tr><th>ID</th><th>Title</th><th>Submissions</th><th>Accepted</th></tr>
    </thead>
    <tbody>
    <?php if (!$problems): ?>
        <tr><td colspan="4" class="muted">No problems found.</td></tr>
    <?php else: foreach ($problems as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><a href="<?= e(url('problem.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a></td>
            <td><?= (int) $p['total'] ?></td>
            <td><?= (int) $p['accepted'] ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
<?php
render_footer();
