<?php
/**
 * Bootstrap — included at the top of every page in public/.
 *
 * Loads config, starts the session, and pulls in the shared helpers so each
 * page only needs a single `require`.
 */

declare(strict_types=1);

// Surface errors during development. (Turn display off in production.)
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('APP_ROOT', dirname(__DIR__));            // project root
define('SRC_PATH', APP_ROOT . '/src');
define('CONFIG_PATH', APP_ROOT . '/config');
define('JUDGE0_DATA', APP_ROOT . '/Judge0');

$configFile = CONFIG_PATH . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration missing. Copy config/config.example.php to config/config.php and fill it in.');
}

/** @var array $CONFIG application configuration */
$CONFIG = require $configFile;
$GLOBALS['CONFIG'] = $CONFIG;

// Start the session for auth + flash messages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require SRC_PATH . '/helpers.php';
require SRC_PATH . '/db.php';
require SRC_PATH . '/auth.php';
require SRC_PATH . '/layout.php';
