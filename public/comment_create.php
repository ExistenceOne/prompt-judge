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
    flash('게시글을 찾을 수 없습니다.', 'bad');
    redirect('board.php');
}
if ($body === '') {
    flash('댓글은 비워둘 수 없습니다.', 'bad');
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
    $user['name'] . '님이 귀하의 게시글 "' . $post['title'] . '"에 댓글을 남겼습니다.'
);

flash('댓글이 등록되었습니다.', 'ok');
redirect('post.php?id=' . $postId . '#comments');
