<?php
require __DIR__ . '/../src/bootstrap.php';

$id = query_int('id');
$problem = db_run('SELECT * FROM problems WHERE id = ?', [$id])->fetch();

if (!$problem) {
    http_response_code(404);
    render_header('Not Found');
    echo '<h1>Problem not found</h1>';
    render_footer();
    exit;
}

render_header($problem['title']);
?>
<article class="problem">
    <h1>#<?= (int) $problem['id'] ?> · <?= e($problem['title']) ?></h1>

    <div class="limits">
        <span>Time Limit: <strong><?= (int) $problem['time_limit_ms'] ?> ms</strong></span>
        <span>Memory Limit: <strong><?= (int) $problem['memory_limit_kb'] ?> KB</strong></span>
        <span>Input Token Limit: <strong><?= (int) $problem['input_token_limit'] ?></strong></span>
        <span>Output Token Limit: <strong><?= (int) $problem['output_token_limit'] ?></strong></span>
    </div>

    <h2>Description</h2>
    <p class="prose"><?= nl2br(e($problem['description'])) ?></p>

    <?php if (!empty($problem['input_format'])): ?>
        <h2>Input</h2>
        <p class="prose"><?= nl2br(e($problem['input_format'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($problem['output_format'])): ?>
        <h2>Output</h2>
        <p class="prose"><?= nl2br(e($problem['output_format'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($problem['sample_input']) || !empty($problem['sample_output'])): ?>
        <h2>Sample</h2>
        <div class="samples">
            <div>
                <h3>Input</h3>
                <pre><?= e($problem['sample_input']) ?></pre>
            </div>
            <div>
                <h3>Output</h3>
                <pre><?= e($problem['sample_output']) ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <p>
        <a class="btn btn-primary" href="<?= e(url('submit.php?problem_id=' . $problem['id'])) ?>">
            Submit a prompt
        </a>
    </p>
</article>
<?php
render_footer();
