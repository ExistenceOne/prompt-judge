<?php
/**
 * Submission pipeline: Prompt -> Claude -> token gate -> Judge0 -> result label.
 *
 * Mirrors the "How It Works (Submission Pipeline)" section of README.md.
 */

declare(strict_types=1);

require_once SRC_PATH . '/claude.php';
require_once SRC_PATH . '/judge0.php';

/**
 * Run a full submission and persist it to the Judging History.
 *
 * @param array<string,mixed> $problem  A row from the problems table.
 * @return int  The new submissions.id.
 */
function run_submission(
    int $userId,
    array $problem,
    int $languageId,
    string $model,
    ?float $temperature,
    ?int $thinkingBudget,
    string $prompt
): int {
    $languageName = judge0_language_name($languageId);

    // Defaults for the record we will persist.
    $record = [
        'user_id'          => $userId,
        'problem_id'       => (int) $problem['id'],
        'language_id'      => $languageId,
        'language_name'    => $languageName,
        'model'            => $model,
        'temperature'      => $temperature,
        'thinking_budget'  => $thinkingBudget,
        'prompt'           => $prompt,
        'generated_code'   => null,
        'input_tokens'     => null,
        'output_tokens'    => null,
        'code_size'        => null,
        'result'           => 'IE',
        'exec_time_ms'     => null,
        'memory_kb'        => null,
        'judge0_status_id' => null,
        'compile_output'   => null,
        'stderr'           => null,
    ];

    // 1) Ask Claude to generate the source code.
    $gen = claude_generate_code($prompt, $languageName, $model, $temperature, $thinkingBudget);
    if (!$gen['ok']) {
        $record['stderr'] = $gen['error'];
        return persist_submission($record);
    }

    $record['generated_code'] = $gen['code'];
    $record['input_tokens']   = $gen['input_tokens'];
    $record['output_tokens']  = $gen['output_tokens'];
    $record['code_size']      = strlen($gen['code']);

    // 2) Token-limit gate — checked BEFORE Judge0 is contacted.
    if ($gen['input_tokens'] > (int) $problem['input_token_limit']) {
        $record['result'] = 'ITLE';
        return persist_submission($record);
    }
    if ($gen['output_tokens'] > (int) $problem['output_token_limit']) {
        $record['result'] = 'OTLE';
        return persist_submission($record);
    }

    // 3) Compile & run against every hidden test case via Judge0.
    $testcases = db_run(
        'SELECT stdin, expected_output FROM testcases WHERE problem_id = ? ORDER BY id',
        [$problem['id']]
    )->fetchAll();

    if (!$testcases) {
        $record['result'] = 'IE';
        $record['stderr'] = 'No test cases configured for this problem.';
        return persist_submission($record);
    }

    $cpuLimitSec  = max(1, (int) ceil(((int) $problem['time_limit_ms']) / 1000));
    $memoryLimit  = (int) $problem['memory_limit_kb'];
    $maxTimeMs    = 0;
    $maxMemoryKb  = 0;
    $finalResult  = 'AC';

    foreach ($testcases as $tc) {
        $run = judge0_run(
            $gen['code'],
            $languageId,
            $tc['stdin'],
            $tc['expected_output'],
            $cpuLimitSec,
            $memoryLimit
        );

        if (!$run['ok']) {
            $finalResult = 'IE';
            $record['stderr'] = $run['error'];
            break;
        }

        $res      = $run['result'];
        $statusId = (int) ($res['status']['id'] ?? 0);
        $record['judge0_status_id'] = $statusId;

        $timeMs   = (int) round(((float) ($res['time'] ?? 0)) * 1000);
        $memoryKb = (int) ($res['memory'] ?? 0);
        $maxTimeMs   = max($maxTimeMs, $timeMs);
        $maxMemoryKb = max($maxMemoryKb, $memoryKb);

        // 4) Translate Judge0 status + our limits into a result label.
        $caseResult = label_from_status($statusId, $memoryKb, $memoryLimit);

        if ($caseResult !== 'AC') {
            $finalResult = $caseResult;
            if ($statusId === 6) {
                $record['compile_output'] = $res['compile_output'] ?? null;
            } elseif (!empty($res['stderr'])) {
                $record['stderr'] = $res['stderr'];
            }
            break; // first failing case decides the verdict
        }
    }

    $record['result']       = $finalResult;
    $record['exec_time_ms'] = $maxTimeMs;
    $record['memory_kb']    = $maxMemoryKb;

    return persist_submission($record);
}

/**
 * Map a Judge0 status id (+ memory check) to a Prompt Judge result label.
 */
function label_from_status(int $statusId, int $memoryKb, int $memoryLimitKb): string
{
    return match (true) {
        $statusId === 3  => ($memoryKb > $memoryLimitKb ? 'MLE' : 'AC'),
        $statusId === 4  => 'WA',
        $statusId === 5  => 'TLE',
        $statusId === 6  => 'CE',
        $statusId >= 7 && $statusId <= 12 => 'RE',
        default          => 'IE',
    };
}

/**
 * Insert a submission record and return its id.
 *
 * @param array<string,mixed> $r
 */
function persist_submission(array $r): int
{
    db_run(
        'INSERT INTO submissions
            (user_id, problem_id, language_id, language_name, model, temperature, thinking_budget,
             prompt, generated_code, input_tokens, output_tokens, code_size, result,
             exec_time_ms, memory_kb, judge0_status_id, compile_output, stderr)
         VALUES
            (:user_id, :problem_id, :language_id, :language_name, :model, :temperature, :thinking_budget,
             :prompt, :generated_code, :input_tokens, :output_tokens, :code_size, :result,
             :exec_time_ms, :memory_kb, :judge0_status_id, :compile_output, :stderr)',
        $r
    );
    return (int) db()->lastInsertId();
}
