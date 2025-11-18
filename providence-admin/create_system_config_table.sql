-- 创建系统配置表
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` text COMMENT '配置值（JSON格式）',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- 插入日利宝默认配置
INSERT INTO `system_config` (`config_key`, `config_value`, `description`, `created_at`, `updated_at`)
VALUES (
  'ribao_settings',
  '{"daily_rate":"0.00100000","min_amount":"100.00000000","max_amount":"1000000.00000000","is_enabled":true,"settlement_time":"00:00:00"}',
  '日利宝配置',
  NOW(),
  NOW()
) ON DUPLICATE KEY UPDATE `updated_at` = NOW();
