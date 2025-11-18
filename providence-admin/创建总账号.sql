-- ===================================
-- 创建前台总账号（用于生成邀请码）
-- 用途：作为所有注册用户的顶级推荐人
-- 后台可以通过此账号查询名下所有注册用户和充值信息
-- ===================================

USE providence;

-- 检查是否已存在总账号（uid: 88888888）
SET @total_account_uid = '88888888';
SET @total_account_username = 'admin888';
SET @total_account_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; -- 密码: password123

-- 检查是否已存在
SELECT @exists := COUNT(*) FROM users WHERE uid = @total_account_uid;

-- 如果不存在，创建总账号
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
    @total_account_uid AS uid,
    @total_account_username AS username,
    @total_account_password AS password,
    '13888888888' AS phone,
    'admin888@providence.com' AS email,
    6 AS vip_level,  -- 最高VIP等级
    0.00000000 AS total_invest,
    1 AS is_internal,  -- 标记为内部账号
    1 AS status,  -- 正常状态
    NULL AS parent_id,  -- 顶级账号，无推荐人
    2 AS realname_status,  -- 已实名
    NOW() AS created_at,
    NOW() AS updated_at
WHERE @exists = 0;

-- 获取总账号ID
SET @total_account_id = (SELECT id FROM users WHERE uid = @total_account_uid LIMIT 1);

-- 创建CNY钱包（如果不存在）
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

-- 创建USDT钱包（如果不存在）
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
WHERE uid = @total_account_uid;

-- 显示账号信息（用于登录）
SELECT
    '=== 总账号信息 ===' AS info,
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS ''
UNION ALL
SELECT
    '邀请码(UID):' AS info,
    uid AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS ''
FROM users WHERE uid = @total_account_uid
UNION ALL
SELECT
    '用户名:' AS info,
    username AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS ''
FROM users WHERE uid = @total_account_uid
UNION ALL
SELECT
    '密码:' AS info,
    'password123' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS ''
UNION ALL
SELECT
    '手机号:' AS info,
    phone AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS '',
    '' AS ''
FROM users WHERE uid = @total_account_uid;
