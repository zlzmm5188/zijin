-- ============================================
-- 兑换接口数据库操作测试脚本
-- ============================================

-- 1. 检查 wallets 表结构
DESCRIBE wallets;

-- 2. 检查 users 表余额字段
DESCRIBE users;

-- 3. 检查唯一索引（防止重复钱包）
SHOW INDEX FROM wallets WHERE Key_name = 'user_currency';

-- 4. 测试场景：用户ID=1，CNY余额=1000，兑换100 CNY → USDT（汇率7.2）
-- 预期结果：
--   - wallets: CNY余额 1000 → 900
--   - wallets: USDT余额 0 → 13.88888888
--   - users: balance_cny 1000 → 900
--   - users: balance_usdt 0 → 13.88888888

-- 5. 检查兑换前的余额
SELECT
    u.id,
    u.balance_cny,
    u.balance_usdt,
    w_cny.balance as cny_wallet_balance,
    w_usdt.balance as usdt_wallet_balance
FROM users u
LEFT JOIN wallets w_cny ON u.id = w_cny.user_id AND w_cny.currency = 'CNY'
LEFT JOIN wallets w_usdt ON u.id = w_usdt.user_id AND w_usdt.currency = 'USDT'
WHERE u.id = 1;

-- 6. 模拟兑换操作（手动执行，用于验证）
-- 注意：实际兑换应通过API接口执行，这里仅用于测试

-- 扣除CNY余额（原子操作）
-- UPDATE wallets
-- SET balance = balance - 100.00,
--     updated_at = NOW()
-- WHERE user_id = 1
--   AND currency = 'CNY'
--   AND balance >= 100.00;

-- 增加USDT余额（原子操作）
-- UPDATE wallets
-- SET balance = balance + 13.88888888,
--     updated_at = NOW()
-- WHERE user_id = 1
--   AND currency = 'USDT';

-- 同步users表余额
-- UPDATE users
-- SET balance_cny = balance_cny - 100.00,
--     balance_usdt = balance_usdt + 13.88888888,
--     updated_at = NOW()
-- WHERE id = 1;

-- 7. 检查兑换后的余额（验证一致性）
SELECT
    u.id,
    u.balance_cny,
    u.balance_usdt,
    w_cny.balance as cny_wallet_balance,
    w_usdt.balance as usdt_wallet_balance,
    CASE
        WHEN ABS(u.balance_cny - w_cny.balance) < 0.0001 THEN '✅ 一致'
        ELSE '❌ 不一致'
    END as cny_sync_status,
    CASE
        WHEN ABS(u.balance_usdt - w_usdt.balance) < 0.0001 THEN '✅ 一致'
        ELSE '❌ 不一致'
    END as usdt_sync_status
FROM users u
LEFT JOIN wallets w_cny ON u.id = w_cny.user_id AND w_cny.currency = 'CNY'
LEFT JOIN wallets w_usdt ON u.id = w_usdt.user_id AND w_usdt.currency = 'USDT'
WHERE u.id = 1;

-- 8. 检查钱包流水记录
SELECT
    id,
    user_id,
    currency,
    change_amount,
    balance_after,
    biz_type,
    created_at
FROM wallet_logs
WHERE user_id = 1
  AND biz_type = 'CURRENCY_EXCHANGE'
ORDER BY id DESC
LIMIT 10;

-- ============================================
-- 数据库完整性检查
-- ============================================

-- 检查是否有余额不一致的情况
SELECT
    u.id,
    u.username,
    u.balance_cny as user_cny,
    w_cny.balance as wallet_cny,
    ABS(u.balance_cny - w_cny.balance) as cny_diff,
    u.balance_usdt as user_usdt,
    w_usdt.balance as wallet_usdt,
    ABS(u.balance_usdt - w_usdt.balance) as usdt_diff
FROM users u
LEFT JOIN wallets w_cny ON u.id = w_cny.user_id AND w_cny.currency = 'CNY'
LEFT JOIN wallets w_usdt ON u.id = w_usdt.user_id AND w_usdt.currency = 'USDT'
WHERE ABS(u.balance_cny - COALESCE(w_cny.balance, 0)) > 0.0001
   OR ABS(u.balance_usdt - COALESCE(w_usdt.balance, 0)) > 0.0001;

-- ============================================
-- 说明：
-- 1. 如果发现余额不一致，需要执行同步脚本
-- 2. 兑换接口已使用事务和原子操作，确保数据一致性
-- 3. 建议定期执行余额一致性检查
-- ============================================
