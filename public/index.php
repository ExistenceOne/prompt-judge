<?php
require __DIR__ . '/../src/bootstrap.php';

render_header('Home');
?>
<section class="hero">
    <h1>Solve problems with <em>prompts</em>, not code.</h1>
    <p class="lead">
        Prompt Judge is an AI-driven online judge. Instead of writing a solution by hand,
        you write a <strong>prompt</strong>. Claude turns it into a program, and Judge0
        compiles and runs it against hidden test cases.
    </p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(url('problems.php')) ?>">Browse Problems</a>
        <?php if (!current_user()): ?>
            <a class="btn" href="<?= e(url('signup.php')) ?>">Create an account</a>
        <?php endif; ?>
    </div>
</section>

<section class="how">
    <h2>How it works</h2>
    <ol class="steps">
        <li><strong>Pick a problem</strong> and read its limits (time, memory, and token limits).</li>
        <li><strong>Choose a model and target language</strong>, then write a prompt describing the solution.</li>
        <li><strong>Claude generates the code</strong> and reports its input/output token usage.</li>
        <li><strong>Judge0 runs the code</strong> against hidden tests and returns a verdict
            (AC, WA, TLE, MLE) — or you get ITLE/OTLE if your prompt blew the token budget.</li>
        <li><strong>Review the result</strong> in your Judging History: prompt, generated code, and metrics.</li>
    </ol>
</section>
<?php
render_footer();
