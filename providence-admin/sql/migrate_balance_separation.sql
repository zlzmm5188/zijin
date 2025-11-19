-- ============================================
-- 余额分离迁移脚本
-- 将用户余额分为 CNY 和 USDT 两种独立余额
-- ============================================

-- 1. 检查并添加 users 表的余额字段（如果不存在）
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `balance_cny` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '人民币余额' AFTER `balance`,
ADD COLUMN IF NOT EXISTS `balance_usdt` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT 'USDT余额' AFTER `balance_cny`;

-- 2. 如果 users 表有旧的 balance 字段，迁移数据到 balance_cny
-- 注意：这里假设旧余额都是人民币，如果是混合的，需要手动处理
UPDATE `users`
SET `balance_cny` = COALESCE(`balance`, 0)
WHERE `balance_cny` = 0 AND `balance` > 0;

-- 3. 确保 wallets 表存在且结构正确
CREATE TABLE IF NOT EXISTS `wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `currency` varchar(10) NOT NULL DEFAULT 'CNY' COMMENT '币种：CNY/USDT',
  `balance` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '可用余额',
  `frozen` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '冻结余额',
  `ribao_balance` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '日利宝余额（仅CNY）',
  `ribao_total_profit` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '日利宝累计收益（仅CNY）',
  `ribao_yesterday_profit` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '日利宝昨日收益（仅CNY）',
  `points` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '积分（仅CNY）',
  `total_income` DECIMAL(20,8) DEFAULT 0.00000000 COMMENT '累计收益',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_currency` (`user_id`, `currency`),
  KEY `user_id` (`user_id`),
  KEY `currency` (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户钱包表（按币种分离）';

-- 4. 为所有用户创建 CNY 钱包（如果不存在）
INSERT INTO `wallets` (`user_id`, `currency`, `balance`, `frozen`, `ribao_balance`, `points`, `total_income`)
SELECT `id`, 'CNY', COALESCE(`balance_cny`, 0), 0, 0, 0, 0
FROM `users`
WHERE NOT EXISTS (
    SELECT 1 FROM `wallets` WHERE `wallets`.`user_id` = `users`.`id` AND `wallets`.`currency` = 'CNY'
);

-- 5. 为所有用户创建 USDT 钱包（如果不存在）
INSERT INTO `wallets` (`user_id`, `currency`, `balance`, `frozen`, `total_income`)
SELECT `id`, 'USDT', COALESCE(`balance_usdt`, 0), 0, 0
FROM `users`
WHERE NOT EXISTS (
    SELECT 1 FROM `wallets` WHERE `wallets`.`user_id` = `users`.`id` AND `wallets`.`currency` = 'USDT'
);

-- 6. 同步 users 表的余额到 wallets 表（如果 wallets 表余额为0）
UPDATE `wallets` w
INNER JOIN `users` u ON w.user_id = u.id
SET w.balance = CASE
    WHEN w.currency = 'CNY' THEN COALESCE(u.balance_cny, 0)
    WHEN w.currency = 'USDT' THEN COALESCE(u.balance_usdt, 0)
    ELSE w.balance
END
WHERE w.balance = 0;

-- 7. 同步 wallets 表的余额到 users 表（保持一致性）
UPDATE `users` u
INNER JOIN (
    SELECT user_id,
           SUM(CASE WHEN currency = 'CNY' THEN balance ELSE 0 END) as cny_balance,
           SUM(CASE WHEN currency = 'USDT' THEN balance ELSE 0 END) as usdt_balance
    FROM `wallets`
    GROUP BY user_id
) w ON u.id = w.user_id
SET u.balance_cny = w.cny_balance,
    u.balance_usdt = w.usdt_balance;

-- 完成提示
SELECT '余额分离迁移完成！' as message;
