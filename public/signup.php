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
        flash('계정이 생성되었습니다. 로그인해 주세요.', 'ok');
        redirect('login.php');
    }
    flash($err, 'bad');
}

render_header('회원가입');
?>
<h1>회원가입</h1>
<form method="post" class="card form">
    <label>아이디
        <input type="text" name="username" value="<?= e($values['username']) ?>" required>
    </label>
    <label>비밀번호
        <input type="password" name="password" required minlength="6">
    </label>
    <label>이름
        <input type="text" name="name" value="<?= e($values['name']) ?>" required>
    </label>
    <label>이메일
        <input type="email" name="email" value="<?= e($values['email']) ?>" required>
    </label>
    <label>소속 (학교 / 직장)
        <input type="text" name="affiliation" value="<?= e($values['affiliation']) ?>">
    </label>
    <button class="btn btn-primary" type="submit">계정 생성</button>
    <p class="muted">이미 계정이 있으신가요? <a href="<?= e(url('login.php')) ?>">로그인</a>.</p>
</form>
<?php
render_footer();
