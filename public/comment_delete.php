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
    flash('댓글을 찾을 수 없습니다.', 'bad');
    redirect('board.php');
}
if (!owns($comment, (int) $user['id'])) {
    flash('자신의 댓글만 삭제할 수 있습니다.', 'bad');
    redirect('post.php?id=' . $comment['post_id']);
}

$postId = (int) $comment['post_id'];
db_run('DELETE FROM comments WHERE id = ?', [$id]);
flash('댓글이 삭제되었습니다.', 'ok');
redirect('post.php?id=' . $postId . '#comments');
