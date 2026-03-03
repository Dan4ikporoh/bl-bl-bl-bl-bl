<?php
declare(strict_types=1);

/**
 * Скопируйте этот файл как config.php и заполните значения.
 * ВАЖНО: config.php не коммитится (он в .gitignore).
 */
return [
    'db' => [
        // Если сайт и MySQL на одном сервере — обычно 127.0.0.1
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'gs103548',
        'user' => 'gs103548',
        'pass' => 'CHANGE_ME_DB_PASSWORD',
        'charset' => 'utf8mb4',
    ],

    'session' => [
        'name' => 'fride_session',
        // true только если сайт на HTTPS. Если HTTP — поставьте false.
        'cookie_secure' => true,
        'cookie_samesite' => 'Lax',
    ],

    'unitpay' => [
        // Ключи берутся в личном кабинете UnitPay
        'public_key' => 'CHANGE_ME_UNITPAY_PUBLIC_KEY',
        'secret_key' => 'CHANGE_ME_UNITPAY_SECRET_KEY',
        'currency'   => 'RUB',

        // Минимальная сумма доната в рублях
        'min_amount' => 10.0,

        // Курс: сколько донат-единиц выдавать за 1 рубль
        // Например 1 рубль = 1 донат
        'rate' => 1.0,
    ],
];
