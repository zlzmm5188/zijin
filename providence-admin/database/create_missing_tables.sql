-- 创建缺失的数据库表
USE providence;

CREATE TABLE IF NOT EXISTS `recharge_records` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `order_no` VARCHAR(32) UNIQUE NOT NULL,
  `user_id` BIGINT NOT NULL,
  `amount` DECIMAL(18,2) NOT NULL,
  `payment_method` VARCHAR(50),
  `certificate_images` TEXT,
  `currency` ENUM('CNY','USDT') DEFAULT 'CNY',
  `status` TINYINT DEFAULT 0,
  `remark` VARCHAR(500),
  `reviewed_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `withdraw_records` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `order_no` VARCHAR(32) UNIQUE NOT NULL,
  `user_id` BIGINT NOT NULL,
  `amount` DECIMAL(18,2) NOT NULL,
  `fee` DECIMAL(18,2) DEFAULT 0,
  `actual_amount` DECIMAL(18,2) NOT NULL,
  `withdraw_method` VARCHAR(50),
  `bank_info` TEXT,
  `currency` ENUM('CNY','USDT') DEFAULT 'CNY',
  `status` TINYINT DEFAULT 0,
  `remark` VARCHAR(500),
  `reviewed_at` DATETIME,
  `paid_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_kyc` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `user_id` BIGINT UNIQUE NOT NULL,
  `real_name` VARCHAR(50) NOT NULL,
  `id_card` VARCHAR(20) NOT NULL,
  `id_card_front` VARCHAR(255),
  `id_card_back` VARCHAR(255),
  `hand_held_photo` VARCHAR(255),
  `status` TINYINT DEFAULT 0,
  `reject_reason` VARCHAR(500),
  `reviewed_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `module` VARCHAR(50),
  `action` VARCHAR(100),
  `actor_type` ENUM('ADMIN','USER','SYSTEM'),
  `actor_id` BIGINT,
  `target_type` VARCHAR(50),
  `target_id` BIGINT,
  `before_data` TEXT,
  `after_data` TEXT,
  `ip` VARCHAR(50),
  `user_agent` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SELECT '✅ 表创建完成！' as result;
