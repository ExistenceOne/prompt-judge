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

render_header('채점 기록');
?>
<h1>채점 기록</h1>

<form method="get" class="searchbar">
    <input type="text" name="q" placeholder="회원 ID / 문제 ID / 이름으로 검색" value="<?= e($q) ?>">
    <select name="result">
        <option value="">모든 결과</option>
        <?php foreach ($resultLabels as $r): ?>
            <option value="<?= e($r) ?>" <?= $r === $result ? 'selected' : '' ?>><?= e($r) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">필터링</button>
</form>

<div class="table-scroll">
<table class="data">
    <thead>
        <tr>
            <th>ID</th><th>사용자</th><th>문제</th><th>결과</th>
            <th>시간</th><th>메모리</th><th>토큰 (입/출력)</th>
            <th>언어</th><th>코드 크기</th><th>제출 시간</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="10" class="muted">제출 기록이 없습니다.</td></tr>
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
