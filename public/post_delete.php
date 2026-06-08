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
    flash('Post not found.', 'bad');
    redirect('board.php');
}
if (!owns($post, (int) $user['id'])) {
    flash('You can only delete your own posts.', 'bad');
    redirect('post.php?id=' . $id);
}

// Comments and notifications pointing at this post cascade away via FK.
db_run('DELETE FROM posts WHERE id = ?', [$id]);
flash('Post deleted.', 'ok');
redirect('board.php');
