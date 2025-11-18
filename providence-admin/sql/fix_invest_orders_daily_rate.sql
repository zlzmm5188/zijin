-- ============================================
-- 修复 invest_orders 表：添加 final_daily_rate 字段
-- ============================================

-- 检查字段是否已存在
SELECT COUNT(*) as field_exists
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'invest_orders'
  AND column_name = 'final_daily_rate';

-- 如果不存在，则添加字段
ALTER TABLE invest_orders 
ADD COLUMN IF NOT EXISTS final_daily_rate DECIMAL(10,8) DEFAULT 0.00000000 
COMMENT '每日收益率（用于每日收益发放计算）' 
AFTER final_rate;

-- 更新现有订单的 final_daily_rate（如果为0或NULL）
-- 公式：final_daily_rate = (final_rate / 100) / cycle_days
UPDATE invest_orders 
SET final_daily_rate = CASE 
    WHEN cycle_days > 0 THEN (final_rate / 100) / cycle_days
    ELSE 0
END
WHERE (final_daily_rate IS NULL OR final_daily_rate = 0)
  AND cycle_days > 0
  AND final_rate > 0;

-- ============================================
-- 说明：
-- final_daily_rate 用于 daily-earnings.php 计算每日收益
-- 公式：每日收益 = invest_amount * final_daily_rate
-- ============================================

