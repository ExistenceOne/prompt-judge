<?php
/**
 * Prompt Judge — 설정 템플릿.
 *
 * 이 파일을 (같은 디렉터리에) `config.php`로 복사한 뒤 실제 값을 채워 넣으세요.
 * `config.php`는 gitignore에 등록되어 있어 비밀 정보가 커밋되지 않습니다.
 *
 * 이 파일은 일부러 웹 루트(public/) 바깥에 위치합니다: PHP 실행이 중단되더라도
 * 웹 서버가 이 비밀 정보를 그대로 제공할 수 없도록 하기 위함입니다.
 */

return [
    // --- 데이터베이스 (PDO를 이용한 MySQL) ---
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'prompt_judge',
        'user'     => 'root',
        'pass'     => '',
        'charset'  => 'utf8mb4',
    ],

    // --- Claude API (Anthropic) ---
    'claude' => [
        // https://console.anthropic.com/ 에서 키를 발급받으세요
        'api_key'        => 'sk-ant-REPLACE_ME',
        'api_url'        => 'https://api.anthropic.com/v1/messages',
        'anthropic_version' => '2023-06-01',
        // 생성되는 코드 길이 제한. 코드 답변은 보통 짧습니다.
        'max_tokens'     => 8000,
        // 요청 타임아웃 (초 단위).
        'timeout'        => 120,
    ],

    // --- Judge0 API ---
    // 자체 호스팅 Judge0 CE:   base_url => 'http://localhost:2358', headers => []
    // RapidAPI 호스팅 Judge0:  base_url => 'https://judge0-ce.p.rapidapi.com',
    //                          headers => [
    //                              'X-RapidAPI-Key: YOUR_RAPIDAPI_KEY',
    //                              'X-RapidAPI-Host: judge0-ce.p.rapidapi.com',
    //                          ]
    'judge0' => [
        'base_url' => 'http://localhost:2358',
        'headers'  => [],
        // 결과를 동기적으로 기다림 (true) vs. 폴링 (wait=true만 지원합니다).
        'timeout'  => 60,
    ],

    // --- 사이트 ---
    'site' => [
        'name'        => 'Prompt Judge',
        'github_url'  => 'https://github.com/ExistenceOne/prompt-judge',
        'creator'     => 'ExistenceOne',
    ],
];
