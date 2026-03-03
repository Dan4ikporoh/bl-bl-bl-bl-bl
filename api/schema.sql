-- Таблица заказов доната (для UnitPay handler)
CREATE TABLE IF NOT EXISTS `donate_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(64) NOT NULL,
  `account_id` int(11) NOT NULL,
  `account_name` varchar(21) NOT NULL,
  `amount_rub` decimal(10,2) NOT NULL,
  `coins` int(11) NOT NULL,
  `status` enum('created','paid','canceled') NOT NULL DEFAULT 'created',
  `unitpay_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `paid_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  UNIQUE KEY `unitpay_id` (`unitpay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
