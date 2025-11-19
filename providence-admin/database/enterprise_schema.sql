-- =====================================================
-- PROVIDENCE 企业级数据库架构 v1.0
-- 引擎: InnoDB | 字符集: utf8mb4_unicode_ci | 时区: UTC
-- =====================================================

DROP DATABASE IF EXISTS providence_new;
CREATE DATABASE providence_new DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE providence_new;

SET sql_mode = 'STRICT_ALL_TABLES';

-- 用户表
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  uid CHAR(8) NOT NULL UNIQUE COMMENT '随机8位邀请码',
  username VARCHAR(50) NOT NULL UNIQUE,
  phone VARCHAR(24) UNIQUE,
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255) NOT NULL,
  trade_password VARCHAR(255) COMMENT '交易密码',
  realname_status TINYINT NOT NULL DEFAULT 0 COMMENT '0待审 1通过 2拒绝',
  vip_level TINYINT NOT NULL DEFAULT 0,
  total_invest DECIMAL(18,2) NOT NULL DEFAULT 0,
  is_internal TINYINT NOT NULL DEFAULT 0,
  status TINYINT NOT NULL DEFAULT 1,
  parent_id BIGINT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_uid (uid),
  INDEX idx_users_parent (parent_id),
  INDEX idx_users_vip (vip_level)
) ENGINE=InnoDB;

-- 币种钱包
CREATE TABLE wallets (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  currency ENUM('CNY','USDT') NOT NULL,
  balance DECIMAL(24,8) NOT NULL DEFAULT 0,
  frozen DECIMAL(24,8) NOT NULL DEFAULT 0,
  total_income DECIMAL(24,8) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY ux_user_currency (user_id, currency),
  INDEX idx_wallets_user (user_id)
) ENGINE=InnoDB;

-- 钱包流水
CREATE TABLE wallet_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  wallet_id BIGINT NOT NULL,
  currency ENUM('CNY','USDT') NOT NULL,
  change_amount DECIMAL(24,8) NOT NULL,
  balance_after DECIMAL(24,8) NOT NULL,
  biz_type ENUM('RECHARGE','WITHDRAW','SUBSCRIBE','UNFREEZE','INCOME','REWARD') NOT NULL,
  ref_id BIGINT,
  meta JSON,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_wallet_logs_user_time (user_id, created_at),
  INDEX idx_wallet_logs_type (biz_type)
) ENGINE=InnoDB;

-- 投资项目
CREATE TABLE invest_projects (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  project_code VARCHAR(32) NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL,
  category ENUM('IPO','BOND','FUND','FIXED','INVEST') NOT NULL,
  currency ENUM('CNY','USDT') NOT NULL,
  cycle_days INT NOT NULL,
  base_rate DECIMAL(6,3) NOT NULL,
  min_amount DECIMAL(24,8) NOT NULL,
  max_amount DECIMAL(24,8) NOT NULL,
  total_quota DECIMAL(24,8) NOT NULL DEFAULT 0,
  schedule DECIMAL(9,4) NOT NULL DEFAULT 0,
  manager_id BIGINT NOT NULL,
  status ENUM('DRAFT','PENDING','ONLINE','CLOSED') NOT NULL DEFAULT 'DRAFT',
  version INT NOT NULL DEFAULT 1,
  script_id BIGINT,
  published_at DATETIME,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_projects_status_currency (status, currency),
  INDEX idx_projects_category (category)
) ENGINE=InnoDB;

-- 投资订单
CREATE TABLE invest_orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_no VARCHAR(32) NOT NULL UNIQUE,
  user_id BIGINT NOT NULL,
  project_id BIGINT NOT NULL,
  currency ENUM('CNY','USDT') NOT NULL,
  amount DECIMAL(24,8) NOT NULL,
  vip_level TINYINT NOT NULL,
  base_rate DECIMAL(6,3) NOT NULL,
  vip_extra_rate DECIMAL(6,3) NOT NULL,
  cycle_days INT NOT NULL,
  expected_profit DECIMAL(24,8) NOT NULL,
  earned_amount DECIMAL(24,8) NOT NULL DEFAULT 0,
  status ENUM('PENDING','RUNNING','FINISHED','REFUND','REJECT') NOT NULL DEFAULT 'PENDING',
  start_at DATETIME,
  end_at DATETIME,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_user_status (user_id, status),
  INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- 统一审核中心
CREATE TABLE review_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  type ENUM('RealName','Recharge','Withdraw','ProjectPublish','RiskFlag','ChangeWithdrawAddr') NOT NULL,
  user_id BIGINT,
  amount DECIMAL(24,8),
  currency ENUM('CNY','USDT'),
  payload JSON,
  status ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  remark VARCHAR(255),
  reviewed_by BIGINT,
  reviewed_at DATETIME,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_review_type_status (type, status)
) ENGINE=InnoDB;

-- VIP规则
CREATE TABLE vip_level_rules (
  level TINYINT PRIMARY KEY,
  vip_name VARCHAR(50) NOT NULL,
  min_total_invest DECIMAL(24,8) NOT NULL,
  vip_extra_rate DECIMAL(6,3) NOT NULL,
  level1_percent DECIMAL(5,2) DEFAULT 0,
  level2_percent DECIMAL(5,2) DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 审计日志
CREATE TABLE audit_log (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  actor_type ENUM('ADMIN','USER','SYSTEM') NOT NULL,
  actor_id BIGINT,
  action VARCHAR(64) NOT NULL,
  target_table VARCHAR(64),
  target_id BIGINT,
  delta JSON,
  ip VARBINARY(16),
  user_agent VARCHAR(255),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_action_time (action, created_at)
) ENGINE=InnoDB;

-- 幂等性
CREATE TABLE idempotency_keys (
  idempotency_key VARCHAR(64) PRIMARY KEY,
  user_id BIGINT NOT NULL,
  api_endpoint VARCHAR(100) NOT NULL,
  response JSON,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- nonce防重放
CREATE TABLE api_nonces (
  nonce VARCHAR(64) PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_nonce_time (created_at)
) ENGINE=InnoDB;

SELECT '✅ 企业级核心表创建完成！' as result;
