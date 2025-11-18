-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- Providence 项目管理升级 SQL
-- 基于旧后台功能优化
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USE providence;

-- 添加缺失字段
ALTER TABLE invest_projects 
ADD COLUMN is_index TINYINT DEFAULT 0 COMMENT '首页显示:0否1是' AFTER status,
ADD COLUMN sort INT DEFAULT 0 COMMENT '排序权重(越大越靠前)' AFTER is_index,
ADD COLUMN payment_type TINYINT DEFAULT 0 COMMENT '支付方式:0不限1仅USDT2仅CNY' AFTER sort,
ADD COLUMN vip_min TINYINT DEFAULT 0 COMMENT '最低VIP等级要求' AFTER payment_type,
ADD COLUMN need_referral TINYINT DEFAULT 0 COMMENT '需要推荐人:0否1是' AFTER vip_min,
ADD COLUMN team_member_required INT DEFAULT 0 COMMENT '团队人数要求' AFTER need_referral,
ADD COLUMN mcount INT DEFAULT 0 COMMENT '购买次数限制:0不限' AFTER team_member_required;

-- 添加索引
ALTER TABLE invest_projects ADD INDEX idx_is_index (is_index);
ALTER TABLE invest_projects ADD INDEX idx_sort (sort);
ALTER TABLE invest_projects ADD INDEX idx_payment_type (payment_type);
ALTER TABLE invest_projects ADD INDEX idx_vip_min (vip_min);

-- 更新现有项目的默认值
UPDATE invest_projects 
SET sort = 100 
WHERE sort = 0;

SELECT '✅ 项目表升级完成' AS status;

-- 显示升级后的表结构
SHOW FULL COLUMNS FROM invest_projects WHERE Field IN ('is_index', 'sort', 'payment_type', 'vip_min', 'need_referral', 'team_member_required', 'mcount');
