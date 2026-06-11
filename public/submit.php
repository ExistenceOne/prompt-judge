<?php
require __DIR__ . '/../src/bootstrap.php';
require_once SRC_PATH . '/pipeline.php';

require_login();

$problemId = query_int('problem_id');
$problem   = db_run('SELECT * FROM problems WHERE id = ?', [$problemId])->fetch();

if (!$problem) {
    http_response_code(404);
    render_header('찾을 수 없음');
    echo '<h1>문제를 찾을 수 없습니다.</h1>';
    render_footer();
    exit;
}

$models    = model_options();
$languages = judge0_languages();

// Form defaults / submitted values.
$form = [
    'model'           => array_key_first($models),
    'language_id'     => 71, // Python (3.8.1)
    'prompt'          => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['model']           = post('model', $form['model']);
    $form['language_id']     = (int) post('language_id', (string) $form['language_id']);
    $form['prompt']          = post('prompt');

    $errors = [];
    if (!isset($models[$form['model']])) {
        $errors[] = '올바른 모델을 선택해 주세요.';
    }
    if (judge0_language_name($form['language_id']) === 'Unknown') {
        $errors[] = '올바른 대상 언어를 선택해 주세요.';
    }
    if ($form['prompt'] === '') {
        $errors[] = 'Your prompt cannot be empty.';
    }

    if (!$errors) {
        $user = current_user();
        $submissionId = run_submission(
            (int) $user['id'],
            $problem,
            $form['language_id'],
            $form['model'],
            1.0,
            null,
            $form['prompt']
        );
        redirect('judging.php?id=' . $submissionId);
    }

    foreach ($errors as $err) {
        flash($err, 'bad');
    }
}

render_header('제출 · ' . $problem['title']);
?>
<h1>프롬프트 작성</h1>
<p class="muted">
    문제: <a href="<?= e(url('problem.php?id=' . $problem['id'])) ?>">#<?= (int) $problem['id'] ?> · <?= e($problem['title']) ?></a>
    — 토큰 제한: 입력 <?= (int) $problem['input_token_limit'] ?> / 출력 <?= (int) $problem['output_token_limit'] ?>
</p>

<form method="post" class="card form">
    <div class="grid-2">
        <label>모델
            <select name="model">
                <?php foreach ($models as $id => $m): ?>
                    <option value="<?= e($id) ?>" <?= $id === $form['model'] ? 'selected' : '' ?>>
                        <?= e($m['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>대상 언어
            <select name="language_id">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= (int) $lang['id'] ?>" <?= (int) $lang['id'] === $form['language_id'] ? 'selected' : '' ?>>
                        <?= e($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <label>프롬프트
        <textarea name="prompt" rows="12" placeholder="AI가 작성해야 할 프로그램을 설명하세요. 표준 입력(stdin)에서 읽고 표준 출력(stdout)으로 내용을 출력해야 합니다."><?= e($form['prompt']) ?></textarea>
    </label>

    <button class="btn btn-primary" type="submit">생성 및 채점</button>
    <p class="muted small">요청을 처리하는 데 몇 초 정도 걸릴 수 있습니다.</p>
</form>
<?php
render_footer();
