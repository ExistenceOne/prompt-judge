<?php
/**
 * Judge0 client via raw cURL, plus helpers for the reference data files in
 * Judge0/languages.json and Judge0/statuses.json.
 */

declare(strict_types=1);

/**
 * Supported languages (from Judge0/languages.json), sorted by name.
 *
 * @return array<int, array{id:int, name:string}>
 */
function judge0_languages(): array
{
    static $langs = null;
    if ($langs !== null) {
        return $langs;
    }

    $json = @file_get_contents(JUDGE0_DATA . '/languages.json');
    $data = $json ? json_decode($json, true) : [];
    $langs = is_array($data) ? $data : [];

    usort($langs, static fn($a, $b) => strcasecmp($a['name'], $b['name']));
    return $langs;
}

/**
 * Look up a language name by Judge0 id.
 */
function judge0_language_name(int $id): string
{
    foreach (judge0_languages() as $lang) {
        if ((int) $lang['id'] === $id) {
            return $lang['name'];
        }
    }
    return 'Unknown';
}

/**
 * Map a Judge0 numeric status id to its description (from statuses.json).
 */
function judge0_status_text(int $id): string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $json = @file_get_contents(JUDGE0_DATA . '/statuses.json');
        foreach (($json ? json_decode($json, true) : []) ?: [] as $row) {
            $map[(int) $row['id']] = $row['description'];
        }
    }
    return $map[$id] ?? ('Status ' . $id);
}

/**
 * Submit one source file + stdin to Judge0 and wait for the result.
 *
 * @return array{ok:bool, result:array, error:string}
 */
function judge0_run(
    string $sourceCode,
    int $languageId,
    string $stdin,
    string $expectedOutput,
    int $cpuTimeLimitSec,
    int $memoryLimitKb
): array {
    $cfg = $GLOBALS['CONFIG']['judge0'];
    $url = rtrim($cfg['base_url'], '/') . '/submissions?base64_encoded=true&wait=true';

    $body = [
        'source_code'     => base64_encode($sourceCode),
        'language_id'     => $languageId,
        'stdin'           => base64_encode($stdin),
        'expected_output' => base64_encode($expectedOutput),
        'cpu_time_limit'  => $cpuTimeLimitSec,
        'memory_limit'    => $memoryLimitKb,
    ];

    $headers = array_merge(['content-type: application/json'], $cfg['headers'] ?? []);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => (int) $cfg['timeout'],
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'result' => [], 'error' => 'Judge0 request failed: ' . $err];
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'result' => [], 'error' => 'Judge0 returned invalid JSON.'];
    }
    if ($status < 200 || $status >= 300) {
        $msg = $data['error'] ?? $data['message'] ?? ('HTTP ' . $status);
        return ['ok' => false, 'result' => [], 'error' => 'Judge0 error: ' . $msg];
    }

    // Decode base64 output fields for convenience.
    foreach (['stdout', 'stderr', 'compile_output', 'message'] as $field) {
        if (!empty($data[$field])) {
            $data[$field] = base64_decode($data[$field], true) ?: $data[$field];
        }
    }

    return ['ok' => true, 'result' => $data, 'error' => ''];
}
