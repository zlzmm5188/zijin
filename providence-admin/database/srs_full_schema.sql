-- =====================================================
-- PROVIDENCE 后台系统 - 完整数据库架构 (SRS v1.0)
-- 遵循：5大中心 + 审计日志 + 风控系统
-- =====================================================

USE providence;

-- =====================================================
-- 【会员中心】Member Center
-- =====================================================

-- 用户登录记录表
DROP TABLE IF EXISTS `prov_user_login_logs`;
CREATE TABLE `prov_user_login_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `login_ip` VARCHAR(50) NOT NULL,
  `ip_location` VARCHAR(200) COMMENT 'IP归属地',
  `device_type` VARCHAR(50) COMMENT '设备类型: mobile/desktop/tablet',
  `device_info` VARCHAR(255) COMMENT '设备信息（User-Agent）',
  `browser` VARCHAR(100) COMMENT '浏览器',
  `os` VARCHAR(100) COMMENT '操作系统',
  `login_time` DATETIME NOT NULL,
  `status` TINYINT DEFAULT 1 COMMENT '1成功 0失败',
  `fail_reason` VARCHAR(255) COMMENT '失败原因',
  INDEX `idx_user` (`user_id`),
  INDEX `idx_ip` (`login_ip`),
  INDEX `idx_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户登录记录';

-- 用户实名认证表
DROP TABLE IF EXISTS `prov_user_kyc`;
CREATE TABLE `prov_user_kyc` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `real_name` VARCHAR(50) NOT NULL,
  `id_card` VARCHAR(20) NOT NULL,
  `id_card_front` VARCHAR(255) COMMENT '身份证正面照片',
  `id_card_back` VARCHAR(255) COMMENT '身份证反面照片',
  `hand_held_photo` VARCHAR(255) COMMENT '手持身份证照片',
  `status` TINYINT DEFAULT 0 COMMENT '0待审核 1已通过 2已拒绝',
  `reject_reason` VARCHAR(500) COMMENT '拒绝原因',
  `reviewed_by` INT UNSIGNED COMMENT '审核人',
  `reviewed_at` DATETIME COMMENT '审核时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实名认证表';

-- =====================================================
-- 【项目中心】Project Center
-- =====================================================

-- 项目类型枚举表
DROP TABLE IF EXISTS `prov_project_types`;
CREATE TABLE `prov_project_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type_code` VARCHAR(20) UNIQUE NOT NULL COMMENT '类型代码: IPO/FUND/BOND/FIXED/INVEST',
  `type_name` VARCHAR(50) NOT NULL COMMENT '类型名称',
  `description` TEXT COMMENT '说明',
  `icon` VARCHAR(255) COMMENT '图标',
  `status` TINYINT DEFAULT 1,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目类型表';

-- 插入项目类型
INSERT INTO `prov_project_types` (`type_code`, `type_name`, `description`) VALUES
('IPO', 'IPO配售', 'IPO新股配售项目'),
('FUND', '基金投资', '私募股权基金'),
('BOND', '债券投资', '固定收益债券'),
('FIXED', '固定收益', '保本保息固定收益'),
('INVEST', '定期定投', '定期定额投资计划');

