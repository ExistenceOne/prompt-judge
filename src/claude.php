<?php
/**
 * Claude API client (Anthropic Messages API) via raw cURL.
 *
 * The project is vanilla PHP with no Composer, so we call the HTTP API directly
 * rather than using the official SDK. Contract:
 *   POST https://api.anthropic.com/v1/messages
 *   headers: x-api-key, anthropic-version: 2023-06-01, content-type: application/json
 *   body:    { model, max_tokens, system, messages, [temperature], [top_p] }
 *   reply:   { content: [{type:"text", text}], usage: {input_tokens, output_tokens} }
 */

declare(strict_types=1);

/**
 * Generate source code from a user's prompt.
 *
 * @return array{ok:bool, code:string, input_tokens:int, output_tokens:int, error:string}
 */
function claude_generate_code(
    string $prompt,
    string $languageName,
    string $model,
    ?float $temperature,
    ?float $topP
): array {
    $cfg = $GLOBALS['CONFIG']['claude'];

    $system =
        "You are a code-generation engine for an online judge. " .
        "Write a COMPLETE, self-contained program in {$languageName} that solves the user's task. " .
        "The program must read from standard input and write the answer to standard output. " .
        "Output ONLY the raw source code — no explanations, no markdown, no code fences.";

    $body = [
        'model'      => $model,
        'max_tokens' => (int) $cfg['max_tokens'],
        'system'     => $system,
        'messages'   => [
            ['role' => 'user', 'content' => $prompt],
        ],
    ];

    // Only newer Opus models reject sampling params; include them when supported.
    $models = model_options();
    $supportsSampling = $models[$model]['sampling'] ?? false;
    if ($supportsSampling) {
        if ($temperature !== null) {
            $body['temperature'] = $temperature;
        }
        if ($topP !== null) {
            $body['top_p'] = $topP;
        }
    }

    $response = claude_http_post($cfg, $body);
    if (!$response['ok']) {
        return [
            'ok' => false, 'code' => '', 'input_tokens' => 0,
            'output_tokens' => 0, 'error' => $response['error'],
        ];
    }

    $data = $response['data'];
    $text = '';
    foreach ($data['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $text .= $block['text'];
        }
    }

    return [
        'ok'            => true,
        'code'          => claude_strip_fences($text),
        'input_tokens'  => (int) ($data['usage']['input_tokens'] ?? 0),
        'output_tokens' => (int) ($data['usage']['output_tokens'] ?? 0),
        'error'         => '',
    ];
}

/**
 * POST JSON to the Anthropic API.
 *
 * @param array<string,mixed> $cfg
 * @param array<string,mixed> $body
 * @return array{ok:bool, data:array, error:string}
 */
function claude_http_post(array $cfg, array $body): array
{
    $ch = curl_init($cfg['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => [
            'content-type: application/json',
            'x-api-key: ' . $cfg['api_key'],
            'anthropic-version: ' . $cfg['anthropic_version'],
        ],
        CURLOPT_TIMEOUT        => (int) $cfg['timeout'],
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'data' => [], 'error' => 'Claude request failed: ' . $err];
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'data' => [], 'error' => 'Claude returned invalid JSON.'];
    }

    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $status);
        return ['ok' => false, 'data' => [], 'error' => 'Claude error: ' . $msg];
    }

    return ['ok' => true, 'data' => $data, 'error' => ''];
}

/**
 * Strip ```lang ... ``` markdown fences if the model added them anyway.
 */
function claude_strip_fences(string $text): string
{
    $text = trim($text);
    if (preg_match('/^```[a-zA-Z0-9+#-]*\s*\n(.*?)\n```$/s', $text, $m)) {
        return trim($m[1]);
    }
    // Leading fence without a clean closing fence.
    $text = preg_replace('/^```[a-zA-Z0-9+#-]*\s*\n/', '', $text);
    $text = preg_replace('/\n```\s*$/', '', $text);
    return trim($text);
}
