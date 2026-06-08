<?php
/**
 * Shared page chrome: header (nav) and footer.
 */

declare(strict_types=1);

/**
 * Render the opening HTML, <head>, header nav, and flash messages.
 * Call render_footer() to close the document.
 */
function render_header(string $title = ''): void
{
    $cfg  = $GLOBALS['CONFIG']['site'];
    $user = current_user();
    $site = $cfg['name'];
    $full = $title !== '' ? "{$title} · {$site}" : $site;
    // Dark mode is remembered in a cookie and applied before paint by inline JS.
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($full) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script>
        // Apply saved theme immediately to avoid a flash of the wrong color.
        if (localStorage.getItem('pj-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body>
<header class="site-header">
    <nav class="nav-left">
        <a class="brand" href="<?= e(url('index.php')) ?>"><?= e($site) ?></a>
        <a href="<?= e(url('problems.php')) ?>">Problems</a>
        <a href="<?= e(url('history.php')) ?>">Judging History</a>
    </nav>
    <nav class="nav-right">
        <button type="button" id="theme-toggle" class="theme-toggle" title="Toggle dark mode">🌓</button>
        <?php if ($user): ?>
            <a href="<?= e(url('mypage.php')) ?>" class="username"><?= e($user['name']) ?></a>
            <a href="<?= e(url('logout.php')) ?>" class="btn btn-sm">Logout</a>
        <?php else: ?>
            <a href="<?= e(url('login.php')) ?>" class="btn btn-sm">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php foreach (take_flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
<?php
}

/**
 * Render the footer and close the document.
 */
function render_footer(): void
{
    $cfg = $GLOBALS['CONFIG']['site'];
    ?>
</main>
<footer class="site-footer">
    <span>&copy; 2026 <?= e($cfg['creator']) ?></span>
    <span>·</span>
    <span><?= e($cfg['name']) ?></span>
    <span>·</span>
    <a href="<?= e($cfg['github_url']) ?>" target="_blank" rel="noopener">GitHub</a>
</footer>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
<?php
}
