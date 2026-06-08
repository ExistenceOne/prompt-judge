<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user   = current_user();
$userId = (int) $user['id'];

// Fetch first (so we can still show which were unread), then clear the badge.
$items = user_notifications($userId);
mark_notifications_read($userId);

render_header('알림');
?>
<h1>알림</h1>

<?php if (!$items): ?>
    <p class="muted">알림이 없습니다.</p>
<?php else: ?>
    <ul class="notif-list">
        <?php foreach ($items as $n): ?>
            <li class="notif <?= $n['is_read'] ? 'read' : 'unread' ?>">
                <?php if ($n['post_id'] !== null): ?>
                    <a href="<?= e(url('post.php?id=' . $n['post_id'] . '#comments')) ?>"><?= e($n['message']) ?></a>
                <?php else: ?>
                    <span><?= e($n['message']) ?></span>
                <?php endif; ?>
                <span class="notif-time muted"><?= e($n['created_at']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
render_footer();
