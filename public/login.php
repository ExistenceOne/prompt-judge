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
        flash('로그인되었습니다!', 'ok');
        redirect('index.php');
    }
    flash('아이디 또는 비밀번호가 잘못되었습니다.', 'bad');
}

render_header('로그인');
?>
<h1>로그인</h1>
<form method="post" class="card form">
    <label>계정 아이디 (ID)
        <input type="text" name="username" value="<?= e($username) ?>" required autofocus>
    </label>
    <label>비밀번호
        <input type="password" name="password" required>
    </label>
    <button class="btn btn-primary" type="submit">로그인</button>
    <p class="muted">아직 계정이 없으신가요? <a href="<?= e(url('signup.php')) ?>">회원가입</a>.</p>
</form>
<?php
render_footer();
