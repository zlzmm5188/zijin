CREATE TABLE IF NOT EXISTS ribao_logs (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '记录ID',
    user_id INT NOT NULL COMMENT '用户ID',
    type ENUM('in', 'out') NOT NULL COMMENT '类型：in=转入，out=转出',
    amount DECIMAL(20, 8) NOT NULL COMMENT '金额',
    before_balance DECIMAL(20, 8) DEFAULT 0 COMMENT '操作前余额',
    after_balance DECIMAL(20, 8) DEFAULT 0 COMMENT '操作后余额',
    status TINYINT DEFAULT 1 COMMENT '状态：0=处理中，1=成功，-1=失败',
    remark VARCHAR(255) DEFAULT NULL COMMENT '备注',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日利宝转入转出记录表';
