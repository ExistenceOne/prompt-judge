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

render_header('문제 모음');
?>
<h1>문제 목록</h1>

<form method="get" class="searchbar">
    <input type="text" name="q" placeholder="문제 ID 또는 제목으로 검색" value="<?= e($search) ?>">
    <button class="btn" type="submit">검색</button>
</form>

<table class="data">
    <thead>
        <tr><th>ID</th><th>제목</th><th>제출수</th><th>맞은 사람 수</th></tr>
    </thead>
    <tbody>
    <?php if (!$problems): ?>
        <tr><td colspan="4" class="muted">문제를 찾을 수 없습니다.</td></tr>
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
