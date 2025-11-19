-- ============================================
-- 防重复提交和防重复返利 - 数据库唯一索引
-- ============================================

-- 1. 投资订单表：order_no 已有 UNIQUE 索引（确认）
-- ALTER TABLE invest_orders ADD UNIQUE KEY uk_order_no (order_no);

-- 2. 返利记录表：添加唯一索引，防止同一订单同一级别重复发放
-- 检查索引是否已存在
SELECT COUNT(*) as index_exists
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'referral_rewards'
  AND index_name = 'uk_user_invest_level';

-- 如果不存在，则添加唯一索引
ALTER TABLE referral_rewards
ADD UNIQUE KEY uk_user_invest_level (user_id, invest_id, level);

-- 3. 充值记录表：order_no 已有 UNIQUE 索引（确认）
-- ALTER TABLE recharge_records ADD UNIQUE KEY uk_order_no (order_no);

-- 4. 提现记录表：order_no 已有 UNIQUE 索引（确认）
-- ALTER TABLE withdraw_records ADD UNIQUE KEY uk_order_no (order_no);

-- 5. 收益记录表：添加唯一索引，防止同一天重复发放收益
ALTER TABLE earnings_records
ADD UNIQUE KEY uk_investment_date (investment_id, earn_date);

-- ============================================
-- 说明：
-- 1. uk_user_invest_level：防止同一订单同一级别重复发放返利
-- 2. uk_investment_date：防止同一天重复发放收益
-- ============================================
