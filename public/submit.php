<?php
require __DIR__ . '/../src/bootstrap.php';
require_once SRC_PATH . '/pipeline.php';

require_login();

$problemId = query_int('problem_id');
$problem   = db_run('SELECT * FROM problems WHERE id = ?', [$problemId])->fetch();

if (!$problem) {
    http_response_code(404);
    render_header('Not Found');
    echo '<h1>Problem not found</h1>';
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
        $errors[] = 'Please choose a valid model.';
    }
    if (judge0_language_name($form['language_id']) === 'Unknown') {
        $errors[] = 'Please choose a valid target language.';
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

render_header('Submit · ' . $problem['title']);
?>
<h1>Submit a Prompt</h1>
<p class="muted">
    Problem: <a href="<?= e(url('problem.php?id=' . $problem['id'])) ?>">#<?= (int) $problem['id'] ?> · <?= e($problem['title']) ?></a>
    — Token limits: in <?= (int) $problem['input_token_limit'] ?> / out <?= (int) $problem['output_token_limit'] ?>
</p>

<form method="post" class="card form">
    <div class="grid-2">
        <label>Model
            <select name="model">
                <?php foreach ($models as $id => $m): ?>
                    <option value="<?= e($id) ?>" <?= $id === $form['model'] ? 'selected' : '' ?>>
                        <?= e($m['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Target Language
            <select name="language_id">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= (int) $lang['id'] ?>" <?= (int) $lang['id'] === $form['language_id'] ? 'selected' : '' ?>>
                        <?= e($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <label>Prompt
        <textarea name="prompt" rows="12" placeholder="Describe the program the AI should write. It must read stdin and print to stdout."><?= e($form['prompt']) ?></textarea>
    </label>

    <button class="btn btn-primary" type="submit">Generate &amp; Judge</button>
    <p class="muted small">This calls Claude and Judge0 and may take a few seconds.</p>
</form>
<?php
render_footer();
