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
    'temperature'     => '1.0',
    'thinking_budget' => '0',
    'prompt'          => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['model']           = post('model', $form['model']);
    $form['language_id']     = (int) post('language_id', (string) $form['language_id']);
    $form['temperature']     = post('temperature', $form['temperature']);
    $form['thinking_budget'] = post('thinking_budget', $form['thinking_budget']);
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
    $thinkingBudget = is_numeric($form['thinking_budget']) ? (int) $form['thinking_budget'] : 0;
    if ($thinkingBudget !== 0 && $thinkingBudget < 1024) {
        $errors[] = 'Thinking budget must be 0 (off) or at least 1024 tokens.';
    }

    if (!$errors) {
        $user = current_user();
        $submissionId = run_submission(
            (int) $user['id'],
            $problem,
            $form['language_id'],
            $form['model'],
            is_numeric($form['temperature']) ? (float) $form['temperature'] : null,
            $thinkingBudget !== 0 ? $thinkingBudget : null,
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

    <div class="grid-2">
        <label>Temperature: <output id="temp-val"><?= e($form['temperature']) ?></output>
            <input type="range" name="temperature" min="0" max="1" step="0.05"
                   value="<?= e($form['temperature']) ?>" oninput="document.getElementById('temp-val').textContent=this.value">
        </label>
        <label>Thinking budget (tokens): <output id="budget-val"><?= e($form['thinking_budget']) ?></output>
            <input type="range" name="thinking_budget" min="0" max="12000" step="1024"
                   value="<?= e($form['thinking_budget']) ?>" oninput="document.getElementById('budget-val').textContent=this.value">
        </label>
    </div>
    <p class="muted small">
        Note: 0 disables extended thinking. Otherwise, at least 1024 tokens are required.
        When extended thinking is enabled, temperature is ignored (the API does not allow both).
    </p>

    <label>Prompt
        <textarea name="prompt" rows="12" placeholder="Describe the program the AI should write. It must read stdin and print to stdout."><?= e($form['prompt']) ?></textarea>
    </label>

    <button class="btn btn-primary" type="submit">Generate &amp; Judge</button>
    <p class="muted small">This calls Claude and Judge0 and may take a few seconds.</p>
</form>
<?php
render_footer();
