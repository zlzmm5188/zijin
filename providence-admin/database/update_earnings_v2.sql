-- 收益计算v2.0数据库更新

USE providence;

-- 更新项目表（添加新字段）
ALTER TABLE `prov_invest_projects`
ADD COLUMN `cycle_days` INT NOT NULL DEFAULT 30 COMMENT '项目周期天数',
ADD COLUMN `base_rate` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT '基础收益率%（整个周期）';

-- 更新订单表（添加新字段）
ALTER TABLE `prov_invest_orders`
ADD COLUMN `cycle_days` INT NOT NULL DEFAULT 30 COMMENT '周期天数',
ADD COLUMN `base_rate` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT '基础收益率%',
ADD COLUMN `final_rate` DECIMAL(5,2) DEFAULT 0 COMMENT '最终收益率%';

-- 修改vip_extra_rate字段说明
ALTER TABLE `prov_invest_orders` 
MODIFY COLUMN `vip_extra_rate` DECIMAL(5,2) DEFAULT 0 COMMENT 'VIP额外收益率%（周期）';

-- 更新VIP规则表的extra_rate为周期收益（假设30天周期）
UPDATE `prov_vip_interest_rules` SET
  `extra_rate` = CASE vip_level
    WHEN 0 THEN 0.00
    WHEN 1 THEN 0.30
    WHEN 2 THEN 0.60
    WHEN 3 THEN 0.72
    WHEN 4 THEN 0.90
    WHEN 5 THEN 0.96
    WHEN 6 THEN 1.08
    WHEN 7 THEN 1.38
    WHEN 8 THEN 1.50
  END,
  `extra_rate_display` = CASE vip_level
    WHEN 0 THEN '+0%'
    WHEN 1 THEN '+0.3%'
    WHEN 2 THEN '+0.6%'
    WHEN 3 THEN '+0.72%'
    WHEN 4 THEN '+0.9%'
    WHEN 5 THEN '+0.96%'
    WHEN 6 THEN '+1.08%'
    WHEN 7 THEN '+1.38%'
    WHEN 8 THEN '+1.5%'
  END;

-- 更新示例项目数据
UPDATE `prov_invest_projects` SET
  `cycle_days` = 30,
  `base_rate` = 4.50
WHERE id = 1;

UPDATE `prov_invest_projects` SET
  `cycle_days` = 60,
  `base_rate` = 15.00
WHERE id = 2;

UPDATE `prov_invest_projects` SET
  `cycle_days` = 90,
  `base_rate` = 18.00
WHERE id = 3;

SELECT '✅ 收益计算v2.0数据库更新完成！' as result;
