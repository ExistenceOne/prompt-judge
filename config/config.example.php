<?php
/**
 * Prompt Judge — configuration template.
 *
 * Copy this file to `config.php` (same directory) and fill in real values.
 * `config.php` is gitignored so your secrets never get committed.
 *
 * This file lives OUTSIDE the web root (public/) on purpose: even if PHP
 * stops executing, the web server cannot serve these secrets.
 */

return [
    // --- Database (MySQL via PDO) ---
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
        // Get a key from https://console.anthropic.com/
        'api_key'        => 'sk-ant-REPLACE_ME',
        'api_url'        => 'https://api.anthropic.com/v1/messages',
        'anthropic_version' => '2023-06-01',
        // Cap on generated code length. Code answers are usually small.
        'max_tokens'     => 8000,
        // Request timeout in seconds.
        'timeout'        => 120,
    ],

    // --- Judge0 API ---
    // Self-hosted Judge0 CE:   base_url => 'http://localhost:2358', headers => []
    // RapidAPI-hosted Judge0:  base_url => 'https://judge0-ce.p.rapidapi.com',
    //                          headers => [
    //                              'X-RapidAPI-Key: YOUR_RAPIDAPI_KEY',
    //                              'X-RapidAPI-Host: judge0-ce.p.rapidapi.com',
    //                          ]
    'judge0' => [
        'base_url' => 'http://localhost:2358',
        'headers'  => [],
        // Wait synchronously for results (true) vs. poll (we only support wait=true).
        'timeout'  => 60,
    ],

    // --- Site ---
    'site' => [
        'name'        => 'Prompt Judge',
        'github_url'  => 'https://github.com/ExistenceOne/prompt-judge',
        'creator'     => 'ExistenceOne',
    ],
];
