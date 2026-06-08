<?php
/**
 * Notifications: create alerts and read them back for the bell + list page.
 */

declare(strict_types=1);

/**
 * Record a notification for $userId. No-op if the actor is the recipient
 * (you don't get notified about your own actions).
 */
function notify(int $userId, ?int $actorId, string $type, ?int $postId, string $message): void
{
    if ($actorId !== null && $actorId === $userId) {
        return;
    }
    db_run(
        'INSERT INTO notifications (user_id, actor_id, type, post_id, message)
         VALUES (?, ?, ?, ?, ?)',
        [$userId, $actorId, $type, $postId, $message]
    );
}

/**
 * Number of unread notifications for a user (used for the header badge).
 */
function unread_notification_count(int $userId): int
{
    $row = db_run(
        'SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0',
        [$userId]
    )->fetch();
    return (int) ($row['c'] ?? 0);
}

/**
 * A user's notifications, newest first.
 *
 * @return array<int, array<string, mixed>>
 */
function user_notifications(int $userId, int $limit = 50): array
{
    return db_run(
        'SELECT n.*, u.username AS actor_username
         FROM notifications n
         LEFT JOIN users u ON u.id = n.actor_id
         WHERE n.user_id = ?
         ORDER BY n.id DESC
         LIMIT ' . (int) $limit,
        [$userId]
    )->fetchAll();
}

/**
 * Mark all of a user's notifications as read.
 */
function mark_notifications_read(int $userId): void
{
    db_run('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0', [$userId]);
}
