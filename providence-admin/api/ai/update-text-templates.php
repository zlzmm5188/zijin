<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
$db = Database::getInstance();

$templates = [
    'greeting' => '<div class="ai-text-message"><p style="font-size:18px;font-weight:700;color:#0e2b44;margin-bottom:16px;">您好！我是 <strong>Providence AI</strong> 智能顾问 🤖</p><p style="margin-bottom:16px;color:#0e2b44;">我可以帮您：</p><div class="ai-feature-list"><div class="ai-feature-item"><span class="ai-feature-icon">💰</span><span>查询账户余额</span></div><div class="ai-feature-item"><span class="ai-feature-icon">📊</span><span>查看投资收益</span></div><div class="ai-feature-item"><span class="ai-feature-icon">👑</span><span>了解VIP权益</span></div><div class="ai-feature-item"><span class="ai-feature-icon">👥</span><span>查询团队数据</span></div></div><p style="margin-top:16px;color:#0e2b44;">请问有什么可以帮您？</p></div>',
    'recharge' => '<div class="ai-text-message"><p style="font-size:18px;font-weight:700;color:#0e2b44;margin-bottom:16px;">充值非常简单！</p><p style="margin-bottom:16px;color:#0e2b44;">支持以下方式：</p><div class="ai-feature-list"><div class="ai-feature-item"><span class="ai-feature-icon">💳</span><span>银行卡转账</span></div><div class="ai-feature-item"><span class="ai-feature-icon">🪙</span><span>USDT充值</span></div><div class="ai-feature-item"><span class="ai-feature-icon">💰</span><span>支付宝（部分渠道）</span></div></div><div class="ai-highlight-box" style="margin-top:16px;">请点击【去充值】按钮，选择充值方式完成操作。</div></div>',
    'withdraw' => '<div class="ai-text-message"><p style="font-size:18px;font-weight:700;color:#0e2b44;margin-bottom:16px;">提现规则说明</p><div class="ai-feature-list"><div class="ai-feature-item"><span class="ai-feature-icon">⏰</span><span>到账时间：1-24小时</span></div><div class="ai-feature-item"><span class="ai-feature-icon">💵</span><span>手续费：{{withdraw_fee}}%</span></div><div class="ai-feature-item"><span class="ai-feature-icon">🔒</span><span>最低提现：¥{{min_withdraw}}</span></div></div><div class="ai-warning-box" style="margin-top:16px;">⚠️ 需完成KYC实名认证</div><p style="margin-top:16px;color:#0e2b44;">点击【去提现】开始操作。</p></div>',
    'kyc' => '<div class="ai-text-message"><p style="font-size:18px;font-weight:700;color:#0e2b44;margin-bottom:16px;">实名认证（KYC）流程</p><div class="ai-step-list"><div class="ai-step-item"><div class="ai-step-number">1</div><div><strong>步骤1：</strong>填写真实姓名和身份证号</div></div><div class="ai-step-item"><div class="ai-step-number">2</div><div><strong>步骤2：</strong>上传身份证正反面照片</div></div><div class="ai-step-item"><div class="ai-step-number">3</div><div><strong>步骤3：</strong>人脸识别验证</div></div></div><div class="ai-info-box" style="margin-top:16px;">完成认证后可享受完整服务功能。</div></div>',
    'password_reset' => '<div class="ai-text-message"><p style="font-size:18px;font-weight:700;color:#0e2b44;margin-bottom:16px;">密码找回流程</p><div class="ai-step-list"><div class="ai-step-item"><div class="ai-step-number">1</div><div>提供您的账号</div></div><div class="ai-step-item"><div class="ai-step-number">2</div><div>通过人脸识别验证身份</div></div><div class="ai-step-item"><div class="ai-step-number">3</div><div>设置新密码</div></div></div><div class="ai-warning-box" style="margin-top:16px;">⚠️ 需要先完成KYC实名认证才能使用此服务。</div></div>'
];

$updated = 0;
foreach ($templates as $category => $template) {
    $result = $db->query('UPDATE pv_ai_knowledge_base SET answer_template = ? WHERE category = ?', [$template, $category]);
    if ($result) {
        $updated++;
        echo "✅ 已更新: {$category}\n";
    }
}
echo "\n总共更新了 {$updated} 个文本模板\n";
