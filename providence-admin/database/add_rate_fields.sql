-- 添加额外加息字段

USE providence;

-- 项目表添加added和gift字段
ALTER TABLE `prov_invest_projects`
ADD COLUMN `added_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '额外临时加息%' AFTER `base_rate`,
ADD COLUMN `gift_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '活动加息%' AFTER `added_rate`;

-- 订单表也添加（快照）
ALTER TABLE `prov_invest_orders`
ADD COLUMN `added_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '额外临时加息%' AFTER `base_rate`,
ADD COLUMN `gift_rate` DECIMAL(6,3) NOT NULL DEFAULT 0 COMMENT '活动加息%' AFTER `added_rate`;

-- 更新示例数据
UPDATE `prov_invest_projects` SET
  `added_rate` = 0.2,
  `gift_rate` = 0
WHERE id = 1;

SELECT '✅ 加息字段添加完成！' as result;
