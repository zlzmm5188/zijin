-- 添加字段级缓存（不使用触发器）
USE providence;

ALTER TABLE `invest_projects`
ADD COLUMN `total_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '总收益率缓存（周期）',
ADD COLUMN `sold` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '已募集金额缓存',
ADD COLUMN `remain` DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT '剩余额度缓存',
ADD COLUMN `cache_updated_at` DATETIME COMMENT '缓存更新时间';

-- 初始化现有项目的缓存
UPDATE `invest_projects` SET
  `total_rate` = `base_rate` + COALESCE(`added_rate`, 0) + COALESCE(`gift_rate`, 0),
  `sold` = `total_invested`,
  `remain` = CASE 
    WHEN `total_quota` > 0 THEN (`total_quota` - `total_invested`)
    ELSE 999999999 
  END,
  `cache_updated_at` = NOW();

SELECT '✅ 缓存字段添加完成！' as result;
