<?php
declare(strict_types=1);

$cfgPath = __DIR__ . '/config.php';
$config = require $cfgPath;
$db = $config['db'];

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $db['host'],
    (int)$db['port'],
    $db['name'],
    $db['charset'] ?? 'utf8mb4'
);

$pdo = new PDO($dsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

return $pdo;
