<?php
require __DIR__ . '/../src/bootstrap.php';
require_once SRC_PATH . '/judge0.php';

$id = query_int('id');
$s  = db_run(
    'SELECT s.*, u.username, p.title AS problem_title
     FROM submissions s
     JOIN users u ON u.id = s.user_id
     JOIN problems p ON p.id = s.problem_id
     WHERE s.id = ?',
    [$id]
)->fetch();

if (!$s) {
    http_response_code(404);
    render_header('찾을 수 없음');
    echo '<h1>제출 기록을 찾을 수 없습니다</h1>';
    render_footer();
    exit;
}

$meta = result_meta($s['result']);

render_header('채점 #' . $s['id']);
?>
<h1>세부 채점 내역 #<?= (int) $s['id'] ?></h1>

<div class="verdict-banner verdict-<?= e($meta['kind']) ?>">
    <strong><?= e($meta['label']) ?></strong> — <?= e($meta['text']) ?>
</div>

<table class="kv">
    <tr><th>문제</th><td><a href="<?= e(url('problem.php?id=' . $s['problem_id'])) ?>">#<?= (int) $s['problem_id'] ?> · <?= e($s['problem_title']) ?></a></td></tr>
    <tr><th>사용자</th><td><?= e($s['username']) ?></td></tr>
    <tr><th>모델</th><td><?= e($s['model']) ?></td></tr>
    <tr><th>언어</th><td><?= e($s['language_name']) ?></td></tr>
    <tr><th>Temperature</th><td><?= e((string) ($s['temperature'] ?? '—')) ?></td></tr>
    <tr><th>Thinking Budget</th><td><?= $s['thinking_budget'] !== null ? (int) $s['thinking_budget'] . ' tokens' : 'Off' ?></td></tr>
    <tr><th>토큰 (입력 / 출력)</th><td><?= ($s['input_tokens'] ?? '—') ?> / <?= ($s['output_tokens'] ?? '—') ?></td></tr>
    <tr><th>실행 시간</th><td><?= $s['exec_time_ms'] !== null ? (int) $s['exec_time_ms'] . ' ms' : '—' ?></td></tr>
    <tr><th>메모리</th><td><?= $s['memory_kb'] !== null ? (int) $s['memory_kb'] . ' KB' : '—' ?></td></tr>
    <tr><th>코드 크기</th><td><?= $s['code_size'] !== null ? (int) $s['code_size'] . ' 바이트' : '—' ?></td></tr>
    <?php if ($s['judge0_status_id'] !== null): ?>
        <tr><th>Judge0 상태</th><td><?= e(judge0_status_text((int) $s['judge0_status_id'])) ?></td></tr>
    <?php endif; ?>
    <tr><th>제출 시간</th><td><?= e($s['created_at']) ?></td></tr>
</table>

<h2>프롬프트</h2>
<pre class="block"><?= e($s['prompt']) ?></pre>

<?php if (!empty($s['generated_code'])): ?>
    <h2>생성된 소스 코드</h2>
    <pre class="block code"><?= e($s['generated_code']) ?></pre>
<?php endif; ?>

<?php if (!empty($s['compile_output'])): ?>
    <h2>컴파일러 출력</h2>
    <pre class="block error"><?= e($s['compile_output']) ?></pre>
<?php endif; ?>

<?php if (!empty($s['stderr'])): ?>
    <h2>에러 로그</h2>
    <pre class="block error"><?= e($s['stderr']) ?></pre>
<?php endif; ?>
<?php
render_footer();
