-- PROVIDENCE 完整数据库安装 v2.1
USE providence;

-- 项目表
CREATE TABLE IF NOT EXISTS `invest_projects` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `project_code` VARCHAR(32) NOT NULL UNIQUE,
  `title` VARCHAR(120) NOT NULL,
  `subtitle` VARCHAR(255),
  `description` TEXT,
  `category` ENUM('IPO','BOND','FUND','FIXED','INVEST') NOT NULL,
  `currency` ENUM('CNY','USDT') NOT NULL DEFAULT 'CNY',
  `cover_image` VARCHAR(255),
  `images` TEXT,
  
  -- 收益参数
  `cycle_days` INT NOT NULL DEFAULT 30,
  `base_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '基础收益%',
  `added_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '额外临时加息%',
  `gift_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '活动加息%',
  
  -- 投资限制
  `min_invest` DECIMAL(18,2) NOT NULL,
  `max_invest` DECIMAL(18,2),
  `total_quota` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `schedule` DECIMAL(9,4) NOT NULL DEFAULT 0,
  
  `manager_id` BIGINT,
  `risk_level` TINYINT DEFAULT 1,
  `status` TINYINT NOT NULL DEFAULT 1,
  `version` INT NOT NULL DEFAULT 1,
  `view_count` INT NOT NULL DEFAULT 0,
  `invest_count` INT NOT NULL DEFAULT 0,
  `total_invested` DECIMAL(18,2) NOT NULL DEFAULT 0,
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_projects_status` (`status`),
  INDEX `idx_projects_category` (`category`)
) ENGINE=InnoDB;

-- 投资订单表
CREATE TABLE IF NOT EXISTS `invest_orders` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `order_no` VARCHAR(32) NOT NULL UNIQUE,
  `user_id` BIGINT NOT NULL,
  `project_id` BIGINT NOT NULL,
  `currency` ENUM('CNY','USDT') NOT NULL,
  `amount` DECIMAL(18,2) NOT NULL,
  
  -- 快照参数
  `vip_level` TINYINT NOT NULL,
  `base_rate` DECIMAL(6,3) NOT NULL,
  `vip_extra_rate` DECIMAL(6,3) NOT NULL,
  `added_rate` DECIMAL(6,3) NOT NULL DEFAULT 0,
  `gift_rate` DECIMAL(6,3) NOT NULL DEFAULT 0,
  `final_rate` DECIMAL(6,3) NOT NULL,
  `cycle_days` INT NOT NULL,
  
  `expected_profit` DECIMAL(18,2) NOT NULL,
  `earned_amount` DECIMAL(18,2) NOT NULL DEFAULT 0,
  
  `status` ENUM('PENDING','RUNNING','FINISHED','REFUND','REJECT') NOT NULL DEFAULT 'PENDING',
  `start_at` DATETIME,
  `end_at` DATETIME,
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`status`)
) ENGINE=InnoDB;

-- VIP规则表
CREATE TABLE IF NOT EXISTS `vip_interest_rules` (
  `vip_level` TINYINT PRIMARY KEY,
  `vip_name` VARCHAR(50) NOT NULL,
  `min_invest` DECIMAL(18,2) NOT NULL,
  `extra_rate` DECIMAL(6,3) NOT NULL,
  `extra_rate_display` VARCHAR(20),
  `level1_percent` DECIMAL(5,2) DEFAULT 0,
  `level2_percent` DECIMAL(5,2) DEFAULT 0,
  `daily_withdraw_limit` DECIMAL(18,2),
  `withdraw_fee_rate` DECIMAL(5,4),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 项目经理表
CREATE TABLE IF NOT EXISTS `project_managers` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100),
  `avatar` VARCHAR(255),
  `bio` TEXT,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 插入示例数据
INSERT INTO `invest_projects` (`project_code`, `title`, `subtitle`, `category`, `currency`, `cycle_days`, `base_rate`, `added_rate`, `gift_rate`, `min_invest`, `max_invest`, `total_quota`, `schedule`, `status`) VALUES
('PRJ202411001', '稳健增长基金', '低风险稳定收益', 'FUND', 'CNY', 30, 4.500, 0.200, 0.000, 1000, 100000, 5000000, 25.50, 1),
('PRJ202411002', '科技创新项目', '高成长潜力', 'FUND', 'CNY', 60, 15.000, 0.000, 0.500, 5000, 500000, 10000000, 10.00, 1),
('PRJ202411003', '全球配置策略', '分散风险资产配置', 'FUND', 'CNY', 90, 18.000, 0.300, 0.000, 10000, 1000000, 20000000, 5.00, 1);

INSERT INTO `project_managers` (`name`, `title`, `bio`) VALUES
('张明', '首席投资官', '拥有15年投资经验，专注于股权投资和并购'),
('李华', '高级基金经理', '管理超过100亿资产，擅长科技领域投资');

INSERT INTO `vip_interest_rules` (`vip_level`, `vip_name`, `min_invest`, `extra_rate`, `extra_rate_display`, `level1_percent`, `level2_percent`) VALUES
(0, 'VIP0', 0, 0.000, '+0%', 1.00, 0.00),
(1, 'VIP1', 30000, 0.300, '+0.3%', 2.00, 1.00),
(2, 'VIP2', 100000, 0.600, '+0.6%', 3.00, 2.00),
(3, 'VIP3', 250000, 0.720, '+0.72%', 4.00, 2.00),
(4, 'VIP4', 800000, 0.900, '+0.9%', 5.00, 3.00),
(5, 'VIP5', 1500000, 0.960, '+0.96%', 5.00, 4.00),
(6, 'VIP6', 3800000, 1.080, '+1.08%', 6.00, 4.00),
(7, 'VIP7', 8000000, 1.380, '+1.38%', 6.00, 5.00),
(8, 'VIP8', 13000000, 1.500, '+1.5%', 7.00, 5.00);

-- 更新项目关联经理
UPDATE `invest_projects` SET `manager_id` = 1 WHERE id = 1;
UPDATE `invest_projects` SET `manager_id` = 2 WHERE id = 2;
UPDATE `invest_projects` SET `manager_id` = 1 WHERE id = 3;

SELECT '✅ 完整数据库安装完成！' as result;
