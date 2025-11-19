-- 积分兑换日志表
CREATE TABLE IF NOT EXISTS points_exchange_logs (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '记录ID',
    user_id INT NOT NULL COMMENT '用户ID',
    points_used INT NOT NULL COMMENT '使用积分',
    amount_received DECIMAL(20, 8) NOT NULL COMMENT '获得金额',
    exchange_rate DECIMAL(10, 4) NOT NULL COMMENT '兑换汇率',
    before_points DECIMAL(20, 2) DEFAULT 0.00 COMMENT '兑换前积分',
    after_points DECIMAL(20, 2) DEFAULT 0.00 COMMENT '兑换后积分',
    before_balance DECIMAL(20, 8) DEFAULT 0.00000000 COMMENT '兑换前余额',
    after_balance DECIMAL(20, 8) DEFAULT 0.00000000 COMMENT '兑换后余额',
    status TINYINT DEFAULT 1 COMMENT '状态：0=失败，1=成功',
    remark VARCHAR(255) DEFAULT NULL COMMENT '备注',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分兑换日志表';
