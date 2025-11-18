-- 为wallets表添加日利宝相关字段
ALTER TABLE `wallets`
ADD COLUMN `ribao_balance` decimal(24,8) NOT NULL DEFAULT '0.00000000' COMMENT '日利宝余额' AFTER `frozen`,
ADD COLUMN `ribao_total_profit` decimal(24,8) NOT NULL DEFAULT '0.00000000' COMMENT '日利宝累计收益' AFTER `ribao_balance`,
ADD COLUMN `ribao_yesterday_profit` decimal(24,8) NOT NULL DEFAULT '0.00000000' COMMENT '日利宝昨日收益' AFTER `ribao_total_profit`;
