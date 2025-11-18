-- 创建人脸审核记录表
CREATE TABLE IF NOT EXISTS `face_review_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `reset_token` varchar(64) NOT NULL COMMENT '重置令牌',
  `face_image_path` varchar(255) NOT NULL COMMENT '人脸照片路径',
  `similarity` decimal(5,4) NOT NULL COMMENT '本地相似度',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT '审核状态',
  `reviewed_by` int(11) DEFAULT NULL COMMENT '审核人ID',
  `reject_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `reviewed_at` datetime DEFAULT NULL COMMENT '审核时间',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reset_token` (`reset_token`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='人脸审核记录表';

-- 创建密码重置令牌表（如果不存在）
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `token` varchar(64) NOT NULL COMMENT '重置令牌',
  `expires_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='密码重置令牌表';

-- 检查 users 表是否有 face_descriptor 字段，如果没有则添加
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'face_descriptor';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TEXT COMMENT '人脸特征向量（JSON格式，512维）' AFTER kyc_status")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
