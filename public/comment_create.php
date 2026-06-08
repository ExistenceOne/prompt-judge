<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('board.php');
}

$postId = (int) post('post_id', '0');
$body   = post('body');
$post   = $postId > 0 ? find_post($postId) : null;

if (!$post) {
    flash('Post not found.', 'bad');
    redirect('board.php');
}
if ($body === '') {
    flash('Comment cannot be empty.', 'bad');
    redirect('post.php?id=' . $postId);
}

db_run(
    'INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)',
    [$postId, $user['id'], $body]
);

// Notify the post's author about the reply (notify() skips self-comments).
notify(
    (int) $post['user_id'],
    (int) $user['id'],
    'comment',
    $postId,
    $user['name'] . ' commented on your post "' . $post['title'] . '"'
);

flash('Comment posted.', 'ok');
redirect('post.php?id=' . $postId . '#comments');
