-- ===================================
-- Providence 数据库修复脚本 Phase 1
-- 创建时间: 2025-11-12 06:49:20
-- ===================================

USE providence;

-- 1️⃣ 在wallets表添加积分字段
ALTER TABLE wallets 
ADD COLUMN points DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT '积分余额' AFTER ribao_yesterday_profit,
ADD COLUMN points_frozen DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT '冻结积分' AFTER points;

-- 2️⃣ 创建VIP等级规则表
CREATE TABLE IF NOT EXISTS vip_level_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level TINYINT NOT NULL COMMENT 'VIP等级',
    name VARCHAR(50) NOT NULL COMMENT '等级名称',
    min_invest DECIMAL(20,8) NOT NULL DEFAULT 0 COMMENT '最低累计投资额',
    interest_rate DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '加息比例(%)',
    daily_withdraw_limit DECIMAL(20,8) NULL COMMENT '每日提现限额',
    monthly_withdraw_limit DECIMAL(20,8) NULL COMMENT '每月提现限额',
    withdraw_fee_rate DECIMAL(5,3) NOT NULL DEFAULT 0 COMMENT '提现手续费率(%)',
    priority_customer_service TINYINT NOT NULL DEFAULT 0 COMMENT '专属客服(0=否,1=是)',
    exclusive_projects TINYINT NOT NULL DEFAULT 0 COMMENT '专属项目(0=否,1=是)',
    birthday_bonus DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '生日礼金',
    description TEXT COMMENT '等级说明',
    icon VARCHAR(255) COMMENT '等级图标',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_level (level),
    KEY idx_min_invest (min_invest)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='VIP等级规则表';

-- 3️⃣ 插入默认VIP等级数据
INSERT INTO vip_level_rules (level, name, min_invest, interest_rate, withdraw_fee_rate, description) VALUES
(0, 'VIP0', 0, 0.000, 0.500, '注册用户'),
(1, 'VIP1', 10000, 0.100, 0.300, '累计投资1万解锁'),
(2, 'VIP2', 50000, 0.200, 0.200, '累计投资5万解锁'),
(3, 'VIP3', 100000, 0.300, 0.100, '累计投资10万解锁'),
(4, 'VIP4', 500000, 0.500, 0.050, '累计投资50万解锁'),
(5, 'VIP5', 1000000, 0.800, 0.000, '累计投资100万解锁')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    min_invest = VALUES(min_invest),
    interest_rate = VALUES(interest_rate),
    withdraw_fee_rate = VALUES(withdraw_fee_rate),
    description = VALUES(description);

-- 4️⃣ 创建用户积分流水表
CREATE TABLE IF NOT EXISTS user_points_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL COMMENT '用户ID',
    change_amount DECIMAL(20,2) NOT NULL COMMENT '变动积分',
    balance_after DECIMAL(20,2) NOT NULL COMMENT '变动后余额',
    biz_type ENUM('SIGN','EXCHANGE','REWARD','ADMIN','REFUND') NOT NULL COMMENT '业务类型',
    ref_id BIGINT NULL COMMENT '关联ID',
    description VARCHAR(255) COMMENT '说明',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_id (user_id),
    KEY idx_biz_type (biz_type),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户积分流水表';

-- 5️⃣ users表已有uid字段，添加索引即可
-- uid可以作为邀请码使用（8位数字）
ALTER TABLE users 
ADD INDEX idx_uid (uid);

SELECT '✅ 数据库修复完成！' AS result;

-- 验证结果
SHOW TABLES LIKE '%vip%';
SHOW TABLES LIKE '%points%';
DESC wallets;
