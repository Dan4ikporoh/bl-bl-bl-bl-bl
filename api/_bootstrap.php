<?php
declare(strict_types=1);

$cfgPath = __DIR__ . '/config.php';
if (!file_exists($cfgPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'CONFIG_NOT_FOUND: создайте api/config.php (см. api/config.example.php)',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = require $cfgPath;

// Сессии
session_name($config['session']['name'] ?? 'fride_session');
session_set_cookie_params([
    'httponly' => true,
    'secure'   => (bool)($config['session']['cookie_secure'] ?? false),
    'samesite' => (string)($config['session']['cookie_samesite'] ?? 'Lax'),
    'path'     => '/',
]);
session_start();

// JSON по умолчанию
header('Content-Type: application/json; charset=utf-8');

function json_out(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
