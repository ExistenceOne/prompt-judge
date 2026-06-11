<?php
require __DIR__ . '/../src/bootstrap.php';

render_header('홈');
?>
<section class="hero">
    <img class="hero-logo" src="<?= e(url('assets/logo.png')) ?>" alt="Prompt Judge 로고">
    <h1>이제는 AI가 코딩하는 시대</h1>
    <p class="lead">
        프롬프트 저지는 <strong>AI 프롬프트 기반 온라인 저지</strong>입니다.<br>
        직접 코딩하는 대신 프롬프트를 작성하세요.<br>
    </p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(url('problems.php')) ?>">문제 둘러보기</a>
        <?php if (!current_user()): ?>
            <a class="btn" href="<?= e(url('signup.php')) ?>">회원가입</a>
        <?php endif; ?>
    </div>
</section>

<section class="how">
    <h2>작동 방식</h2>
    <ol class="steps">
        <li><strong>문제를 선택하고</strong> 제한 사항(시간, 메모리, 토큰 제한)을 읽어보세요.</li>
        <li><strong>모델과 대상 언어를 선택한 후</strong>, 솔루션을 설명하는 프롬프트를 작성하세요.</li>
        <li><strong>Claude가 코드를 생성하고</strong> 입출력 토큰 사용량을 보고합니다.</li>
        <li><strong>Judge0가 코드 실행 후</strong> 판정(AC, WA, TLE, MLE)을 반환합니다. 토큰 제한 초과 시 ITLE/OTLE 결과를 받습니다.</li>
        <li><strong>채점 기록에서</strong> 프롬프트, 생성된 코드 및 지표를 확인하세요.</li>
    </ol>
</section>
<?php
render_footer();
