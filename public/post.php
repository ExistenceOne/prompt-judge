<?php
require __DIR__ . '/../src/bootstrap.php';

$id = query_int('id');
$post = find_post($id);

if (!$post) {
    http_response_code(404);
    render_header('Not Found');
    echo '<h1>Post not found</h1>';
    render_footer();
    exit;
}

$user     = current_user();
$userId   = $user ? (int) $user['id'] : 0;
$comments = post_comments($id);

render_header($post['title']);
?>
<article class="post">
    <div class="page-head">
        <h1><?= e($post['title']) ?></h1>
        <?php if ($user && owns($post, $userId)): ?>
            <span class="owner-actions">
                <a class="btn btn-sm" href="<?= e(url('post_form.php?id=' . $post['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(url('post_delete.php')) ?>" class="inline"
                      onsubmit="return confirm('Delete this post?');">
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                </form>
            </span>
        <?php endif; ?>
    </div>

    <p class="post-meta">
        <span class="tag tag-<?= e($post['category']) ?>"><?= e(board_category_label($post['category'])) ?></span>
        by <strong><?= e($post['author_name']) ?></strong> <span class="muted">@<?= e($post['username']) ?></span>
        · <?= e($post['created_at']) ?>
        <?php if ($post['updated_at'] !== null): ?>
            <span class="muted">(edited)</span>
        <?php endif; ?>
        <?php if ($post['problem_id'] !== null): ?>
            · related: <a href="<?= e(url('problem.php?id=' . $post['problem_id'])) ?>">Problem #<?= (int) $post['problem_id'] ?></a>
        <?php endif; ?>
    </p>

    <div class="post-body prose"><?= nl2br(e($post['body'])) ?></div>
</article>

<section class="comments" id="comments">
    <h2>Comments <span class="muted">(<?= count($comments) ?>)</span></h2>

    <?php if (!$comments): ?>
        <p class="muted">No comments yet.</p>
    <?php else: foreach ($comments as $c): ?>
        <div class="comment">
            <div class="comment-head">
                <strong><?= e($c['author_name']) ?></strong> <span class="muted">@<?= e($c['username']) ?></span>
                · <span class="muted"><?= e($c['created_at']) ?><?= $c['updated_at'] !== null ? ' (edited)' : '' ?></span>
                <?php if ($user && owns($c, $userId)): ?>
                    <span class="owner-actions">
                        <a href="<?= e(url('comment_edit.php?id=' . $c['id'])) ?>">Edit</a>
                        <form method="post" action="<?= e(url('comment_delete.php')) ?>" class="inline"
                              onsubmit="return confirm('Delete this comment?');">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="linklike" type="submit">Delete</button>
                        </form>
                    </span>
                <?php endif; ?>
            </div>
            <div class="comment-body"><?= nl2br(e($c['body'])) ?></div>
        </div>
    <?php endforeach; endif; ?>

    <?php if ($user): ?>
        <form method="post" action="<?= e(url('comment_create.php')) ?>" class="comment-form">
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <textarea name="body" rows="3" placeholder="Write a comment…" required></textarea>
            <button class="btn btn-primary" type="submit">Post comment</button>
        </form>
    <?php else: ?>
        <p class="muted"><a href="<?= e(url('login.php')) ?>">Log in</a> to leave a comment.</p>
    <?php endif; ?>
</section>
<?php
render_footer();
