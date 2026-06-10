<?php
/**
 * Small shared helpers: output escaping, redirects, flash messages,
 * and result-label metadata.
 */

declare(strict_types=1);

/**
 * HTML-escape a value for safe output. Use everywhere user data is printed.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Build a URL relative to the app's public base (so it works whether the app
 * is served at "/" or under "/prompt-judge/public/").
 */
function url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    // When SCRIPT_NAME already points at a .php file, dirname() gives its folder.
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

/**
 * Redirect and stop. $path is relative to the public base.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Store a one-shot flash message shown on the next page load.
 */
function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

/**
 * Pull and clear all pending flash messages.
 *
 * @return array<int, array{message:string,type:string}>
 */
function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Read a trimmed string from POST.
 */
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

/**
 * Read an integer query-string parameter.
 */
function query_int(string $key, int $default = 0): int
{
    return isset($_GET[$key]) && is_numeric($_GET[$key]) ? (int) $_GET[$key] : $default;
}

/**
 * Metadata for a result label: human description and a CSS class suffix.
 *
 * @return array{label:string, text:string, kind:string}
 */
function result_meta(string $code): array
{
    static $map = [
        'AC'   => ['Accepted',                  'ok'],
        'WA'   => ['Wrong Answer',              'bad'],
        'TLE'  => ['Time Limit Exceeded',       'warn'],
        'MLE'  => ['Memory Limit Exceeded',     'warn'],
        'ITLE' => ['Input Token Limit Exceeded','warn'],
        'OTLE' => ['Output Token Limit Exceeded','warn'],
        'CE'   => ['Compilation Error',         'bad'],
        'RE'   => ['Runtime Error',             'bad'],
        'IE'   => ['Internal Error',            'bad'],
    ];
    [$text, $kind] = $map[$code] ?? [$code, 'bad'];
    return ['label' => $code, 'text' => $text, 'kind' => $kind];
}

/**
 * The AI model options offered on the submission page.
 *
 * Note: Opus 4.7/4.8 reject `temperature` (HTTP 400). Models flagged
 * `sampling => false` here will have that parameter omitted from the API call
 * (see src/claude.php). Models flagged `thinking => true` support extended
 * thinking via a `budget_tokens` parameter; when enabled, `temperature` is
 * omitted instead (the API rejects sampling params alongside thinking).
 *
 * @return array<string, array{label:string, sampling:bool, thinking:bool}>
 */
function model_options(): array
{
    return [
        'claude-sonnet-4-6'          => ['label' => 'Claude Sonnet 4.6 (default)', 'sampling' => true, 'thinking' => true],
        'claude-haiku-4-5'           => ['label' => 'Claude Haiku 4.5 (fast)',     'sampling' => true, 'thinking' => true],
        'claude-opus-4-8'            => ['label' => 'Claude Opus 4.8 (most capable)', 'sampling' => false, 'thinking' => true],
    ];
}
