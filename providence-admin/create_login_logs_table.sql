-- 创建用户登录历史表
CREATE TABLE IF NOT EXISTS `user_login_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `login_ip` varchar(50) NOT NULL COMMENT '登录IP',
  `login_location` varchar(100) DEFAULT NULL COMMENT 'IP归属地',
  `device_type` varchar(50) DEFAULT NULL COMMENT '设备类型',
  `device_info` text COMMENT '设备详细信息',
  `user_agent` text COMMENT 'UserAgent',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `status` tinyint(1) DEFAULT 1 COMMENT '登录状态 1成功 0失败',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户登录历史记录表';

