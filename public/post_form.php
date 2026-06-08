<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

$editId = query_int('id');                 // >0 when editing
$editing = $editId > 0;
$post = null;

if ($editing) {
    $post = find_post($editId);
    if (!$post) {
        http_response_code(404);
        render_header('Not Found');
        echo '<h1>Post not found</h1>';
        render_footer();
        exit;
    }
    if (!owns($post, (int) $user['id'])) {
        flash('You can only edit your own posts.', 'bad');
        redirect('post.php?id=' . $editId);
    }
}

// Form values: from the post when editing, else defaults / query prefill.
$values = [
    'category'   => $post['category']   ?? (is_valid_category((string) ($_GET['category'] ?? '')) ? $_GET['category'] : 'free'),
    'title'      => $post['title']      ?? '',
    'body'       => $post['body']       ?? '',
    'problem_id' => $post['problem_id'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['category']   = post('category');
    $values['title']      = post('title');
    $values['body']       = post('body');
    $values['problem_id'] = post('problem_id');

    $errors = [];
    if (!is_valid_category($values['category'])) {
        $errors[] = 'Please choose a valid category.';
    }
    if ($values['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if ($values['body'] === '') {
        $errors[] = 'Content is required.';
    }

    // Optional problem link: blank => null; otherwise must be a real problem.
    $problemId = null;
    if ($values['problem_id'] !== '') {
        if (!ctype_digit($values['problem_id'])) {
            $errors[] = 'Related Problem ID must be a number.';
        } else {
            $problemId = (int) $values['problem_id'];
            if (!db_run('SELECT 1 FROM problems WHERE id = ?', [$problemId])->fetch()) {
                $errors[] = "Problem #{$problemId} does not exist.";
            }
        }
    }

    if ($errors) {
        foreach ($errors as $err) {
            flash($err, 'bad');
        }
    } elseif ($editing) {
        db_run(
            'UPDATE posts SET category = ?, problem_id = ?, title = ?, body = ?, updated_at = NOW()
             WHERE id = ?',
            [$values['category'], $problemId, $values['title'], $values['body'], $editId]
        );
        flash('Post updated.', 'ok');
        redirect('post.php?id=' . $editId);
    } else {
        db_run(
            'INSERT INTO posts (user_id, category, problem_id, title, body)
             VALUES (?, ?, ?, ?, ?)',
            [$user['id'], $values['category'], $problemId, $values['title'], $values['body']]
        );
        $newId = (int) db()->lastInsertId();
        flash('Post created.', 'ok');
        redirect('post.php?id=' . $newId);
    }
}

render_header($editing ? 'Edit Post' : 'New Post');
?>
<h1><?= $editing ? 'Edit Post' : 'New Post' ?></h1>
<form method="post" class="card form">
    <label>Category
        <select name="category">
            <?php foreach (board_categories() as $code => $label): ?>
                <option value="<?= e($code) ?>" <?= $values['category'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Related Problem ID <span class="muted">(optional)</span>
        <input type="number" name="problem_id" min="1" value="<?= e((string) $values['problem_id']) ?>" placeholder="e.g. 1">
    </label>
    <label>Title
        <input type="text" name="title" value="<?= e($values['title']) ?>" required maxlength="200">
    </label>
    <label>Content
        <textarea name="body" rows="12" required><?= e($values['body']) ?></textarea>
    </label>
    <button class="btn btn-primary" type="submit"><?= $editing ? 'Save changes' : 'Publish' ?></button>
    <a class="btn" href="<?= e(url($editing ? 'post.php?id=' . $editId : 'board.php')) ?>">Cancel</a>
</form>
<?php
render_footer();
