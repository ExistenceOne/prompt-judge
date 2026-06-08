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
        render_header('찾을 수 없음');
        echo '<h1>게시글을 찾을 수 없습니다</h1>';
        render_footer();
        exit;
    }
    if (!owns($post, (int) $user['id'])) {
        flash('자신의 게시글만 수정할 수 있습니다.', 'bad');
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
        $errors[] = '올바른 카테고리를 선택해 주세요.';
    }
    if ($values['title'] === '') {
        $errors[] = '제목이 필요합니다.';
    }
    if ($values['body'] === '') {
        $errors[] = '내용이 필요합니다.';
    }

    // Optional problem link: blank => null; otherwise must be a real problem.
    $problemId = null;
    if ($values['problem_id'] !== '') {
        if (!ctype_digit($values['problem_id'])) {
            $errors[] = '관련 문제 ID는 숫자여야 합니다.';
        } else {
            $problemId = (int) $values['problem_id'];
            if (!db_run('SELECT 1 FROM problems WHERE id = ?', [$problemId])->fetch()) {
                $errors[] = "문제 #{$problemId}이(가) 존재하지 않습니다.";
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
        flash('게시글이 수정되었습니다.', 'ok');
        redirect('post.php?id=' . $editId);
    } else {
        db_run(
            'INSERT INTO posts (user_id, category, problem_id, title, body)
             VALUES (?, ?, ?, ?, ?)',
            [$user['id'], $values['category'], $problemId, $values['title'], $values['body']]
        );
        $newId = (int) db()->lastInsertId();
        flash('게시글이 작성되었습니다.', 'ok');
        redirect('post.php?id=' . $newId);
    }
}

render_header($editing ? '게시글 수정' : '새 글 쓰기');
?>
<h1><?= $editing ? '게시글 수정' : '새 글 쓰기' ?></h1>
<form method="post" class="card form">
    <label>카테고리
        <select name="category">
            <?php foreach (board_categories() as $code => $label): ?>
                <option value="<?= e($code) ?>" <?= $values['category'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>관련 문제 ID <span class="muted">(선택사항)</span>
        <input type="number" name="problem_id" min="1" value="<?= e((string) $values['problem_id']) ?>" placeholder="예: 1">
    </label>
    <label>제목
        <input type="text" name="title" value="<?= e($values['title']) ?>" required maxlength="200">
    </label>
    <label>내용
        <textarea name="body" rows="12" required><?= e($values['body']) ?></textarea>
    </label>
    <button class="btn btn-primary" type="submit"><?= $editing ? '변경사항 저장' : '등록' ?></button>
    <a class="btn" href="<?= e(url($editing ? 'post.php?id=' . $editId : 'board.php')) ?>">취소</a>
</form>
<?php
render_footer();
