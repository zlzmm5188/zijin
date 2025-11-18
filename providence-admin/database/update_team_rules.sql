-- ===================================================
-- PROVIDENCE 团队管理系统规则更新
-- ===================================================

USE providence;

-- 1. 添加VIP等级表的新字段（忽略错误如果已存在）
ALTER TABLE `prov_vip_levels` ADD COLUMN `level1_percent` DECIMAL(5,2) DEFAULT 0 COMMENT '一级推荐奖励百分比';
ALTER TABLE `prov_vip_levels` ADD COLUMN `level2_percent` DECIMAL(5,2) DEFAULT 0 COMMENT '二级推荐奖励百分比';
ALTER TABLE `prov_vip_levels` ADD COLUMN `extra_rate` DECIMAL(5,4) DEFAULT 0 COMMENT '额外加息率';

-- 2. 清空并重新插入VIP等级（0-8级）
TRUNCATE TABLE `prov_vip_levels`;

INSERT INTO `prov_vip_levels` (`id`, `name`, `min_invest`, `daily_withdraw_limit`, `withdraw_fee_rate`, `invite_reward_rate`, `invite_points`, `first_invest_bonus`, `level1_percent`, `level2_percent`, `extra_rate`, `privileges`) VALUES
(0, 'VIP0', 0, 5000, 0.01, 0.01, 20, 50, 1.00, 0.00, 0.0000, '{"name": "普通会员"}'),
(1, 'VIP1', 30000, 10000, 0.008, 0.02, 50, 100, 2.00, 1.00, 0.0005, '{"name": "青铜会员"}'),
(2, 'VIP2', 100000, 50000, 0.006, 0.03, 100, 200, 3.00, 2.00, 0.0010, '{"name": "白银会员"}'),
(3, 'VIP3', 250000, 100000, 0.005, 0.04, 200, 300, 4.00, 2.00, 0.0012, '{"name": "黄金会员"}'),
(4, 'VIP4', 800000, 200000, 0.003, 0.05, 500, 500, 5.00, 3.00, 0.0015, '{"name": "铂金会员"}'),
(5, 'VIP5', 1500000, 500000, 0.002, 0.05, 1000, 1000, 5.00, 4.00, 0.0016, '{"name": "钻石会员"}'),
(6, 'VIP6', 3800000, 1000000, 0.001, 0.06, 2000, 2000, 6.00, 4.00, 0.0018, '{"name": "皇冠会员"}'),
(7, 'VIP7', 8000000, 5000000, 0.0005, 0.06, 5000, 5000, 6.00, 5.00, 0.0023, '{"name": "至尊会员"}'),
(8, 'VIP8', 13000000, 99999999, 0, 0.07, 10000, 10000, 7.00, 5.00, 0.0025, '{"name": "荣耀会员"}');

-- 3. 创建团队奖励规则表
DROP TABLE IF EXISTS `prov_team_reward_rules`;
CREATE TABLE `prov_team_reward_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_members` INT NOT NULL COMMENT '团队人数要求',
  `total_invest` DECIMAL(15,2) NOT NULL COMMENT '累计投资额要求',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `status` TINYINT DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='团队奖励规则';

-- 插入团队奖励规则
INSERT INTO `prov_team_reward_rules` (`team_members`, `total_invest`, `reward_amount`, `sort_order`) VALUES
(3, 80000, 1800, 1),
(5, 150000, 2500, 2),
(10, 500000, 8800, 3),
(20, 1500000, 18000, 4),
(50, 3800000, 25000, 5),
(100, 8800000, 38000, 6),
(200, 15000000, 66000, 7),
(500, 58000000, 100000, 8),
(1000, 98000000, 180000, 9);

-- 4. 团队奖励领取记录表
DROP TABLE IF EXISTS `prov_team_rewards_claimed`;
CREATE TABLE `prov_team_rewards_claimed` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `rule_id` INT UNSIGNED NOT NULL COMMENT '规则ID',
  `team_members` INT NOT NULL COMMENT '达标时团队人数',
  `total_invest` DECIMAL(15,2) NOT NULL COMMENT '达标时累计投资',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `status` TINYINT DEFAULT 1 COMMENT '1已发放',
  `claimed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='团队奖励领取记录';

-- 5. 推荐奖励记录表
DROP TABLE IF EXISTS `prov_referral_rewards`;
CREATE TABLE `prov_referral_rewards` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '获得奖励的用户ID',
  `referred_user_id` INT UNSIGNED NOT NULL COMMENT '被推荐人ID',
  `invest_id` INT UNSIGNED COMMENT '投资订单ID',
  `level` TINYINT NOT NULL COMMENT '推荐层级:1一级 2二级',
  `vip_level` TINYINT NOT NULL COMMENT '获奖时的VIP等级',
  `invest_amount` DECIMAL(15,2) NOT NULL COMMENT '投资金额',
  `reward_percent` DECIMAL(5,2) NOT NULL COMMENT '奖励百分比',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `status` TINYINT DEFAULT 1 COMMENT '1已发放',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_referred` (`referred_user_id`),
  INDEX `idx_invest` (`invest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐奖励记录';

-- 更新users表，确保VIP等级默认值为0
ALTER TABLE `prov_users` MODIFY `vip_level` TINYINT DEFAULT 0 COMMENT 'VIP等级 0-8';

SELECT 'SQL更新成功！' as result;
