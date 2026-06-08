<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$values = ['username' => '', 'name' => '', 'email' => '', 'affiliation' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['username']    = post('username');
    $values['name']        = post('name');
    $values['email']       = post('email');
    $values['affiliation'] = post('affiliation');
    $password              = post('password');

    [$ok, $err] = register_user(
        $values['username'],
        $password,
        $values['name'],
        $values['email'],
        $values['affiliation']
    );

    if ($ok) {
        flash('Account created. Please log in.', 'ok');
        redirect('login.php');
    }
    flash($err, 'bad');
}

render_header('Sign Up');
?>
<h1>Sign Up</h1>
<form method="post" class="card form">
    <label>Username (ID)
        <input type="text" name="username" value="<?= e($values['username']) ?>" required>
    </label>
    <label>Password
        <input type="password" name="password" required minlength="6">
    </label>
    <label>Name
        <input type="text" name="name" value="<?= e($values['name']) ?>" required>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($values['email']) ?>" required>
    </label>
    <label>Affiliation (University / Company)
        <input type="text" name="affiliation" value="<?= e($values['affiliation']) ?>">
    </label>
    <button class="btn btn-primary" type="submit">Create account</button>
    <p class="muted">Already have an account? <a href="<?= e(url('login.php')) ?>">Log in</a>.</p>
</form>
<?php
render_footer();
