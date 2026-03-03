<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
$pdo = require __DIR__ . '/db.php';

$accountId = (int)($_SESSION['account_id'] ?? 0);
if ($accountId <= 0) {
    json_out(['ok' => false, 'error' => 'Не авторизован'], 401);
}

$stmt = $pdo->prepare('
    SELECT id, name, email, level, exp, skin, money, bank,
           donate_current, donate_total, golds, admin, premium, premium_time, last_login, last_ip
    FROM accounts
    WHERE id = ?
    LIMIT 1
');
$stmt->execute([$accountId]);
$row = $stmt->fetch();

if (!$row) {
    json_out(['ok' => false, 'error' => 'Аккаунт не найден'], 404);
}

$user = [
    'id'            => (int)$row['id'],
    'name'          => (string)$row['name'],
    'email'         => (string)$row['email'],
    'level'         => (int)$row['level'],
    'exp'           => (int)$row['exp'],
    'skin'          => (int)$row['skin'],
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
