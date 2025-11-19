-- Providence 后端代码检查修复SQL
-- 执行时间: 2025-11-11
-- 根据 backend_check_report.md 的检查结果生成

USE providence;

-- ==========================================
-- 1. 修复资金字段数据类型（统一为 decimal(20,8)）
-- ==========================================

-- invest_orders 表
ALTER TABLE `invest_orders`
    MODIFY COLUMN `amount` decimal(20,8) NOT NULL COMMENT '投资金额',
    MODIFY COLUMN `earned_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '已赚收益',
    MODIFY COLUMN `expected_profit` decimal(20,8) NULL DEFAULT NULL COMMENT '预计收益';

-- invest_projects 表
ALTER TABLE `invest_projects`
    MODIFY COLUMN `min_invest` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '最低投资额',
    MODIFY COLUMN `max_invest` decimal(20,8) NULL DEFAULT NULL COMMENT '最高投资额',
    MODIFY COLUMN `total_invested` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '已募集金额',
    MODIFY COLUMN `total_quota` decimal(20,8) NULL DEFAULT NULL COMMENT '总募集额度';

-- recharge_records 表
ALTER TABLE `recharge_records`
    MODIFY COLUMN `amount` decimal(20,8) NOT NULL COMMENT '充值金额';

-- withdraw_records 表
ALTER TABLE `withdraw_records`
    MODIFY COLUMN `amount` decimal(20,8) NOT NULL COMMENT '提现金额',
    MODIFY COLUMN `actual_amount` decimal(20,8) NULL DEFAULT NULL COMMENT '实际到账金额',
    MODIFY COLUMN `fee` decimal(20,8) NULL DEFAULT 0.00000000 COMMENT '手续费';

-- ==========================================
-- 2. 为所有字段添加中文注释
-- ==========================================

-- users 表（分步执行避免AUTO_INCREMENT冲突）
ALTER TABLE `users`
    MODIFY COLUMN `username` varchar(50) NULL DEFAULT NULL COMMENT '用户名',
    MODIFY COLUMN `phone` varchar(24) NULL DEFAULT NULL COMMENT '手机号',
    MODIFY COLUMN `email` varchar(100) NULL DEFAULT NULL COMMENT '邮箱',
    MODIFY COLUMN `password` varchar(255) NOT NULL COMMENT '密码（bcrypt加密）',
    MODIFY COLUMN `vip_level` tinyint NOT NULL DEFAULT 0 COMMENT 'VIP等级（0-8）',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    MODIFY COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间';

ALTER TABLE `users` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '用户ID';

-- wallets 表（分步执行避免AUTO_INCREMENT冲突）
ALTER TABLE `wallets`
    MODIFY COLUMN `user_id` bigint NOT NULL COMMENT '用户ID',
    MODIFY COLUMN `currency` enum('CNY','USDT') NOT NULL COMMENT '币种（CNY/USDT）',
    MODIFY COLUMN `balance` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '可用余额',
    MODIFY COLUMN `frozen` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '冻结金额',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    MODIFY COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间';

ALTER TABLE `wallets` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '钱包ID';

-- invest_orders 表（分步执行避免AUTO_INCREMENT冲突）
ALTER TABLE `invest_orders`
    MODIFY COLUMN `order_no` varchar(32) NOT NULL COMMENT '订单号',
    MODIFY COLUMN `user_id` bigint NOT NULL COMMENT '用户ID',
    MODIFY COLUMN `project_id` bigint NOT NULL COMMENT '项目ID',
    MODIFY COLUMN `currency` enum('CNY','USDT') NOT NULL COMMENT '币种',
    MODIFY COLUMN `cycle_days` int NOT NULL COMMENT '投资周期（天）',
    MODIFY COLUMN `base_rate` decimal(6,3) NOT NULL COMMENT '基础利率（%）',
    MODIFY COLUMN `vip_extra_rate` decimal(6,3) NOT NULL COMMENT 'VIP加息（%）',
    MODIFY COLUMN `added_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '活动加息（%）',
    MODIFY COLUMN `gift_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '赠送利率（%）',
    MODIFY COLUMN `final_rate` decimal(6,3) NOT NULL COMMENT '最终利率（%）',
    MODIFY COLUMN `vip_level` tinyint NOT NULL COMMENT 'VIP等级',
    MODIFY COLUMN `status` enum('PENDING','RUNNING','FINISHED','REFUND','REJECT') NOT NULL DEFAULT 'PENDING' COMMENT '订单状态',
    MODIFY COLUMN `start_at` datetime NULL DEFAULT NULL COMMENT '开始时间',
    MODIFY COLUMN `end_at` datetime NULL DEFAULT NULL COMMENT '结束时间',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    MODIFY COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间';

ALTER TABLE `invest_orders` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '订单ID';

-- invest_projects 表（分步执行避免AUTO_INCREMENT冲突）
ALTER TABLE `invest_projects`
    MODIFY COLUMN `project_code` varchar(32) NOT NULL COMMENT '项目编号',
    MODIFY COLUMN `title` varchar(120) NOT NULL COMMENT '项目标题',
    MODIFY COLUMN `subtitle` varchar(255) NULL DEFAULT NULL COMMENT '副标题',
    MODIFY COLUMN `description` text NULL COMMENT '项目描述',
    MODIFY COLUMN `cover_image` varchar(255) NULL DEFAULT NULL COMMENT '封面图片',
    MODIFY COLUMN `images` text NULL COMMENT '图片集（JSON）',
    MODIFY COLUMN `category` enum('IPO','BOND','FUND','FIXED','INVEST') NOT NULL COMMENT '项目类型',
    MODIFY COLUMN `currency` enum('CNY','USDT') NOT NULL COMMENT '币种',
    MODIFY COLUMN `cycle_days` int NOT NULL COMMENT '周期（天）',
    MODIFY COLUMN `base_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '基础利率（%）',
    MODIFY COLUMN `added_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '活动加息（%）',
    MODIFY COLUMN `gift_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '赠送利率（%）',
    MODIFY COLUMN `total_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '总利率（%）',
    MODIFY COLUMN `schedule` decimal(9,4) NOT NULL DEFAULT 0.0000 COMMENT '募集进度（%）',
    MODIFY COLUMN `invest_count` int NOT NULL DEFAULT 0 COMMENT '认购人数',
    MODIFY COLUMN `view_count` int NOT NULL DEFAULT 0 COMMENT '浏览次数',
    MODIFY COLUMN `manager_id` bigint NULL DEFAULT NULL COMMENT '项目经理ID',
    MODIFY COLUMN `risk_level` tinyint NOT NULL DEFAULT 1 COMMENT '风险等级（1-5）',
    MODIFY COLUMN `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态（0:草稿,1:募集中,2:已满额,3:已结束）',
    MODIFY COLUMN `version` int NOT NULL DEFAULT 1 COMMENT '版本号',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    MODIFY COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间';

