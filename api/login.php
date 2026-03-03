<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
$pdo = require __DIR__ . '/db.php';

$body = json_decode((string)file_get_contents('php://input'), true) ?? [];
$name = trim((string)($body['username'] ?? ''));
$pass = (string)($body['password'] ?? '');

if ($name === '' || $pass === '') {
    json_out(['ok' => false, 'error' => 'Введите логин и пароль'], 400);
}

// Ваша таблица из дампа: accounts(name, password, ...)
$stmt = $pdo->prepare('
    SELECT id, name, password, email, level, exp, skin, money, bank,
           donate_current, donate_total, golds, admin, premium, premium_time, last_login, last_ip
    FROM accounts
    WHERE name = ?
    LIMIT 1
');
$stmt->execute([$name]);
$row = $stmt->fetch();

if (!$row) {
    json_out(['ok' => false, 'error' => 'Неверный логин или пароль'], 401);
}

$stored = (string)$row['password'];
$ok = false;

// Если вдруг у вас когда-то будут хэши bcrypt/argon — тоже поддержим
if (preg_match('/^\$2[aby]\$/', $stored) || strpos($stored, '$argon2') === 0) {
    $ok = password_verify($pass, $stored);
} else {
    // В дампе пароли лежат как обычная строка
    $ok = hash_equals($stored, $pass);
}

if (!$ok) {
    json_out(['ok' => false, 'error' => 'Неверный логин или пароль'], 401);
}

$_SESSION['account_id'] = (int)$row['id'];

$user = [
    'id'            => (int)$row['id'],
    'name'          => (string)$row['name'],
    'email'         => (string)$row['email'],
    'level'         => (int)$row['level'],
    'exp'           => (int)$row['exp'],
    'skin'          => (int)$row['skin'],
    // money/bank в БД varchar — отдаём строкой (чтобы не потерять большие значения)
    'money'         => (string)$row['money'],
    'bank'          => (string)$row['bank'],
    'donate_current'=> (int)$row['donate_current'],
    'donate_total'  => (int)$row['donate_total'],
    'golds'         => (int)$row['golds'],
    'admin'         => (int)$row['admin'],
    'premium'       => (int)$row['premium'],
    'premium_time'  => (int)$row['premium_time'],
    'last_login'    => (int)$row['last_login'],
    'last_ip'       => (string)$row['last_ip'],
];

json_out(['ok' => true, 'user' => $user]);
