-- =====================================================
-- PROVIDENCE 后台管理增强模块 - 数据库架构
-- 版本: 1.0.0
-- 创建日期: 2024
-- =====================================================

USE providence;

-- =====================================================
-- 【管理员系统】Admin Management
-- =====================================================

-- 管理员账号表
DROP TABLE IF EXISTS `prov_admins`;
CREATE TABLE `prov_admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
  `password` VARCHAR(255) NOT NULL COMMENT '密码哈希',
  `real_name` VARCHAR(50) COMMENT '真实姓名',
  `email` VARCHAR(100) COMMENT '邮箱',
  `phone` VARCHAR(20) COMMENT '手机号',
  `avatar` VARCHAR(255) COMMENT '头像URL',
  `role_id` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '角色ID',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `last_login_at` DATETIME COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(50) COMMENT '最后登录IP',
  `login_count` INT NOT NULL DEFAULT 0 COMMENT '登录次数',
  `created_by` INT UNSIGNED COMMENT '创建人',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员账号表';

-- 管理员角色表
DROP TABLE IF EXISTS `prov_admin_roles`;
CREATE TABLE `prov_admin_roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE COMMENT '角色名称',
  `code` VARCHAR(50) NOT NULL UNIQUE COMMENT '角色代码',
  `description` VARCHAR(255) COMMENT '角色描述',
  `permissions` TEXT COMMENT 'JSON权限列表',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员角色表';

