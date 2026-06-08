<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post('username');
    $password = post('password');

    if (attempt_login($username, $password)) {
        flash('Welcome back!', 'ok');
        redirect('index.php');
    }
    flash('Invalid username or password.', 'bad');
}

render_header('Login');
?>
<h1>Login</h1>
<form method="post" class="card form">
    <label>Username (ID)
        <input type="text" name="username" value="<?= e($username) ?>" required autofocus>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button class="btn btn-primary" type="submit">Log in</button>
    <p class="muted">No account yet? <a href="<?= e(url('signup.php')) ?>">Sign up</a>.</p>
</form>
<?php
render_footer();
