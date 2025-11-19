-- ============================================
-- 同步 users 表和 wallets 表余额
-- ============================================

-- 1. 同步 CNY 余额
UPDATE users u
INNER JOIN wallets w ON u.id = w.user_id AND w.currency = 'CNY'
SET u.balance_cny = w.balance
WHERE ABS(u.balance_cny - w.balance) > 0.0001;

-- 2. 同步 USDT 余额
UPDATE users u
INNER JOIN wallets w ON u.id = w.user_id AND w.currency = 'USDT'
SET u.balance_usdt = w.balance
WHERE ABS(u.balance_usdt - w.balance) > 0.0001;

-- 3. 检查同步结果
SELECT 
    u.id,
    u.username,
    u.balance_cny as user_cny,
    w_cny.balance as wallet_cny,
    ABS(u.balance_cny - COALESCE(w_cny.balance, 0)) as cny_diff,
    u.balance_usdt as user_usdt,
    w_usdt.balance as wallet_usdt,
    ABS(u.balance_usdt - COALESCE(w_usdt.balance, 0)) as usdt_diff
FROM users u
LEFT JOIN wallets w_cny ON u.id = w_cny.user_id AND w_cny.currency = 'CNY'
LEFT JOIN wallets w_usdt ON u.id = w_usdt.user_id AND w_usdt.currency = 'USDT'
WHERE ABS(u.balance_cny - COALESCE(w_cny.balance, 0)) > 0.0001
   OR ABS(u.balance_usdt - COALESCE(w_usdt.balance, 0)) > 0.0001
LIMIT 20;

-- ============================================
-- 说明：
-- 此脚本将 wallets 表的余额同步到 users 表
-- 如果 wallets 表是主数据源，执行此脚本
-- ============================================

