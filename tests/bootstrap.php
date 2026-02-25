<?php

/**
 * Test bootstrap — forces test environment variables into $_ENV and $_SERVER
 * BEFORE autoload and Laravel bootstrap. This ensures Docker container env vars
 * (DB_DATABASE=lms, QUEUE_CONNECTION=redis, etc.) never bleed into tests.
 */
$viewCacheDir = sys_get_temp_dir() . '/lms_test_views';
if (! is_dir($viewCacheDir)) {
    mkdir($viewCacheDir, 0755, true);
}

$testEnv = [
    'APP_ENV'              => 'testing',
    'DB_CONNECTION'        => 'sqlite',
    'DB_DATABASE'          => ':memory:',
    'DB_HOST'              => '',
    'DB_PORT'              => '',
    'DB_USERNAME'          => '',
    'DB_PASSWORD'          => '',
    'QUEUE_CONNECTION'     => 'sync',
    'CACHE_STORE'          => 'array',
    'SESSION_DRIVER'       => 'array',
    'MAIL_MAILER'          => 'array',
    'BROADCAST_CONNECTION' => 'null',
    'LOG_CHANNEL'          => 'null',
    'VIEW_COMPILED_PATH'   => $viewCacheDir,
];

foreach ($testEnv as $key => $value) {
    $_ENV[$key]    = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}

require_once __DIR__ . '/../vendor/autoload.php';
