<?php
/**
 * 添加公司介绍、产品分析、通知功能
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

$db = Database::getInstance();

// 公司介绍
$companyIntro = [
    'category' => 'company',
    'question' => '公司介绍',
    'keywords' => json_encode(['公司', '介绍', '关于我们', '公司简介', '公司信息', 'providence']),
    'question_pattern' => '',
    'answer_template' => '<div class="ai-text-message">
        <h3 style="color: #0e2b44; margin-bottom: 16px;">🏢 Providence金融平台</h3>
        <p style="margin-bottom: 12px;">Providence是一家专业的金融科技平台，致力于为用户提供安全、便捷、高效的金融服务。</p>
        
        <div class="ai-feature-list">
            <div class="ai-feature-item">
                <span class="ai-feature-icon">🔒</span>
                <div>
                    <strong>安全保障</strong>
                    <p>多重安全防护，资金安全有保障</p>
                </div>
            </div>
            <div class="ai-feature-item">
                <span class="ai-feature-icon">💰</span>
                <div>
                    <strong>多元产品</strong>
                    <p>丰富的投资产品，满足不同需求</p>
                </div>
            </div>
            <div class="ai-feature-item">
                <span class="ai-feature-icon">⚡</span>
                <div>
                    <strong>高效便捷</strong>
                    <p>7x24小时服务，随时随地理财</p>
                </div>
            </div>
            <div class="ai-feature-item">
                <span class="ai-feature-icon">👑</span>
                <div>
                    <strong>VIP特权</strong>
                    <p>VIP会员专享高收益和专属服务</p>
                </div>
            </div>
        </div>
        
        <div class="ai-info-box" style="margin-top: 16px;">
            <p><strong>我们的使命：</strong>让每个人都能享受到专业的金融服务，实现财富增值。</p>
        </div>
    </div>',
    'requires_data' => '',
    'requires_auth' => 0,
    'priority' => 50,
    'status' => 1
];

// 产品分析
$productAnalysis = [
    'category' => 'product',
    'question' => '产品分析',
    'keywords' => json_encode(['产品', '分析', '投资产品', '理财产品', '项目', '收益', '产品介绍']),
    'question_pattern' => '',
    'answer_template' => '<div class="ai-text-message">
        <h3 style="color: #0e2b44; margin-bottom: 16px;">📊 产品分析</h3>
        <p style="margin-bottom: 16px;">我们提供多种投资产品，满足不同风险偏好和收益需求：</p>
        
        <div class="ai-highlight-box" style="margin-bottom: 16px;">
            <h4 style="margin-top: 0;">💎 固定收益产品</h4>
            <ul class="ai-step-list">
                <li><strong>特点：</strong>收益稳定，风险较低</li>
                <li><strong>适合：</strong>稳健型投资者</li>
                <li><strong>期限：</strong>灵活选择，1-12个月</li>
            </ul>
        </div>
        
        <div class="ai-highlight-box" style="margin-bottom: 16px;">
            <h4 style="margin-top: 0;">📈 日利宝产品</h4>
            <ul class="ai-step-list">
                <li><strong>特点：</strong>每日计息，灵活存取</li>
                <li><strong>适合：</strong>需要资金流动性的用户</li>
                <li><strong>优势：</strong>随存随取，收益可观</li>
            </ul>
        </div>
        
        <div class="ai-highlight-box">
            <h4 style="margin-top: 0;">👑 VIP专属产品</h4>
            <ul class="ai-step-list">
                <li><strong>特点：</strong>VIP会员专享，收益更高</li>
                <li><strong>适合：</strong>VIP会员用户</li>
                <li><strong>优势：</strong>额外加息，专属服务</li>
            </ul>
        </div>
        
        <div class="ai-info-box" style="margin-top: 16px;">
            <p>💡 <strong>投资建议：</strong>根据您的风险承受能力和资金需求，选择合适的投资产品。建议分散投资，降低风险。</p>
        </div>
    </div>',
    'requires_data' => '',
    'requires_auth' => 0,
    'priority' => 50,
    'status' => 1
];

// 通知公告
$notice = [
    'category' => 'notice',
    'question' => '通知公告',
    'keywords' => json_encode(['通知', '公告', '消息', '系统通知', '最新消息', '活动']),
    'question_pattern' => '',
    'answer_template' => '<div class="ai-text-message">
        <h3 style="color: #0e2b44; margin-bottom: 16px;">📢 通知公告</h3>
        <p style="margin-bottom: 16px;">以下是平台最新通知：</p>
        
        <div class="ai-warning-box" style="margin-bottom: 12px;">
            <strong>🔔 系统维护通知</strong>
            <p>平台将于每周日凌晨2:00-4:00进行系统维护，期间可能影响部分功能使用，请提前做好准备。</p>
        </div>
        
        <div class="ai-info-box" style="margin-bottom: 12px;">
            <strong>🎉 新用户福利</strong>
            <p>新用户注册即送体验金，首次投资享受额外收益加成！</p>
        </div>
        
        <div class="ai-highlight-box">
            <strong>⭐ VIP升级活动</strong>
            <p>本月投资满额即可升级VIP，享受更高收益和专属服务！</p>
        </div>
        
        <div class="ai-info-box" style="margin-top: 16px;">
            <p>💡 更多通知请查看【消息中心】或关注平台公告。</p>
        </div>
    </div>',
    'requires_data' => '',
    'requires_auth' => 0,
    'priority' => 50,
    'status' => 1
];

// 检查并插入
$categories = ['company', 'product', 'notice'];
foreach ($categories as $cat) {
    $existing = $db->fetchOne(
        "SELECT id FROM pv_ai_knowledge_base WHERE category = ? AND status = 1",
        [$cat]
    );
    
    if (!$existing) {
        $data = ${$cat === 'company' ? 'companyIntro' : ($cat === 'product' ? 'productAnalysis' : 'notice')};
        $db->query(
            "INSERT INTO pv_ai_knowledge_base 
             (category, question, keywords, question_pattern, answer_template, requires_data, requires_auth, priority, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['category'],
                $data['question'],
                $data['keywords'],
                $data['question_pattern'],
                $data['answer_template'],
                $data['requires_data'],
                $data['requires_auth'],
                $data['priority'],
                $data['status']
            ]
        );
        echo "✅ 已添加: {$data['question']}\n";
    } else {
        echo "ℹ️  已存在: {$cat}\n";
    }
}

echo "\n完成！\n";

