<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('board.php');
}

$id      = (int) post('id', '0');
$comment = $id > 0 ? find_comment($id) : null;

if (!$comment) {
    flash('Comment not found.', 'bad');
    redirect('board.php');
}
if (!owns($comment, (int) $user['id'])) {
    flash('You can only delete your own comments.', 'bad');
    redirect('post.php?id=' . $comment['post_id']);
}

$postId = (int) $comment['post_id'];
db_run('DELETE FROM comments WHERE id = ?', [$id]);
flash('Comment deleted.', 'ok');
redirect('post.php?id=' . $postId . '#comments');