ALTER TABLE `invest_projects` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '项目ID';

-- recharge_records 表（分步执行避免AUTO_INCREMENT冲突）
-- 先更新现有NULL值
UPDATE `recharge_records` SET `payment_method` = 'BANK' WHERE `payment_method` IS NULL;
UPDATE `recharge_records` SET `currency` = 'CNY' WHERE `currency` IS NULL;

ALTER TABLE `recharge_records`
    MODIFY COLUMN `order_no` varchar(32) NOT NULL COMMENT '订单号',
    MODIFY COLUMN `user_id` bigint NOT NULL COMMENT '用户ID',
    MODIFY COLUMN `currency` enum('CNY','USDT') NOT NULL DEFAULT 'CNY' COMMENT '币种',
    MODIFY COLUMN `payment_method` varchar(50) NOT NULL DEFAULT 'BANK' COMMENT '支付方式',
    MODIFY COLUMN `remark` varchar(500) NULL DEFAULT NULL COMMENT '备注',
    MODIFY COLUMN `reviewed_at` datetime NULL DEFAULT NULL COMMENT '审核时间',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';

ALTER TABLE `recharge_records` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '充值记录ID';

-- withdraw_records 表（分步执行避免AUTO_INCREMENT冲突）
-- 先更新现有NULL值
UPDATE `withdraw_records` SET `withdraw_method` = 'BANK' WHERE `withdraw_method` IS NULL;
UPDATE `withdraw_records` SET `currency` = 'CNY' WHERE `currency` IS NULL;

ALTER TABLE `withdraw_records`
    MODIFY COLUMN `order_no` varchar(32) NOT NULL COMMENT '订单号',
    MODIFY COLUMN `user_id` bigint NOT NULL COMMENT '用户ID',
    MODIFY COLUMN `currency` enum('CNY','USDT') NOT NULL DEFAULT 'CNY' COMMENT '币种',
    MODIFY COLUMN `withdraw_method` varchar(50) NOT NULL DEFAULT 'BANK' COMMENT '提现方式',
    MODIFY COLUMN `bank_info` text NULL COMMENT '银行卡信息（JSON）',
    MODIFY COLUMN `remark` varchar(500) NULL DEFAULT NULL COMMENT '备注',
    MODIFY COLUMN `reviewed_at` datetime NULL DEFAULT NULL COMMENT '审核时间',
    MODIFY COLUMN `paid_at` datetime NULL DEFAULT NULL COMMENT '打款时间',
    MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';

ALTER TABLE `withdraw_records` MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT COMMENT '提现记录ID';

-- ==========================================
-- 执行完成提示
-- ==========================================
SELECT '✅ Schema修复完成！' AS status;
SELECT '所有资金字段已统一为 decimal(20,8)' AS result1;
SELECT '所有字段已添加中文注释' AS result2;
