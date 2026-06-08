<?php
require __DIR__ . '/../src/bootstrap.php';

$category = trim((string) ($_GET['category'] ?? '')); // '' = all
$q        = trim((string) ($_GET['q'] ?? ''));         // title or author

if ($category !== '' && !is_valid_category($category)) {
    $category = '';
}

// Posts with author name and comment count. Search by title or author.
$sql = "
    SELECT p.id, p.category, p.problem_id, p.title, p.created_at,
           u.username, u.name AS author_name,
           COUNT(c.id) AS comment_count
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN comments c ON c.post_id = p.id
    WHERE 1 = 1
";
$params = [];
if ($category !== '') {
    $sql .= ' AND p.category = ?';
    $params[] = $category;
}
if ($q !== '') {
    $sql .= ' AND (p.title LIKE ? OR u.username LIKE ? OR u.name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' GROUP BY p.id, p.category, p.problem_id, p.title, p.created_at, u.username, u.name
          ORDER BY p.id DESC LIMIT 200';

$posts = db_run($sql, $params)->fetchAll();

render_header('Community');
?>
<div class="page-head">
    <h1>Community</h1>
    <?php if (current_user()): ?>
        <a class="btn btn-primary" href="<?= e(url('post_form.php' . ($category !== '' ? '?category=' . $category : ''))) ?>">New Post</a>
    <?php endif; ?>
</div>

<nav class="tabs">
    <a class="<?= $category === '' ? 'active' : '' ?>" href="<?= e(url('board.php')) ?>">All</a>
    <?php foreach (board_categories() as $code => $label): ?>
        <a class="<?= $category === $code ? 'active' : '' ?>" href="<?= e(url('board.php?category=' . $code)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>

<form method="get" class="searchbar">
    <?php if ($category !== ''): ?>
        <input type="hidden" name="category" value="<?= e($category) ?>">
    <?php endif; ?>
    <input type="text" name="q" placeholder="Search by Title or Author" value="<?= e($q) ?>">
    <button class="btn" type="submit">Search</button>
</form>

<div class="table-scroll">
<table class="data">
    <thead>
        <tr>
            <th>ID</th><th>Category</th><th>Problem</th><th>Title</th>
            <th>Comments</th><th>Author</th><th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$posts): ?>
        <tr><td colspan="7" class="muted">No posts yet.</td></tr>
    <?php else: foreach ($posts as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><span class="tag tag-<?= e($p['category']) ?>"><?= e(board_category_label($p['category'])) ?></span></td>
            <td>
                <?php if ($p['problem_id'] !== null): ?>
                    <a href="<?= e(url('problem.php?id=' . $p['problem_id'])) ?>">#<?= (int) $p['problem_id'] ?></a>
                <?php else: ?>
                    <span class="muted">—</span>
                <?php endif; ?>
            </td>
            <td><a href="<?= e(url('post.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a></td>
            <td><?= (int) $p['comment_count'] ?></td>
            <td><?= e($p['author_name']) ?> <span class="muted">@<?= e($p['username']) ?></span></td>
            <td><?= e($p['created_at']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
<?php
render_footer();
