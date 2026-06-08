<?php
/**
 * Community boards: categories, post and comment lookups, ownership checks.
 *
 * Writes (create/update/delete) are done inline in the page handlers, mirroring
 * the style of auth.php / submit.php; this file holds the shared read helpers
 * and the small bits of logic reused across pages.
 */

declare(strict_types=1);

/**
 * Board category code => human label.
 *
 * @return array<string, string>
 */
function board_categories(): array
{
    return ['notice' => '공지사항', 'free' => '자유게시판', 'qna' => 'Q&A'];
}

/**
 * Human label for a category code (falls back to the code itself).
 */
function board_category_label(string $code): string
{
    return board_categories()[$code] ?? $code;
}

/**
 * Whether a category code is one we recognise.
 */
function is_valid_category(string $code): bool
{
    return array_key_exists($code, board_categories());
}

/**
 * Fetch a post with its author, or null.
 *
 * @return array<string, mixed>|null
 */
function find_post(int $id): ?array
{
    $row = db_run(
        'SELECT p.*, u.username, u.name AS author_name
         FROM posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.id = ?',
        [$id]
    )->fetch();
    return $row ?: null;
}

/**
 * Comments on a post (oldest first), each with author info.
 *
 * @return array<int, array<string, mixed>>
 */
function post_comments(int $postId): array
{
    return db_run(
        'SELECT c.*, u.username, u.name AS author_name
         FROM comments c
         JOIN users u ON u.id = c.user_id
         WHERE c.post_id = ?
         ORDER BY c.id ASC',
        [$postId]
    )->fetchAll();
}

/**
 * Fetch a single comment row, or null.
 *
 * @return array<string, mixed>|null
 */
function find_comment(int $id): ?array
{
    $row = db_run('SELECT * FROM comments WHERE id = ?', [$id])->fetch();
    return $row ?: null;
}

/**
 * Does $row (a post or comment) belong to $userId?
 *
 * @param array<string, mixed> $row
 */
function owns(array $row, int $userId): bool
{
    return (int) $row['user_id'] === $userId;
}
