<?php
require __DIR__ . '/../src/bootstrap.php';

$id = query_int('id');
$post = find_post($id);

if (!$post) {
    http_response_code(404);
    render_header('찾을 수 없음');
    echo '<h1>게시글을 찾을 수 없습니다</h1>';
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
                <a class="btn btn-sm" href="<?= e(url('post_form.php?id=' . $post['id'])) ?>">수정</a>
                <form method="post" action="<?= e(url('post_delete.php')) ?>" class="inline"
                      onsubmit="return confirm('이 게시글을 삭제하시겠습니까?');">
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">삭제</button>
                </form>
            </span>
        <?php endif; ?>
    </div>

    <p class="post-meta">
        <span class="tag tag-<?= e($post['category']) ?>"><?= e(board_category_label($post['category'])) ?></span>
        작성자 <strong><?= e($post['author_name']) ?></strong> <span class="muted">@<?= e($post['username']) ?></span>
        · <?= e($post['created_at']) ?>
        <?php if ($post['updated_at'] !== null): ?>
            <span class="muted">(수정됨)</span>
        <?php endif; ?>
        <?php if ($post['problem_id'] !== null): ?>
            · 관련 문제: <a href="<?= e(url('problem.php?id=' . $post['problem_id'])) ?>">문제 #<?= (int) $post['problem_id'] ?></a>
        <?php endif; ?>
    </p>

    <div class="post-body prose"><?= nl2br(e($post['body'])) ?></div>
</article>

<section class="comments" id="comments">
    <h2>댓글 <span class="muted">(<?= count($comments) ?>)</span></h2>

    <?php if (!$comments): ?>
        <p class="muted">아직 댓글이 없습니다.</p>
    <?php else: foreach ($comments as $c): ?>
        <div class="comment">
            <div class="comment-head">
                <strong><?= e($c['author_name']) ?></strong> <span class="muted">@<?= e($c['username']) ?></span>
                · <span class="muted"><?= e($c['created_at']) ?><?= $c['updated_at'] !== null ? ' (수정됨)' : '' ?></span>
                <?php if ($user && owns($c, $userId)): ?>
                    <span class="owner-actions">
                        <a href="<?= e(url('comment_edit.php?id=' . $c['id'])) ?>">수정</a>
                        <form method="post" action="<?= e(url('comment_delete.php')) ?>" class="inline"
                              onsubmit="return confirm('이 댓글을 삭제하시겠습니까?');">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="linklike" type="submit">삭제</button>
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
            <textarea name="body" rows="3" placeholder="댓글을 작성해 주세요…" required></textarea>
            <button class="btn btn-primary" type="submit">댓글 등록</button>
        </form>
    <?php else: ?>
        <p class="muted">댓글을 남기려면 <a href="<?= e(url('login.php')) ?>">로그인</a>하세요.</p>
    <?php endif; ?>
</section>
<?php
render_footer();
