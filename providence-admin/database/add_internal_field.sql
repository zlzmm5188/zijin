-- 添加内部人员标记字段
USE providence;

ALTER TABLE `prov_users` 
ADD COLUMN `is_internal` TINYINT DEFAULT 0 COMMENT '是否内部人员:0否 1是' AFTER `status`;

ALTER TABLE `prov_recharge_records`
ADD COLUMN `is_internal` TINYINT DEFAULT 0 COMMENT '是否内部人员充值' AFTER `status`;

ALTER TABLE `prov_withdraw_records`
ADD COLUMN `is_internal` TINYINT DEFAULT 0 COMMENT '是否内部人员提现' AFTER `status`;

SELECT 'SQL更新成功！' as result;
