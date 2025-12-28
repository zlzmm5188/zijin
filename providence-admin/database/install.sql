-- ===================================================
-- PROVIDENCE 后台管理系统数据库安装脚本
-- 创建时间: 2024-11-10
-- ===================================================

-- 创建数据库
CREATE DATABASE IF NOT EXISTS `providence` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `providence`;

-- 1. 用户表
CREATE TABLE IF NOT EXISTS `prov_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
  `email` VARCHAR(100) UNIQUE COMMENT '邮箱',
  `phone` VARCHAR(20) UNIQUE COMMENT '手机号',
  `password` VARCHAR(255) NOT NULL COMMENT '密码(加密)',
  `real_name` VARCHAR(50) COMMENT '真实姓名',
  `id_card` VARCHAR(20) COMMENT '身份证号',
  `vip_level` TINYINT DEFAULT 1 COMMENT 'VIP等级 1-6',
  `balance` DECIMAL(15,2) DEFAULT 0.00 COMMENT '账户余额',
  `frozen_balance` DECIMAL(15,2) DEFAULT 0.00 COMMENT '冻结金额',
  `points` INT DEFAULT 0 COMMENT '积分',
  `trial_balance` DECIMAL(15,2) DEFAULT 0.00 COMMENT '体验金余额',
  `invite_code` VARCHAR(20) UNIQUE COMMENT '邀请码',
  `parent_id` INT UNSIGNED COMMENT '推荐人ID',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1正常 2冻结 3禁用',
  `kyc_status` TINYINT DEFAULT 0 COMMENT '实名状态:0未认证 1审核中 2已认证 3失败',
  `avatar` VARCHAR(255) COMMENT '头像',
  `login_ip` VARCHAR(50) COMMENT '最后登录IP',
  `login_time` DATETIME COMMENT '最后登录时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_parent` (`parent_id`),
  INDEX `idx_vip` (`vip_level`),
  INDEX `idx_status` (`status`),
  INDEX `idx_invite` (`invite_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 2. VIP等级配置表
CREATE TABLE IF NOT EXISTS `prov_vip_levels` (
  `id` TINYINT UNSIGNED PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL COMMENT '等级名称',
  `icon` VARCHAR(255) COMMENT '图标',
  `min_invest` DECIMAL(15,2) DEFAULT 0 COMMENT '最低投资额',
  `daily_withdraw_limit` DECIMAL(15,2) COMMENT '每日提现限额',
  `withdraw_fee_rate` DECIMAL(5,4) COMMENT '提现手续费率',
  `invite_reward_rate` DECIMAL(5,4) COMMENT '邀请奖励比例',
  `invite_points` INT DEFAULT 0 COMMENT '邀请奖励积分',
  `first_invest_bonus` INT DEFAULT 0 COMMENT '首投额外奖励',
  `privileges` TEXT COMMENT '特权列表(JSON)',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='VIP等级配置';

-- 3. 投资项目表
CREATE TABLE IF NOT EXISTS `prov_projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL COMMENT '项目名称',
  `subtitle` VARCHAR(255) COMMENT '副标题',
  `description` TEXT COMMENT '项目描述',
  `cover_image` VARCHAR(255) COMMENT '封面图',
  `images` TEXT COMMENT '项目图片列表(JSON)',
  `category` VARCHAR(50) COMMENT '项目分类',
  `min_invest` DECIMAL(15,2) NOT NULL COMMENT '最低投资额',
  `max_invest` DECIMAL(15,2) COMMENT '最高投资额',
  `daily_rate` DECIMAL(5,4) NOT NULL COMMENT '日收益率',
  `total_days` INT NOT NULL COMMENT '投资周期(天)',
  `total_return_rate` DECIMAL(5,2) COMMENT '总收益率',
  `risk_level` TINYINT DEFAULT 1 COMMENT '风险等级:1低 2中 3高',
  `manager_id` INT UNSIGNED COMMENT '项目经理ID',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1正常 2暂停 3结束',
  `sort_order` INT DEFAULT 0 COMMENT '排序',
  `view_count` INT DEFAULT 0 COMMENT '浏览次数',
  `invest_count` INT DEFAULT 0 COMMENT '投资人数',
  `total_invested` DECIMAL(15,2) DEFAULT 0 COMMENT '累计投资额',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='投资项目表';

-- 4. 项目经理表
CREATE TABLE IF NOT EXISTS `prov_project_managers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '姓名',
  `title` VARCHAR(100) COMMENT '职位',
  `avatar` VARCHAR(255) COMMENT '头像',
  `bio` TEXT COMMENT '简介',
  `experience` TEXT COMMENT '工作经历',
  `achievements` TEXT COMMENT '成就列表(JSON)',
  `contact_email` VARCHAR(100) COMMENT '联系邮箱',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1正常 2禁用',
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目经理表';

-- 5. 用户投资订单表
CREATE TABLE IF NOT EXISTS `prov_user_investments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL COMMENT '订单号',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `project_id` INT UNSIGNED NOT NULL COMMENT '项目ID',
  `invest_amount` DECIMAL(15,2) NOT NULL COMMENT '投资金额',
  `daily_rate` DECIMAL(5,4) NOT NULL COMMENT '日收益率',
  `total_days` INT NOT NULL COMMENT '投资天数',
  `earned_amount` DECIMAL(15,2) DEFAULT 0 COMMENT '已获收益',
  `total_return` DECIMAL(15,2) COMMENT '预期总收益',
  `start_date` DATE NOT NULL COMMENT '开始日期',
  `end_date` DATE NOT NULL COMMENT '结束日期',
  `is_trial` TINYINT DEFAULT 0 COMMENT '是否体验金投资:0否 1是',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1进行中 2已完成 3已取消',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_project` (`project_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户投资订单表';

-- 6. 收益发放记录表
CREATE TABLE IF NOT EXISTS `prov_earnings_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `investment_id` INT UNSIGNED NOT NULL COMMENT '投资订单ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `amount` DECIMAL(15,2) NOT NULL COMMENT '收益金额',
  `earn_date` DATE NOT NULL COMMENT '收益日期',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1已发放 2待发放',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_investment` (`investment_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_date` (`earn_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收益发放记录';

-- 7. 充值记录表
CREATE TABLE IF NOT EXISTS `prov_recharge_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL COMMENT '充值订单号',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `amount` DECIMAL(15,2) NOT NULL COMMENT '充值金额',
  `payment_method` VARCHAR(50) COMMENT '支付方式:bank/usdt/alipay',
  `payment_info` TEXT COMMENT '支付信息(JSON)',
  `certificate` VARCHAR(255) COMMENT '凭证图片',
  `status` TINYINT DEFAULT 0 COMMENT '状态:0待审核 1已通过 2已拒绝',
  `remark` VARCHAR(500) COMMENT '备注/拒绝原因',
  `reviewed_by` INT UNSIGNED COMMENT '审核人ID',
  `reviewed_at` DATETIME COMMENT '审核时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值记录表';

-- 8. 提现记录表
CREATE TABLE IF NOT EXISTS `prov_withdraw_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL COMMENT '提现订单号',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `amount` DECIMAL(15,2) NOT NULL COMMENT '提现金额',
  `fee` DECIMAL(15,2) DEFAULT 0 COMMENT '手续费',
  `actual_amount` DECIMAL(15,2) NOT NULL COMMENT '实际到账',
  `withdraw_method` VARCHAR(50) COMMENT '提现方式',
  `bank_info` TEXT COMMENT '银行卡/钱包信息(JSON)',
  `status` TINYINT DEFAULT 0 COMMENT '状态:0待审核 1已通过 2已拒绝 3已打款',
  `remark` VARCHAR(500) COMMENT '备注',
  `reviewed_by` INT UNSIGNED COMMENT '审核人ID',
  `reviewed_at` DATETIME COMMENT '审核时间',
  `paid_by` INT UNSIGNED COMMENT '打款人ID',
  `paid_at` DATETIME COMMENT '打款时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提现记录表';

-- 9. 银行卡表
CREATE TABLE IF NOT EXISTS `prov_bank_cards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `card_type` VARCHAR(20) COMMENT '类型:bank/usdt',
  `bank_name` VARCHAR(100) COMMENT '银行名称',
  `card_number` VARCHAR(50) NOT NULL COMMENT '卡号/钱包地址',
  `card_holder` VARCHAR(50) COMMENT '持卡人姓名',
  `bank_branch` VARCHAR(200) COMMENT '开户行',
  `is_default` TINYINT DEFAULT 0 COMMENT '是否默认',
  `status` TINYINT DEFAULT 0 COMMENT '状态:0待审核 1已通过 2已拒绝',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='银行卡表';

-- 10. 资金流水表
CREATE TABLE IF NOT EXISTS `prov_balance_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `type` VARCHAR(20) NOT NULL COMMENT '类型:recharge/withdraw/invest/earning/refund',
  `amount` DECIMAL(15,2) NOT NULL COMMENT '金额(正负)',
  `balance_before` DECIMAL(15,2) NOT NULL COMMENT '变动前余额',
  `balance_after` DECIMAL(15,2) NOT NULL COMMENT '变动后余额',
  `ref_id` BIGINT UNSIGNED COMMENT '关联ID',
  `remark` VARCHAR(500) COMMENT '备注',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='资金流水表';

-- 11. 新闻快讯表
CREATE TABLE IF NOT EXISTS `prov_news_flash` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL COMMENT '标题',
  `content` TEXT COMMENT '内容',
  `source` VARCHAR(100) COMMENT '来源',
  `category` VARCHAR(50) COMMENT '分类',
  `importance` TINYINT DEFAULT 1 COMMENT '重要性:1普通 2重要 3紧急',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1发布 2草稿',
  `view_count` INT DEFAULT 0 COMMENT '浏览量',
  `published_at` DATETIME COMMENT '发布时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='新闻快讯表';

-- 12. 公司动态表
CREATE TABLE IF NOT EXISTS `prov_company_news` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL COMMENT '标题',
  `subtitle` VARCHAR(255) COMMENT '副标题',
  `cover_image` VARCHAR(255) COMMENT '封面图',
  `content` LONGTEXT COMMENT '内容',
  `category` VARCHAR(50) COMMENT '分类:news/award/milestone',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1发布 2草稿',
  `is_top` TINYINT DEFAULT 0 COMMENT '是否置顶',
  `view_count` INT DEFAULT 0,
  `published_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公司动态表';

-- 13. 财经日历表
CREATE TABLE IF NOT EXISTS `prov_financial_calendar` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_date` DATE NOT NULL COMMENT '事件日期',
  `event_time` TIME COMMENT '事件时间',
  `country` VARCHAR(50) COMMENT '国家',
  `event_name` VARCHAR(200) NOT NULL COMMENT '事件名称',
  `importance` TINYINT COMMENT '重要性:1-5',
  `previous_value` VARCHAR(50) COMMENT '前值',
  `forecast_value` VARCHAR(50) COMMENT '预测值',
  `actual_value` VARCHAR(50) COMMENT '实际值',
  `impact` VARCHAR(50) COMMENT '影响',
  `status` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='财经日历';

-- 14. 投资课堂表
CREATE TABLE IF NOT EXISTS `prov_education_courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255),
  `cover_image` VARCHAR(255),
  `content` LONGTEXT,
  `category` VARCHAR(50) COMMENT '分类:beginner/advanced/analysis',
  `difficulty` TINYINT DEFAULT 1 COMMENT '难度:1入门 2中级 3高级',
  `duration` INT COMMENT '时长(分钟)',
  `status` TINYINT DEFAULT 1,
  `view_count` INT DEFAULT 0,
  `like_count` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='投资课堂';

-- 15. 积分商城商品表
CREATE TABLE IF NOT EXISTS `prov_shop_products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255),
  `points_required` INT NOT NULL COMMENT '所需积分',
  `stock` INT DEFAULT 0 COMMENT '库存',
  `sales_count` INT DEFAULT 0 COMMENT '销量',
  `status` TINYINT DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分商城商品';

-- 16. 积分兑换记录表
CREATE TABLE IF NOT EXISTS `prov_points_exchange_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `points_cost` INT NOT NULL,
  `status` TINYINT DEFAULT 0 COMMENT '0待发货 1已发货 2已完成',
  `address_info` TEXT COMMENT '收货地址(JSON)',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分兑换记录';

-- 17. 积分明细表
CREATE TABLE IF NOT EXISTS `prov_points_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(20) NOT NULL COMMENT '类型:checkin/invite/invest/exchange',
  `points` INT NOT NULL COMMENT '积分(正负)',
  `balance_before` INT NOT NULL COMMENT '变动前积分',
  `balance_after` INT NOT NULL COMMENT '变动后积分',
  `remark` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分明细表';

-- 18. 管理员表
CREATE TABLE IF NOT EXISTS `prov_admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `real_name` VARCHAR(50),
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `role` VARCHAR(20) DEFAULT 'admin' COMMENT 'super_admin/admin/operator',
  `permissions` TEXT COMMENT '权限列表(JSON)',
  `status` TINYINT DEFAULT 1,
  `last_login_ip` VARCHAR(50),
  `last_login_time` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- 19. 操作日志表
CREATE TABLE IF NOT EXISTS `prov_admin_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT '操作类型',
  `module` VARCHAR(50) COMMENT '模块',
  `content` TEXT COMMENT '操作内容',
  `ip` VARCHAR(50),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员操作日志';

-- 20. 系统配置表
CREATE TABLE IF NOT EXISTS `prov_system_config` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) UNIQUE NOT NULL,
  `value` TEXT,
  `description` VARCHAR(255),
  `group` VARCHAR(50) COMMENT '分组',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- ===================================================
-- 初始数据
-- ===================================================

-- 插入默认管理员 (用户名:admin, 密码:admin123)
INSERT INTO `prov_admins` (`username`, `password`, `real_name`, `role`) 
VALUES ('admin', 'e10adc3949ba59abbe56e057f20f883e', '超级管理员', 'super_admin');

-- 插入VIP等级配置
INSERT INTO `prov_vip_levels` (`id`, `name`, `min_invest`, `daily_withdraw_limit`, `withdraw_fee_rate`, `invite_reward_rate`, `invite_points`, `first_invest_bonus`) VALUES
(1, 'VIP1', 0, 10000, 0.005, 0.01, 50, 100),
(2, 'VIP2', 10000, 50000, 0.003, 0.015, 100, 200),
(3, 'VIP3', 50000, 100000, 0.002, 0.02, 200, 300),
(4, 'VIP4', 200000, 500000, 0.001, 0.03, 500, 500),
(5, 'VIP5', 500000, 1000000, 0.0005, 0.04, 1000, 1000),
(6, 'VIP6', 1000000, 9999999, 0, 0.05, 2000, 2000);

-- 插入示例项目
INSERT INTO `prov_projects` (`title`, `subtitle`, `description`, `min_invest`, `max_invest`, `daily_rate`, `total_days`, `total_return_rate`, `category`, `risk_level`, `status`) VALUES
('稳健增长基金', '低风险稳定收益', '专注于稳定增长的优质企业投资组合', 1000, 100000, 0.0015, 30, 4.5, 'fund', 1, 1),
('科技创新项目', '高成长潜力', '投资于前沿科技领域的创新企业', 5000, 500000, 0.0025, 60, 15, 'tech', 2, 1),
('全球配置策略', '分散风险资产配置', '全球范围内的多元化资产配置', 10000, 1000000, 0.002, 90, 18, 'global', 2, 1);

-- 插入示例项目经理
INSERT INTO `prov_project_managers` (`name`, `title`, `bio`) VALUES
('张明', '首席投资官', '拥有15年投资经验，专注于股权投资和并购'),
('李华', '高级基金经理', '管理超过100亿资产，擅长科技领域投资'),
('王芳', '风控总监', '10年风险管理经验，曾任职于顶级投行');

-- 插入系统配置
INSERT INTO `prov_system_config` (`key`, `value`, `description`, `group`) VALUES
('site_name', 'PROVIDENCE', '网站名称', 'basic'),
('site_url', 'https://agx.bi', '网站地址', 'basic'),
('trial_fund_amount', '1000', '新用户体验金额度', 'promotion'),
('min_withdraw', '100', '最低提现金额', 'finance'),
('max_withdraw_daily', '100000', '每日最大提现额度', 'finance');

-- ===================================================
-- 安装完成
-- ===================================================
