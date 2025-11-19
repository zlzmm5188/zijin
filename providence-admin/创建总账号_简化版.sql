-- ===================================
-- 创建前台总账号（用于生成邀请码）
-- 简化版：直接插入，避免字符集问题
-- ===================================

USE providence;

-- 检查并创建总账号
INSERT INTO users (
    uid,
    username,
    password,
    phone,
    email,
    vip_level,
    total_invest,
    is_internal,
    status,
    parent_id,
    realname_status,
    created_at,
    updated_at
)
SELECT
    '88888888' AS uid,
    'admin888' AS username,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password,
    '13888888888' AS phone,
    'admin888@providence.com' AS email,
    6 AS vip_level,
    0.00000000 AS total_invest,
    1 AS is_internal,
    1 AS status,
    NULL AS parent_id,
    2 AS realname_status,
    NOW() AS created_at,
    NOW() AS updated_at
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE uid = '88888888'
);

-- 获取总账号ID
SET @total_account_id = (SELECT id FROM users WHERE uid = '88888888' LIMIT 1);

-- 创建CNY钱包
INSERT INTO wallets (
    user_id,
    currency,
    balance,
    frozen,
    created_at,
    updated_at
)
SELECT
    @total_account_id AS user_id,
    'CNY' AS currency,
    0.00000000 AS balance,
    0.00000000 AS frozen,
    NOW() AS created_at,
    NOW() AS updated_at
WHERE NOT EXISTS (
    SELECT 1 FROM wallets WHERE user_id = @total_account_id AND currency = 'CNY'
);

-- 创建USDT钱包
INSERT INTO wallets (
    user_id,
    currency,
    balance,
    frozen,
    created_at,
    updated_at
)
SELECT
    @total_account_id AS user_id,
    'USDT' AS currency,
    0.00000000 AS balance,
    0.00000000 AS frozen,
    NOW() AS created_at,
    NOW() AS updated_at
WHERE NOT EXISTS (
    SELECT 1 FROM wallets WHERE user_id = @total_account_id AND currency = 'USDT'
);

-- 显示总账号信息
SELECT
    id,
    uid AS '邀请码',
    username AS '用户名',
    phone AS '手机号',
    email AS '邮箱',
    vip_level AS 'VIP等级',
    status AS '状态',
    parent_id AS '推荐人ID',
    created_at AS '创建时间'
FROM users
WHERE uid = '88888888';