-- 管理员登录日志表
DROP TABLE IF EXISTS `prov_admin_login_logs`;
CREATE TABLE `prov_admin_login_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `login_ip` VARCHAR(50) NOT NULL,
  `ip_location` VARCHAR(200) COMMENT 'IP归属地',
  `device_info` VARCHAR(500) COMMENT '设备信息',
  `browser` VARCHAR(100) COMMENT '浏览器',
  `os` VARCHAR(100) COMMENT '操作系统',
  `login_time` DATETIME NOT NULL,
  `status` TINYINT DEFAULT 1 COMMENT '1成功 0失败',
  `fail_reason` VARCHAR(255) COMMENT '失败原因',
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员登录日志';

-- 管理员操作日志表
DROP TABLE IF EXISTS `prov_admin_operation_logs`;
CREATE TABLE `prov_admin_operation_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL COMMENT '模块名',
  `action` VARCHAR(100) NOT NULL COMMENT '操作类型',
  `method` VARCHAR(10) NOT NULL COMMENT '请求方式',
  `path` VARCHAR(255) NOT NULL COMMENT '请求路径',
  `params` TEXT COMMENT '请求参数',
  `result` TEXT COMMENT '操作结果',
  `ip` VARCHAR(50) NOT NULL,
  `user_agent` VARCHAR(500),
  `duration` INT COMMENT '耗时(ms)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_module` (`module`),
  INDEX `idx_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员操作日志';

-- =====================================================
-- 【系统配置】System Configuration
-- =====================================================

-- 系统配置表
DROP TABLE IF EXISTS `prov_system_configs`;
CREATE TABLE `prov_system_configs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group` VARCHAR(50) NOT NULL COMMENT '配置分组',
  `key` VARCHAR(100) NOT NULL COMMENT '配置键',
  `value` TEXT COMMENT '配置值',
  `type` VARCHAR(20) NOT NULL DEFAULT 'string' COMMENT 'string/number/boolean/json',
  `description` VARCHAR(255) COMMENT '配置说明',
  `is_public` TINYINT NOT NULL DEFAULT 0 COMMENT '是否公开',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_group_key` (`group`, `key`),
  INDEX `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- =====================================================
-- 【短信配置】SMS Configuration
-- =====================================================

-- 短信通道配置表
DROP TABLE IF EXISTS `prov_sms_channels`;
CREATE TABLE `prov_sms_channels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL COMMENT '通道名称',
  `code` VARCHAR(50) NOT NULL UNIQUE COMMENT '通道代码',
  `provider` VARCHAR(50) NOT NULL COMMENT '服务商(aliyun/tencent/253/custom)',
  `config` TEXT NOT NULL COMMENT 'JSON配置(appId/appKey/签名等)',
  `templates` TEXT COMMENT 'JSON模板配置',
  `priority` INT NOT NULL DEFAULT 0 COMMENT '优先级',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `daily_limit` INT NOT NULL DEFAULT 0 COMMENT '日限额(0不限)',
  `used_today` INT NOT NULL DEFAULT 0 COMMENT '今日已用',
  `success_count` INT NOT NULL DEFAULT 0 COMMENT '发送成功数',
  `fail_count` INT NOT NULL DEFAULT 0 COMMENT '发送失败数',
  `last_used_at` DATETIME COMMENT '最后使用时间',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status_priority` (`status`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信通道配置';

-- 短信发送记录表
DROP TABLE IF EXISTS `prov_sms_logs`;
CREATE TABLE `prov_sms_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `channel_id` INT UNSIGNED NOT NULL,
  `channel_name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `template_code` VARCHAR(50) COMMENT '模板代码',
  `content` TEXT COMMENT '短信内容',
  `params` TEXT COMMENT 'JSON参数',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '0待发送 1成功 2失败',
  `result` TEXT COMMENT '发送结果',
  `error_msg` VARCHAR(255) COMMENT '错误信息',
  `sent_at` DATETIME COMMENT '发送时间',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_phone` (`phone`),
  INDEX `idx_status` (`status`),
  INDEX `idx_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信发送记录';

-- =====================================================
-- 【支付通道配置】Payment Channel Configuration
-- =====================================================

-- 支付通道表
DROP TABLE IF EXISTS `prov_payment_channels`;
CREATE TABLE `prov_payment_channels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL COMMENT '通道名称',
  `code` VARCHAR(50) NOT NULL UNIQUE COMMENT '通道代码',
  `type` VARCHAR(20) NOT NULL COMMENT 'bank/alipay/wechat/usdt',
  `mode` VARCHAR(20) NOT NULL DEFAULT 'qrcode' COMMENT 'qrcode/api/manual',
  `config` TEXT COMMENT 'JSON配置(商户号/密钥等)',
  `qrcode_image` VARCHAR(255) COMMENT '收款码图片',
  `account_info` TEXT COMMENT 'JSON收款账户信息',
  `min_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '最低金额',
  `max_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '最高金额(0不限)',
  `fee_rate` DECIMAL(5,4) NOT NULL DEFAULT 0 COMMENT '费率',
  `fee_fixed` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '固定手续费',
  `priority` INT NOT NULL DEFAULT 0 COMMENT '优先级',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `daily_limit` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '日限额(0不限)',
  `used_today` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '今日已用',
  `total_amount` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '累计收款',
  `order_count` INT NOT NULL DEFAULT 0 COMMENT '订单数',
  `remark` VARCHAR(255) COMMENT '备注',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type` (`type`),
  INDEX `idx_status_priority` (`status`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付通道配置';

-- 支付二维码表
DROP TABLE IF EXISTS `prov_payment_qrcodes`;
CREATE TABLE `prov_payment_qrcodes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `channel_id` INT UNSIGNED NOT NULL COMMENT '关联通道',
  `name` VARCHAR(50) NOT NULL COMMENT '名称',
  `type` VARCHAR(20) NOT NULL COMMENT 'alipay/wechat/bank/usdt',
  `image` VARCHAR(255) NOT NULL COMMENT '二维码图片',
  `account` VARCHAR(100) COMMENT '收款账号',
  `account_name` VARCHAR(50) COMMENT '收款人姓名',
  `min_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `max_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1,
  `priority` INT NOT NULL DEFAULT 0,
  `used_today` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `daily_limit` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `remark` VARCHAR(255),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_channel` (`channel_id`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付二维码';

-- =====================================================
-- 【任务中心】Task Center
-- =====================================================

-- 任务配置表
DROP TABLE IF EXISTS `prov_tasks`;
CREATE TABLE `prov_tasks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '任务名称',
  `code` VARCHAR(50) NOT NULL UNIQUE COMMENT '任务代码',
  `type` VARCHAR(20) NOT NULL COMMENT 'daily/once/invite/invest/checkin',
  `description` TEXT COMMENT '任务描述',
  `icon` VARCHAR(255) COMMENT '任务图标',
  `reward_type` VARCHAR(20) NOT NULL DEFAULT 'points' COMMENT 'points/balance/coupon',
  `reward_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '奖励数量',
  `target_value` INT NOT NULL DEFAULT 1 COMMENT '目标值',
  `target_unit` VARCHAR(20) COMMENT '目标单位(次/元/人)',
  `vip_limit` TINYINT NOT NULL DEFAULT 0 COMMENT '最低VIP要求',
  `start_time` DATETIME COMMENT '开始时间',
  `end_time` DATETIME COMMENT '结束时间',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `sort_order` INT NOT NULL DEFAULT 0,
  `complete_count` INT NOT NULL DEFAULT 0 COMMENT '完成次数',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务配置表';

-- 用户任务进度表
DROP TABLE IF EXISTS `prov_user_tasks`;
CREATE TABLE `prov_user_tasks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `task_id` INT UNSIGNED NOT NULL,
  `task_code` VARCHAR(50) NOT NULL,
  `progress` INT NOT NULL DEFAULT 0 COMMENT '当前进度',
  `target` INT NOT NULL COMMENT '目标值',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '0进行中 1已完成 2已领取',
  `completed_at` DATETIME COMMENT '完成时间',
  `claimed_at` DATETIME COMMENT '领取时间',
  `reward_amount` DECIMAL(15,2) COMMENT '奖励数量',
  `date` DATE NOT NULL COMMENT '任务日期',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_task_date` (`user_id`, `task_id`, `date`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户任务进度';

-- =====================================================
-- 【邀请奖励配置】Invite Rewards Configuration
-- =====================================================

-- 邀请奖励规则表
DROP TABLE IF EXISTS `prov_invite_reward_rules`;
CREATE TABLE `prov_invite_reward_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL COMMENT '规则名称',
  `type` VARCHAR(20) NOT NULL COMMENT 'register/invest/recharge',
  `level` TINYINT NOT NULL DEFAULT 1 COMMENT '层级(1一级/2二级)',
  `reward_type` VARCHAR(20) NOT NULL DEFAULT 'percent' COMMENT 'fixed/percent',
  `reward_value` DECIMAL(10,4) NOT NULL COMMENT '奖励值(固定金额或百分比)',
  `min_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '最低触发金额',
  `max_reward` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '最高奖励(0不限)',
  `vip_limit` TINYINT NOT NULL DEFAULT 0 COMMENT '最低VIP要求',
  `status` TINYINT NOT NULL DEFAULT 1,
  `start_time` DATETIME COMMENT '开始时间',
  `end_time` DATETIME COMMENT '结束时间',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type_level` (`type`, `level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请奖励规则';

-- 邀请奖励记录表
DROP TABLE IF EXISTS `prov_invite_reward_logs`;
CREATE TABLE `prov_invite_reward_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '获奖用户',
  `from_user_id` INT UNSIGNED NOT NULL COMMENT '来源用户',
  `rule_id` INT UNSIGNED NOT NULL COMMENT '规则ID',
  `type` VARCHAR(20) NOT NULL COMMENT 'register/invest/recharge',
  `level` TINYINT NOT NULL COMMENT '层级',
  `ref_order_no` VARCHAR(50) COMMENT '关联订单号',
  `ref_amount` DECIMAL(15,2) COMMENT '关联金额',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1已发放 0待发放',
  `remark` VARCHAR(255),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_from_user` (`from_user_id`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请奖励记录';

-- =====================================================
-- 【用户资产调账】Balance Adjustment
-- =====================================================

-- 资产调账记录表
DROP TABLE IF EXISTS `prov_balance_adjustments`;
CREATE TABLE `prov_balance_adjustments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `currency` ENUM('CNY','USDT') NOT NULL DEFAULT 'CNY',
  `type` ENUM('ADD','SUBTRACT') NOT NULL COMMENT '加/减',
  `target` VARCHAR(20) NOT NULL COMMENT 'balance/frozen/points',
  `amount` DECIMAL(15,2) NOT NULL,
  `before_amount` DECIMAL(15,2) NOT NULL COMMENT '调账前金额',
  `after_amount` DECIMAL(15,2) NOT NULL COMMENT '调账后金额',
  `reason` VARCHAR(255) NOT NULL COMMENT '调账原因',
  `remark` TEXT COMMENT '备注',
  `admin_id` INT UNSIGNED NOT NULL COMMENT '操作管理员',
  `admin_name` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资产调账记录';

-- =====================================================
-- 【报表统计】Reports & Analytics
-- =====================================================

-- 每日统计报表
DROP TABLE IF EXISTS `prov_daily_reports`;
CREATE TABLE `prov_daily_reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL UNIQUE,
  `new_users` INT NOT NULL DEFAULT 0 COMMENT '新增用户',
  `active_users` INT NOT NULL DEFAULT 0 COMMENT '活跃用户',
  `total_users` INT NOT NULL DEFAULT 0 COMMENT '累计用户',
  `recharge_count` INT NOT NULL DEFAULT 0 COMMENT '充值笔数',
  `recharge_amount` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '充值金额',
  `withdraw_count` INT NOT NULL DEFAULT 0 COMMENT '提现笔数',
  `withdraw_amount` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '提现金额',
  `invest_count` INT NOT NULL DEFAULT 0 COMMENT '投资笔数',
  `invest_amount` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '投资金额',
  `profit_amount` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '收益发放',
  `net_income` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '净收入',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='每日统计报表';

-- 导出任务表
DROP TABLE IF EXISTS `prov_export_tasks`;
CREATE TABLE `prov_export_tasks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '任务名称',
  `type` VARCHAR(50) NOT NULL COMMENT '导出类型',
  `params` TEXT COMMENT 'JSON查询参数',
  `file_path` VARCHAR(255) COMMENT '文件路径',
  `file_size` INT COMMENT '文件大小',
  `row_count` INT COMMENT '数据行数',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '0处理中 1完成 2失败',
  `error_msg` VARCHAR(255),
  `admin_id` INT UNSIGNED NOT NULL,
  `started_at` DATETIME,
  `completed_at` DATETIME,
  `expires_at` DATETIME COMMENT '过期时间',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导出任务表';

-- =====================================================
-- 【日利宝配置】Daily Profit Configuration
-- =====================================================

-- 日利宝配置表（如不存在）
CREATE TABLE IF NOT EXISTS `prov_ribao_config` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL COMMENT '产品名称',
  `min_amount` DECIMAL(15,2) NOT NULL DEFAULT 100 COMMENT '最低存入',
  `max_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '最高存入(0不限)',
  `daily_rate` DECIMAL(8,6) NOT NULL DEFAULT 0.0003 COMMENT '日利率',
  `annual_rate` DECIMAL(6,4) COMMENT '年化收益率(显示用)',
  `settle_time` VARCHAR(10) NOT NULL DEFAULT '00:00' COMMENT '结算时间',
  `status` TINYINT NOT NULL DEFAULT 1,
  `description` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日利宝配置';

-- =====================================================
-- 【初始化数据】Initial Data
-- =====================================================

-- 插入默认管理员角色
INSERT INTO `prov_admin_roles` (`name`, `code`, `description`, `permissions`, `sort_order`) VALUES
('超级管理员', 'super_admin', '拥有所有权限', '["*"]', 0),
('财务管理员', 'finance_admin', '管理充值提现', '["finance.*"]', 1),
('用户管理员', 'user_admin', '管理用户信息', '["user.*"]', 2),
('运营管理员', 'operation_admin', '管理项目活动', '["project.*","task.*"]', 3),
('客服', 'customer_service', '处理用户问题', '["user.view","finance.view"]', 4);

-- 插入默认超级管理员账号 (密码: admin123)
INSERT INTO `prov_admins` (`username`, `password`, `real_name`, `role_id`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系统管理员', 1, 1);

-- 插入默认系统配置
INSERT INTO `prov_system_configs` (`group`, `key`, `value`, `type`, `description`, `is_public`) VALUES
('site', 'name', 'PROVIDENCE', 'string', '网站名称', 1),
('site', 'logo', '/assets/logo.png', 'string', '网站Logo', 1),
('site', 'customer_service', '', 'string', '客服联系方式', 1),
('finance', 'min_recharge', '100', 'number', '最低充值金额', 0),
('finance', 'min_withdraw', '100', 'number', '最低提现金额', 0),
('finance', 'withdraw_fee_rate', '0.01', 'number', '提现手续费率', 0),
('finance', 'daily_withdraw_limit', '50000', 'number', '每日提现限额', 0),
('sms', 'enabled', 'false', 'boolean', '是否启用短信', 0),
('security', 'login_fail_limit', '5', 'number', '登录失败次数限制', 0),
('security', 'login_lock_minutes', '30', 'number', '登录锁定时间(分钟)', 0);

-- 插入默认任务
INSERT INTO `prov_tasks` (`name`, `code`, `type`, `description`, `reward_type`, `reward_amount`, `target_value`, `target_unit`, `sort_order`) VALUES
('每日签到', 'daily_checkin', 'daily', '每日签到可获得积分奖励', 'points', 10, 1, '次', 1),
('邀请好友', 'invite_friend', 'invite', '邀请好友注册并实名认证', 'balance', 10, 1, '人', 2),
('首次充值', 'first_recharge', 'once', '首次充值满100元', 'points', 100, 100, '元', 3),
('首次投资', 'first_invest', 'once', '首次成功投资项目', 'points', 200, 1, '次', 4),
('累计投资', 'total_invest_1w', 'invest', '累计投资达1万元', 'points', 500, 10000, '元', 5);

-- 插入默认邀请奖励规则
INSERT INTO `prov_invite_reward_rules` (`name`, `type`, `level`, `reward_type`, `reward_value`, `min_amount`, `status`) VALUES
('一级邀请注册奖励', 'register', 1, 'fixed', 10, 0, 1),
('一级邀请充值返佣', 'recharge', 1, 'percent', 0.02, 100, 1),
('二级邀请充值返佣', 'recharge', 2, 'percent', 0.01, 100, 1),
('一级邀请投资返佣', 'invest', 1, 'percent', 0.03, 1000, 1),
('二级邀请投资返佣', 'invest', 2, 'percent', 0.01, 1000, 1);

-- 插入默认日利宝配置
INSERT INTO `prov_ribao_config` (`name`, `min_amount`, `max_amount`, `daily_rate`, `annual_rate`, `settle_time`, `status`, `description`) VALUES
('日利宝', 100, 0, 0.000300, 10.95, '00:00', 1, '日利宝产品，每日计息，随存随取');

SELECT '✅ 后台管理增强模块数据库创建完成！' as result;
