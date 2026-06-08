<?php
require __DIR__ . '/../src/bootstrap.php';

$q      = trim((string) ($_GET['q'] ?? ''));        // user id or problem id
$result = trim((string) ($_GET['result'] ?? ''));   // result label filter

$sql = '
    SELECT s.*, u.username
    FROM submissions s
    JOIN users u ON u.id = s.user_id
    WHERE 1 = 1
';
$params = [];

if ($q !== '') {
    $sql .= ' AND (CAST(s.user_id AS CHAR) = ? OR CAST(s.problem_id AS CHAR) = ? OR u.username LIKE ?)';
    $params[] = $q;
    $params[] = $q;
    $params[] = '%' . $q . '%';
}
if ($result !== '') {
    $sql .= ' AND s.result = ?';
    $params[] = $result;
}
$sql .= ' ORDER BY s.id DESC LIMIT 200';

$rows = db_run($sql, $params)->fetchAll();

$resultLabels = ['AC', 'WA', 'TLE', 'MLE', 'ITLE', 'OTLE', 'CE', 'RE', 'IE'];

render_header('Judging History');
?>
<h1>Judging History</h1>

<form method="get" class="searchbar">
    <input type="text" name="q" placeholder="Search by User ID / Problem ID / username" value="<?= e($q) ?>">
    <select name="result">
        <option value="">All results</option>
        <?php foreach ($resultLabels as $r): ?>
            <option value="<?= e($r) ?>" <?= $r === $result ? 'selected' : '' ?>><?= e($r) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Filter</button>
</form>

<div class="table-scroll">
<table class="data">
    <thead>
        <tr>
            <th>ID</th><th>User</th><th>Problem</th><th>Result</th>
            <th>Time</th><th>Memory</th><th>Tokens (in/out)</th>
            <th>Language</th><th>Code Size</th><th>Submitted</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="10" class="muted">No submissions yet.</td></tr>
    <?php else: foreach ($rows as $s):
        $meta = result_meta($s['result']); ?>
        <tr>
            <td><a href="<?= e(url('judging.php?id=' . $s['id'])) ?>"><?= (int) $s['id'] ?></a></td>
            <td><?= e($s['username']) ?></td>
            <td><a href="<?= e(url('problem.php?id=' . $s['problem_id'])) ?>">#<?= (int) $s['problem_id'] ?></a></td>
            <td><span class="verdict verdict-<?= e($meta['kind']) ?>" title="<?= e($meta['text']) ?>"><?= e($meta['label']) ?></span></td>
            <td><?= $s['exec_time_ms'] !== null ? (int) $s['exec_time_ms'] . ' ms' : '—' ?></td>
            <td><?= $s['memory_kb'] !== null ? (int) $s['memory_kb'] . ' KB' : '—' ?></td>
            <td><?= ($s['input_tokens'] ?? '—') ?> / <?= ($s['output_tokens'] ?? '—') ?></td>
            <td><?= e($s['language_name']) ?></td>
            <td><?= $s['code_size'] !== null ? (int) $s['code_size'] . ' B' : '—' ?></td>
            <td><?= e($s['created_at']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
<?php
render_footer();
