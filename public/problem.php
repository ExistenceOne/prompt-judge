<?php
require __DIR__ . '/../src/bootstrap.php';

$id = query_int('id');
$problem = db_run('SELECT * FROM problems WHERE id = ?', [$id])->fetch();

if (!$problem) {
    http_response_code(404);
    render_header('찾을 수 없음');
    echo '<h1>문제를 찾을 수 없습니다</h1>';
    render_footer();
    exit;
}

render_header($problem['title']);
?>
<article class="problem">
    <h1>#<?= (int) $problem['id'] ?> · <?= e($problem['title']) ?></h1>

    <div class="limits">
        <span>시간 제한: <strong><?= (int) $problem['time_limit_ms'] ?> ms</strong></span>
        <span>메모리 제한: <strong><?= (int) $problem['memory_limit_kb'] ?> KB</strong></span>
        <span>입력 토큰 제한: <strong><?= (int) $problem['input_token_limit'] ?></strong></span>
        <span>출력 토큰 제한: <strong><?= (int) $problem['output_token_limit'] ?></strong></span>
    </div>

    <h2>문제 설명</h2>
    <p class="prose"><?= nl2br(e($problem['description'])) ?></p>

    <?php if (!empty($problem['input_format'])): ?>
        <h2>입력</h2>
        <p class="prose"><?= nl2br(e($problem['input_format'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($problem['output_format'])): ?>
        <h2>출력</h2>
        <p class="prose"><?= nl2br(e($problem['output_format'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($problem['sample_input']) || !empty($problem['sample_output'])): ?>
        <h2>예제</h2>
        <div class="samples">
            <div>
                <h3>입력</h3>
                <pre><?= e($problem['sample_input']) ?></pre>
            </div>
            <div>
                <h3>출력</h3>
                <pre><?= e($problem['sample_output']) ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <p>
        <a class="btn btn-primary" href="<?= e(url('submit.php?problem_id=' . $problem['id'])) ?>">
            프롬프트 작성
        </a>
    </p>
</article>
<?php
render_footer();
