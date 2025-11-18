-- Providence AI 聊天系统数据库表
-- 创建时间: 2025-11-17
-- 版本: 1.0

-- 表1: AI聊天消息记录表
CREATE TABLE IF NOT EXISTS `pv_ai_chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `conversation_id` varchar(64) NOT NULL COMMENT '会话ID',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID（0=未登录）',
  `message_type` enum('user','ai') NOT NULL COMMENT '消息类型：user=用户，ai=AI',
  `message_content` text NOT NULL COMMENT '消息内容',
  `user_intent` varchar(100) DEFAULT NULL COMMENT '用户意图（查询余额/查看投资等）',
  `ai_confidence` decimal(5,2) DEFAULT NULL COMMENT 'AI置信度（0-100）',
  `context_data` text DEFAULT NULL COMMENT '上下文数据（JSON格式）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  PRIMARY KEY (`id`),
  KEY `idx_conversation` (`conversation_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI聊天消息记录表';

-- 表2: AI对话会话表
CREATE TABLE IF NOT EXISTS `pv_ai_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '会话ID',
  `conversation_id` varchar(64) NOT NULL COMMENT '会话唯一标识',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID（0=未登录）',
  `session_mode` enum('normal','password_reset') DEFAULT 'normal' COMMENT '会话模式',
  `message_count` int(11) NOT NULL DEFAULT 0 COMMENT '消息数量',
  `last_message_at` timestamp NULL DEFAULT NULL COMMENT '最后消息时间',
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '会话开始时间',
  `ended_at` timestamp NULL DEFAULT NULL COMMENT '会话结束时间',
  `status` enum('active','ended') DEFAULT 'active' COMMENT '会话状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conversation` (`conversation_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI对话会话表';

-- 表3: AI知识库表
CREATE TABLE IF NOT EXISTS `pv_ai_knowledge_base` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '知识ID',
  `category` varchar(50) NOT NULL COMMENT '分类（账户/投资/VIP/提现等）',
  `keywords` text NOT NULL COMMENT '关键词（JSON数组）',
  `question_pattern` text NOT NULL COMMENT '问题模式（正则）',
  `answer_template` text NOT NULL COMMENT '回答模板',
  `requires_auth` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要登录',
  `requires_data` varchar(100) DEFAULT NULL COMMENT '需要的数据（balance/vip_level等）',
  `priority` int(11) NOT NULL DEFAULT 0 COMMENT '优先级（越大越优先）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1=启用，0=禁用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI知识库表';

-- 表4: AI用户反馈表
CREATE TABLE IF NOT EXISTS `pv_ai_user_feedback` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) UNSIGNED NOT NULL COMMENT '消息ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `feedback_type` enum('helpful','not_helpful','wrong') NOT NULL COMMENT '反馈类型',
  `feedback_note` text DEFAULT NULL COMMENT '反馈备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_message` (`message_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI用户反馈表';

-- 初始化知识库数据
INSERT INTO `pv_ai_knowledge_base` (`category`, `keywords`, `question_pattern`, `answer_template`, `requires_auth`, `requires_data`, `priority`, `status`) VALUES
('balance', '["余额","钱","多少钱","balance","账户"]', '余额|多少钱|balance|账户.*钱', '您好！您当前的账户余额如下：\n\n💰 CNY余额：¥{{balance_cny}}\n💵 USDT余额：{{balance_usdt}} USDT\n\n如需充值或提现，请点击下方按钮。', 1, 'balance_cny,balance_usdt', 90, 1),
('investment', '["投资","项目","收益","profit","理财"]', '投资|项目|收益|profit|我的.*投资', '您好！您的投资情况如下：\n\n📊 总投资金额：¥{{total_invest}}\n💎 进行中项目：{{active_count}}个\n💰 累计收益：¥{{total_profit}}\n\n详情请查看【我的投资】页面。', 1, 'total_invest,active_count,total_profit', 85, 1),
('vip', '["VIP","等级","level","会员","升级"]', 'VIP|等级|level|会员|升级', '您好！您当前的VIP信息：\n\n👑 VIP等级：{{vip_level}}\n⭐ 额外加息：{{vip_rate}}%\n🎁 专属权益：{{vip_benefits}}\n\n了解如何升级VIP，请查看【VIP中心】。', 1, 'vip_level,vip_rate,vip_benefits', 80, 1),
('recharge', '["充值","recharge","存钱","入金"]', '充值|recharge|存钱|入金|怎么.*充', '充值非常简单！支持以下方式：\n\n💳 银行卡转账\n🪙 USDT充值\n💰 支付宝（部分渠道）\n\n请点击【去充值】按钮，选择充值方式完成操作。', 0, NULL, 70, 1),
('withdraw', '["提现","withdraw","取钱","出金"]', '提现|withdraw|取钱|出金|怎么.*提', '提现规则说明：\n\n⏰ 到账时间：1-24小时\n💵 手续费：{{withdraw_fee}}%\n🔒 最低提现：¥{{min_withdraw}}\n⚠️ 需完成KYC实名认证\n\n点击【去提现】开始操作。', 0, 'withdraw_fee,min_withdraw', 75, 1),
('ribao', '["日利宝","ribao","理财","定期"]', '日利宝|ribao|理财产品', '日利宝是稳健的理财产品！\n\n💰 当前余额：¥{{ribao_balance}}\n📈 昨日收益：¥{{ribao_yesterday_profit}}\n💎 累计收益：¥{{ribao_total_profit}}\n\n支持随时转入转出，详情请访问【日利宝】页面。', 1, 'ribao_balance,ribao_yesterday_profit,ribao_total_profit', 80, 1),
('team', '["团队","邀请","推广","分享"]', '团队|邀请|推广|分享|我的.*团队', '您的团队数据：\n\n👥 团队人数：{{team_count}}人\n💰 团队业绩：¥{{team_performance}}\n🎁 推荐奖励：¥{{referral_rewards}}\n\n查看详情请访问【团队】页面。', 1, 'team_count,team_performance,referral_rewards', 75, 1),
('kyc', '["实名","认证","KYC","身份验证"]', '实名|认证|KYC|身份.*验证', '实名认证（KYC）流程：\n\n📋 步骤1：填写真实姓名和身份证号\n📸 步骤2：上传身份证正反面照片\n🤳 步骤3：人脸识别验证\n\n完成认证后可享受完整服务功能。', 0, NULL, 65, 1),
('password_reset', '["忘记密码","找回密码","重置密码","密码"]', '忘记.*密码|找回.*密码|重置.*密码|改.*密码', '密码找回流程：\n\n1️⃣ 提供您的账号\n2️⃣ 通过人脸识别验证身份\n3️⃣ 设置新密码\n\n⚠️ 需要先完成KYC实名认证才能使用此服务。', 0, NULL, 85, 1),
('greeting', '["你好","hi","hello","在吗"]', '^(你好|hi|hello|在吗|嗨)', '您好！我是 **Providence AI** 智能顾问 🤖\n\n我可以帮您：\n💰 查询账户余额\n📊 查看投资收益\n👑 了解VIP权益\n👥 查询团队数据\n\n请问有什么可以帮您？', 0, NULL, 100, 1);
