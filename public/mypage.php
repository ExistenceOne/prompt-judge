<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
$user = current_user();

// Basic statistics.
$stats = db_run(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN result = 'AC' THEN 1 ELSE 0 END) AS accepted
     FROM submissions WHERE user_id = ?",
    [$user['id']]
)->fetch();

// Distinct solved problems.
$solved = db_run(
    "SELECT DISTINCT p.id, p.title
     FROM submissions s JOIN problems p ON p.id = s.problem_id
     WHERE s.user_id = ? AND s.result = 'AC'
     ORDER BY p.id",
    [$user['id']]
)->fetchAll();

render_header('My Page');
?>
<h1>My Page</h1>

<section class="card">
    <h2><?= e($user['name']) ?> <span class="muted">@<?= e($user['username']) ?></span></h2>
    <p class="muted"><?= e($user['email']) ?><?= $user['affiliation'] ? ' · ' . e($user['affiliation']) : '' ?></p>
    <div class="stats">
        <div><span class="num"><?= (int) $stats['total'] ?></span><span class="muted">Submissions</span></div>
        <div><span class="num"><?= (int) $stats['accepted'] ?></span><span class="muted">Accepted</span></div>
        <div><span class="num"><?= count($solved) ?></span><span class="muted">Problems solved</span></div>
    </div>
</section>

<h2>Solved Problems</h2>
<?php if (!$solved): ?>
    <p class="muted">No problems solved yet. <a href="<?= e(url('problems.php')) ?>">Find one to try!</a></p>
<?php else: ?>
    <ul class="solved-list">
        <?php foreach ($solved as $p): ?>
            <li><a href="<?= e(url('problem.php?id=' . $p['id'])) ?>">#<?= (int) $p['id'] ?> · <?= e($p['title']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
render_footer();
