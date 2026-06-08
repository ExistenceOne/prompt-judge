<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

$id      = query_int('id');
$comment = $id > 0 ? find_comment($id) : null;

if (!$comment) {
    http_response_code(404);
    render_header('Not Found');
    echo '<h1>Comment not found</h1>';
    render_footer();
    exit;
}
if (!owns($comment, (int) $user['id'])) {
    flash('You can only edit your own comments.', 'bad');
    redirect('post.php?id=' . $comment['post_id']);
}

$body = $comment['body'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = post('body');
    if ($body === '') {
        flash('Comment cannot be empty.', 'bad');
    } else {
        db_run('UPDATE comments SET body = ?, updated_at = NOW() WHERE id = ?', [$body, $id]);
        flash('Comment updated.', 'ok');
        redirect('post.php?id=' . $comment['post_id'] . '#comments');
    }
}

render_header('Edit Comment');
?>
<h1>Edit Comment</h1>
<form method="post" class="card form">
    <label>Comment
        <textarea name="body" rows="5" required><?= e($body) ?></textarea>
    </label>
    <button class="btn btn-primary" type="submit">Save changes</button>
    <a class="btn" href="<?= e(url('post.php?id=' . $comment['post_id'] . '#comments')) ?>">Cancel</a>
</form>
<?php
render_footer();
