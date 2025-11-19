-- 添加币种支持和完善字段

USE providence;

-- 用户表添加币种钱包字段
ALTER TABLE `prov_users` 
ADD COLUMN `balance_cny` DECIMAL(15,2) DEFAULT 0 COMMENT '人民币余额' AFTER `balance`,
ADD COLUMN `balance_usdt` DECIMAL(15,2) DEFAULT 0 COMMENT 'USDT余额' AFTER `balance_cny`,
ADD COLUMN `frozen_cny` DECIMAL(15,2) DEFAULT 0 COMMENT '人民币冻结' AFTER `frozen_balance`,
ADD COLUMN `frozen_usdt` DECIMAL(15,2) DEFAULT 0 COMMENT 'USDT冻结' AFTER `frozen_cny`;

-- 投资项目表添加币种字段
ALTER TABLE `prov_invest_projects`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种:CNY/USDT' AFTER `project_type`,
ADD COLUMN `version` INT DEFAULT 1 COMMENT '版本号' AFTER `project_code`,
ADD COLUMN `published_at` DATETIME COMMENT '发布时间' AFTER `status`;

-- 投资订单表添加币种字段
ALTER TABLE `prov_invest_orders`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `project_type`;

-- 钱包流水表添加币种字段
ALTER TABLE `prov_wallet_logs`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `type`;

-- 充值提现表添加币种
ALTER TABLE `prov_recharge_records`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `payment_method`;

ALTER TABLE `prov_withdraw_records`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `withdraw_method`;

-- 推荐返利表添加币种
CREATE TABLE IF NOT EXISTS `prov_referral_rewards` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '获得奖励的用户ID',
  `referred_user_id` INT UNSIGNED NOT NULL COMMENT '被推荐人ID',
  `invest_id` BIGINT UNSIGNED COMMENT '投资订单ID',
  `level` TINYINT NOT NULL COMMENT '推荐层级:1一级 2二级',
  `vip_level` TINYINT NOT NULL COMMENT '获奖时的VIP等级',
  `invest_amount` DECIMAL(15,2) NOT NULL COMMENT '投资金额',
  `reward_percent` DECIMAL(5,2) NOT NULL COMMENT '奖励百分比',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种',
  `status` TINYINT DEFAULT 1 COMMENT '1已发放',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_referred` (`referred_user_id`),
  INDEX `idx_invest` (`invest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐奖励记录';

-- VIP历史记录表
CREATE TABLE IF NOT EXISTS `prov_vip_history_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `old_level` TINYINT NOT NULL,
  `new_level` TINYINT NOT NULL,
  `total_invest` DECIMAL(15,2) NOT NULL COMMENT '触发升级时的累计投资',
  `upgraded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='VIP升级历史';

SELECT '✅ 币种支持和字段更新完成！' as result;
