<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

$id      = query_int('id');
$comment = $id > 0 ? find_comment($id) : null;

if (!$comment) {
    http_response_code(404);
    render_header('찾을 수 없음');
    echo '<h1>댓글을 찾을 수 없습니다</h1>';
    render_footer();
    exit;
}
if (!owns($comment, (int) $user['id'])) {
    flash('자신의 댓글만 수정할 수 있습니다.', 'bad');
    redirect('post.php?id=' . $comment['post_id']);
}

$body = $comment['body'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = post('body');
    if ($body === '') {
        flash('댓글은 비워둘 수 없습니다.', 'bad');
    } else {
        db_run('UPDATE comments SET body = ?, updated_at = NOW() WHERE id = ?', [$body, $id]);
        flash('댓글이 수정되었습니다.', 'ok');
        redirect('post.php?id=' . $comment['post_id'] . '#comments');
    }
}

render_header('댓글 수정');
?>
<h1>댓글 수정</h1>
<form method="post" class="card form">
    <label>댓글
        <textarea name="body" rows="5" required><?= e($body) ?></textarea>
    </label>
    <button class="btn btn-primary" type="submit">변경사항 저장</button>
    <a class="btn" href="<?= e(url('post.php?id=' . $comment['post_id'] . '#comments')) ?>">취소</a>
</form>
<?php
render_footer();
