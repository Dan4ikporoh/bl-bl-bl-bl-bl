<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
$pdo = require __DIR__ . '/db.php';

$config = require __DIR__ . '/config.php';
$unitpay = $config['unitpay'] ?? [];

$PUBLIC_KEY = (string)($unitpay['public_key'] ?? '');
$SECRET_KEY = (string)($unitpay['secret_key'] ?? '');
$CURRENCY   = (string)($unitpay['currency'] ?? 'RUB');
$MIN_AMOUNT = (float)($unitpay['min_amount'] ?? 10.0);
$RATE       = (float)($unitpay['rate'] ?? 1.0);

if ($PUBLIC_KEY === '' || $SECRET_KEY === '') {
    json_out(['ok' => false, 'error' => 'UnitPay не настроен: заполните public_key/secret_key в api/config.php'], 500);
}

$body = json_decode((string)file_get_contents('php://input'), true) ?? [];
$name = trim((string)($body['username'] ?? ''));
$amount = (float)($body['amount'] ?? 0);

if ($name === '' || $amount <= 0) {
    json_out(['ok' => false, 'error' => 'Укажите ник и сумму'], 400);
}
if ($amount < $MIN_AMOUNT) {
    json_out(['ok' => false, 'error' => 'Минимальная сумма доната: ' . $MIN_AMOUNT], 400);
}

// Проверяем, что аккаунт существует
$stmt = $pdo->prepare('SELECT id, name FROM accounts WHERE name = ? LIMIT 1');
$stmt->execute([$name]);
$acc = $stmt->fetch();
if (!$acc) {
    json_out(['ok' => false, 'error' => 'Аккаунт не найден. Проверьте ник (name) как в игре.'], 404);
}

$accountId = (int)$acc['id'];
$accountName = (string)$acc['name'];

// Считаем донат-единицы
$coins = (int)round($amount * $RATE);

// Уникальный order_id
$orderId = bin2hex(random_bytes(8)) . '-' . time();

$desc = 'FRIDE RP донат: ' . $accountName;

// UnitPay очень чувствителен к строке суммы в подписи — используем формат 2 знака после запятой
$sumStr = number_format($amount, 2, '.', '');

// Сохраняем заказ
$ins = $pdo->prepare('
    INSERT INTO donate_orders(order_id, account_id, account_name, amount_rub, coins, status)
    VALUES(?,?,?,?,?,"created")
');
$ins->execute([$orderId, $accountId, $accountName, $sumStr, $coins]);

// Подпись для формы: sha256(account + "{up}" + currency + "{up}" + desc + "{up}" + sum + "{up}" + secretKey)
$hashStr = $orderId . '{up}' . $CURRENCY . '{up}' . $desc . '{up}' . $sumStr . '{up}' . $SECRET_KEY;
$signature = hash('sha256', $hashStr);

// URL платежной формы
$redirectUrl = 'https://unitpay.ru/pay/' . rawurlencode($PUBLIC_KEY)
    . '?sum=' . rawurlencode($sumStr)
    . '&account=' . rawurlencode($orderId)
    . '&desc=' . rawurlencode($desc)
    . '&currency=' . rawurlencode($CURRENCY)
    . '&signature=' . rawurlencode($signature);

json_out([
    'ok' => true,
    'redirectUrl' => $redirectUrl,
]);
