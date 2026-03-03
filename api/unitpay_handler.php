<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
$pdo = require __DIR__ . '/db.php';

$config = require __DIR__ . '/config.php';
$unitpay = $config['unitpay'] ?? [];
$SECRET_KEY = (string)($unitpay['secret_key'] ?? '');
$RATE = (float)($unitpay['rate'] ?? 1.0);

if ($SECRET_KEY === '') {
    // Специальный формат ответа UnitPay
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'UnitPay secret_key is not set']], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = (string)($_GET['method'] ?? '');
$params = (array)($_GET['params'] ?? []);

function unitpay_calc_signature(string $method, array $params, string $secret): string {
    unset($params['signature']);
    ksort($params);

    $s = $method;
    foreach ($params as $v) {
        $s .= '{up}' . (string)$v;
    }
    $s .= '{up}' . $secret;

    return hash('sha256', $s);
}

function unitpay_ok(string $message): void {
    echo json_encode(['result' => ['message' => $message]], JSON_UNESCAPED_UNICODE);
    exit;
}

function unitpay_error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['error' => ['message' => $message]], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === '') {
    unitpay_error('method is required', 400);
}

$theirSig = (string)($params['signature'] ?? '');
$mySig = unitpay_calc_signature($method, $params, $SECRET_KEY);

if (!$theirSig || !hash_equals($mySig, $theirSig)) {
    unitpay_error('wrong signature', 403);
}

$orderId = (string)($params['account'] ?? '');
$orderSumStr = number_format((float)($params['orderSum'] ?? 0), 2, '.', '');
$unitpayId = (string)($params['unitpayId'] ?? '');

if ($orderId === '' || $orderSumStr === '0.00') {
    unitpay_error('bad params', 400);
}

$stmt = $pdo->prepare('SELECT * FROM donate_orders WHERE order_id = ? LIMIT 1');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    unitpay_error('order not found', 404);
}

// Проверяем сумму
$dbSumStr = number_format((float)$order['amount_rub'], 2, '.', '');
if ($dbSumStr !== $orderSumStr) {
    unitpay_error('wrong amount', 400);
}

if ($method === 'check') {
    if ($order['status'] === 'paid') {
        unitpay_ok('Already paid');
    }
    unitpay_ok('Check Success');
}

if ($method === 'pay') {
    if ($unitpayId === '') {
        unitpay_error('unitpayId is required', 400);
    }

    if ($order['status'] === 'paid') {
        unitpay_ok('Pay Success (duplicate)');
    }

    $pdo->beginTransaction();
    try {
        // 1) Помечаем заказ как paid (только если был created)
        $u1 = $pdo->prepare('
            UPDATE donate_orders
            SET status="paid", unitpay_id=?, paid_at=NOW()
            WHERE order_id=? AND status="created"
        ');
        $u1->execute([$unitpayId, $orderId]);

        // Если уже успели оплатить параллельно — просто ок
        if ($u1->rowCount() === 0) {
            $pdo->commit();
            unitpay_ok('Pay Success (duplicate)');
        }

        // 2) Начисляем донат-единицы игроку (в вашу таблицу accounts)
        $coins = (int)$order['coins'];
        $accountId = (int)$order['account_id'];

        // По умолчанию увеличиваем donate_current и donate_total
        // Если вам нужно начислять игровую валюту — замените запрос ниже, например на:
        // UPDATE accounts SET money = CAST(money AS UNSIGNED) + ? WHERE id = ?
        $u2 = $pdo->prepare('
            UPDATE accounts
            SET donate_current = donate_current + ?, donate_total = donate_total + ?
            WHERE id = ?
        ');
        $u2->execute([$coins, $coins, $accountId]);

        $pdo->commit();
        unitpay_ok('Pay Success');
    } catch (Throwable $e) {
        $pdo->rollBack();
        unitpay_error('db error', 500);
    }
}

if ($method === 'error') {
    // Можно пометить как canceled
    $u = $pdo->prepare('UPDATE donate_orders SET status="canceled" WHERE order_id=? AND status="created"');
    $u->execute([$orderId]);

    unitpay_ok('Error logged');
}

unitpay_error('method is not supported', 400);
