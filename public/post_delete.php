<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('board.php');
}

$id   = (int) post('id', '0');
$post = $id > 0 ? find_post($id) : null;

if (!$post) {
    flash('게시글을 찾을 수 없습니다.', 'bad');
    redirect('board.php');
}
if (!owns($post, (int) $user['id'])) {
    flash('자신의 게시글만 삭제할 수 있습니다.', 'bad');
    redirect('post.php?id=' . $id);
}

// Comments and notifications pointing at this post cascade away via FK.
db_run('DELETE FROM posts WHERE id = ?', [$id]);
flash('게시글이 삭제되었습니다.', 'ok');
redirect('board.php');