-- 项目文案库表
DROP TABLE IF EXISTS `prov_project_scripts_library`;
CREATE TABLE `prov_project_scripts_library` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_type` VARCHAR(20) COMMENT '项目类型',
  `title` VARCHAR(200) NOT NULL COMMENT '文案标题',
  `content` TEXT NOT NULL COMMENT '文案内容',
  `tags` VARCHAR(255) COMMENT '标签',
  `status` TINYINT DEFAULT 1,
  `use_count` INT DEFAULT 0 COMMENT '使用次数',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_type` (`project_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目文案库（≥100条）';

-- 重构投资项目表
DROP TABLE IF EXISTS `prov_invest_projects`;
CREATE TABLE `prov_invest_projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_code` VARCHAR(50) UNIQUE NOT NULL COMMENT '项目编号',
  `project_type` VARCHAR(20) NOT NULL COMMENT '项目类型',
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255),
  `description` TEXT,
  `script_id` INT UNSIGNED COMMENT '关联文案ID',
  `cover_image` VARCHAR(255),
  `images` TEXT COMMENT 'JSON',
  `manager_id` INT UNSIGNED,
  
  -- 投资配置
  `min_invest` DECIMAL(15,2) NOT NULL,
  `max_invest` DECIMAL(15,2),
  `base_daily_rate` DECIMAL(5,4) NOT NULL COMMENT '基础日收益率',
  `total_days` INT NOT NULL COMMENT '投资周期（天）',
  
  -- 限制规则
  `vip_limit` TINYINT DEFAULT 0 COMMENT '最低VIP要求',
  `max_buy_count` INT DEFAULT 0 COMMENT '每人最多购买次数（0=无限）',
  `total_quota` DECIMAL(15,2) DEFAULT 0 COMMENT '项目总额度（0=无限）',
  
  `risk_level` TINYINT DEFAULT 1,
  `status` TINYINT DEFAULT 1 COMMENT '1正常 2暂停 3结束 4审核中',
  `sort_order` INT DEFAULT 0,
  `view_count` INT DEFAULT 0,
  `invest_count` INT DEFAULT 0,
  `total_invested` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type` (`project_type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='投资项目表（统一）';

-- 投资订单表
DROP TABLE IF EXISTS `prov_invest_orders`;
CREATE TABLE `prov_invest_orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `project_type` VARCHAR(20) NOT NULL,
  `invest_amount` DECIMAL(15,2) NOT NULL,
  
  -- 收益计算（后端统一计算）
  `base_daily_rate` DECIMAL(5,4) NOT NULL COMMENT '基础日收益率',
  `vip_extra_rate` DECIMAL(5,4) DEFAULT 0 COMMENT 'VIP额外加息',
  `final_daily_rate` DECIMAL(5,4) NOT NULL COMMENT '实际日收益率',
  
  `total_days` INT NOT NULL,
  `earned_amount` DECIMAL(15,2) DEFAULT 0,
  `total_return` DECIMAL(15,2) COMMENT '预期总收益',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_trial` TINYINT DEFAULT 0,
  `status` TINYINT DEFAULT 1 COMMENT '1进行中 2已完成 3已取消',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_project` (`project_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='投资订单表（统一）';

-- =====================================================
-- 【资金中心】Finance Center
-- =====================================================

-- 用户钱包表
DROP TABLE IF EXISTS `prov_wallet_balance`;
CREATE TABLE `prov_wallet_balance` (
  `user_id` INT UNSIGNED PRIMARY KEY,
  `balance` DECIMAL(15,2) DEFAULT 0 COMMENT '可用余额',
  `frozen` DECIMAL(15,2) DEFAULT 0 COMMENT '冻结金额',
  `total_recharge` DECIMAL(15,2) DEFAULT 0 COMMENT '累计充值',
  `total_withdraw` DECIMAL(15,2) DEFAULT 0 COMMENT '累计提现',
  `total_invest` DECIMAL(15,2) DEFAULT 0 COMMENT '累计投资',
  `total_earned` DECIMAL(15,2) DEFAULT 0 COMMENT '累计收益',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户钱包';

-- 钱包流水表
DROP TABLE IF EXISTS `prov_wallet_logs`;
CREATE TABLE `prov_wallet_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(20) NOT NULL COMMENT 'recharge/withdraw/invest/earning/refund/reward',
  `amount` DECIMAL(15,2) NOT NULL COMMENT '金额（带符号）',
  `balance_before` DECIMAL(15,2) NOT NULL,
  `balance_after` DECIMAL(15,2) NOT NULL,
  `ref_type` VARCHAR(20) COMMENT '关联类型',
  `ref_id` BIGINT UNSIGNED COMMENT '关联ID',
  `remark` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='钱包流水';

-- 充值记录表（增强图片）
DROP TABLE IF EXISTS `prov_recharge_records`;
CREATE TABLE `prov_recharge_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_method` VARCHAR(50) COMMENT 'bank/usdt/alipay/wechat',
  `payment_info` TEXT COMMENT 'JSON',
  
  -- 凭证图片（支持多张）
  `certificate_images` TEXT COMMENT '凭证图片JSON数组',
  
  `status` TINYINT DEFAULT 0 COMMENT '0待审核 1已通过 2已拒绝',
  `remark` VARCHAR(500),
  `reviewed_by` INT UNSIGNED,
  `reviewed_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='充值记录';

-- 提现记录表（增强图片）
DROP TABLE IF EXISTS `prov_withdraw_records`;
CREATE TABLE `prov_withdraw_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(32) UNIQUE NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `fee` DECIMAL(15,2) DEFAULT 0,
  `actual_amount` DECIMAL(15,2) NOT NULL,
  `withdraw_method` VARCHAR(50),
  `bank_info` TEXT COMMENT 'JSON',
  
  -- 审核凭证
  `payment_proof` VARCHAR(255) COMMENT '打款凭证图片',
  
  `status` TINYINT DEFAULT 0 COMMENT '0待审核 1已通过 2已拒绝 3已打款',
  `remark` VARCHAR(500),
  `reviewed_by` INT UNSIGNED,
  `reviewed_at` DATETIME,
  `paid_by` INT UNSIGNED,
  `paid_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现记录';

-- =====================================================
-- 【推广中心】Promotion Center
-- =====================================================

-- 团队奖励规则表（可动态调整）
DROP TABLE IF EXISTS `prov_team_reward_rules`;
CREATE TABLE `prov_team_reward_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level` INT NOT NULL COMMENT '等级',
  `team_members` INT NOT NULL COMMENT '团队人数要求',
  `total_invest` DECIMAL(15,2) NOT NULL COMMENT '累计投资额要求',
  `reward_amount` DECIMAL(15,2) NOT NULL COMMENT '奖励金额',
  `status` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='团队奖励规则（可动态调整）';

-- VIP加息规则表（可动态调整）
DROP TABLE IF EXISTS `prov_vip_interest_rules`;
CREATE TABLE `prov_vip_interest_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vip_level` TINYINT NOT NULL UNIQUE,
  `vip_name` VARCHAR(50) NOT NULL,
  `min_invest` DECIMAL(15,2) DEFAULT 0 COMMENT '累计投资要求',
  
  -- 加息规则
  `extra_rate` DECIMAL(5,4) DEFAULT 0 COMMENT '额外加息率（如+0.003表示+0.3%）',
  `extra_rate_display` VARCHAR(20) COMMENT '显示文本（如+0.3%）',
  
  -- 推荐奖励
  `level1_percent` DECIMAL(5,2) DEFAULT 0 COMMENT '一级推荐奖励%',
  `level2_percent` DECIMAL(5,2) DEFAULT 0 COMMENT '二级推荐奖励%',
  
  -- 其他权益
  `daily_withdraw_limit` DECIMAL(15,2),
  `withdraw_fee_rate` DECIMAL(5,4),
  `invite_points` INT DEFAULT 0,
  
  `status` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='VIP加息规则表（可动态调整）';

-- =====================================================
-- 【风控中心】Risk Control Center
-- =====================================================

-- IP黑白名单
DROP TABLE IF EXISTS `prov_ip_blacklist`;
CREATE TABLE `prov_ip_blacklist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(50) NOT NULL,
  `type` TINYINT DEFAULT 1 COMMENT '1黑名单 2白名单',
  `reason` VARCHAR(255),
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='IP黑白名单';

-- 设备指纹表
DROP TABLE IF EXISTS `prov_device_fingerprints`;
CREATE TABLE `prov_device_fingerprints` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `fingerprint` VARCHAR(64) NOT NULL COMMENT '设备指纹hash',
  `device_info` TEXT COMMENT 'JSON设备信息',
  `first_seen` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_seen` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `use_count` INT DEFAULT 1,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_fingerprint` (`fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备指纹';

-- 风控告警表
DROP TABLE IF EXISTS `prov_risk_alerts`;
CREATE TABLE `prov_risk_alerts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `alert_type` VARCHAR(50) NOT NULL COMMENT '告警类型',
  `user_id` INT UNSIGNED,
  `level` TINYINT DEFAULT 1 COMMENT '1低 2中 3高 4严重',
  `content` TEXT,
  `ip` VARCHAR(50),
  `status` TINYINT DEFAULT 0 COMMENT '0未处理 1已处理',
  `handled_by` INT UNSIGNED,
  `handled_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='风控告警';

-- =====================================================
-- 【审计日志】Audit Log (核心)
-- =====================================================

DROP TABLE IF EXISTS `prov_audit_log`;
CREATE TABLE `prov_audit_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL COMMENT '模块：member/project/finance/risk/promotion',
  `action` VARCHAR(100) NOT NULL COMMENT '操作',
  `operator_type` VARCHAR(20) NOT NULL COMMENT 'user/admin/system',
  `operator_id` INT UNSIGNED NOT NULL,
  `target_type` VARCHAR(50) COMMENT '目标类型',
  `target_id` BIGINT UNSIGNED COMMENT '目标ID',
  `before_data` TEXT COMMENT '变更前数据JSON',
  `after_data` TEXT COMMENT '变更后数据JSON',
  `ip` VARCHAR(50),
  `user_agent` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_module` (`module`),
  INDEX `idx_operator` (`operator_type`, `operator_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统审计日志（所有关键操作）';

-- 完成
SELECT '✅ SRS架构数据库创建完成！' as result;
