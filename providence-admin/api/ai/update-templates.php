<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
$db = Database::getInstance();
$templates = [
    'balance' => '<div class="ai-info-card"><div class="ai-info-card-title">💰 账户余额</div><div class="ai-info-card-grid"><div class="ai-info-card-item"><div class="ai-info-card-label">CNY余额</div><div class="ai-info-card-value amount">{{balance_cny}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">USDT余额</div><div class="ai-info-card-value usdt">{{balance_usdt}}</div></div></div><div class="ai-info-card-footer">如需充值或提现，请点击下方按钮</div></div>',
    'investment' => '<div class="ai-info-card"><div class="ai-info-card-title">📊 投资情况</div><div class="ai-info-card-grid"><div class="ai-info-card-item"><div class="ai-info-card-label">总投资金额</div><div class="ai-info-card-value amount">{{total_invest}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">进行中项目</div><div class="ai-info-card-value">{{active_count}}个</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">累计收益</div><div class="ai-info-card-value amount">{{total_profit}}</div></div></div><div class="ai-info-card-footer">详情请查看【我的投资】页面</div></div>',
    'vip' => '<div class="ai-info-card"><div class="ai-info-card-title">👑 VIP信息</div><div class="ai-info-card-grid"><div class="ai-info-card-item"><div class="ai-info-card-label">VIP等级</div><div class="ai-info-card-value">VIP{{vip_level}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">额外加息</div><div class="ai-info-card-value">{{vip_rate}}%</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">专属权益</div><div class="ai-info-card-value" style="font-size:16px;">{{vip_benefits}}</div></div></div><div class="ai-info-card-footer">了解如何升级VIP，请查看【VIP中心】</div></div>',
    'ribao' => '<div class="ai-info-card"><div class="ai-info-card-title">📈 日利宝</div><div class="ai-info-card-grid"><div class="ai-info-card-item"><div class="ai-info-card-label">当前余额</div><div class="ai-info-card-value amount">{{ribao_balance}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">昨日收益</div><div class="ai-info-card-value amount">{{ribao_yesterday_profit}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">累计收益</div><div class="ai-info-card-value amount">{{ribao_total_profit}}</div></div></div><div class="ai-info-card-footer">支持随时转入转出，详情请访问【日利宝】页面</div></div>',
    'team' => '<div class="ai-info-card"><div class="ai-info-card-title">👥 团队数据</div><div class="ai-info-card-grid"><div class="ai-info-card-item"><div class="ai-info-card-label">团队人数</div><div class="ai-info-card-value">{{team_count}}人</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">团队业绩</div><div class="ai-info-card-value amount">{{team_performance}}</div></div><div class="ai-info-card-item"><div class="ai-info-card-label">推荐奖励</div><div class="ai-info-card-value amount">{{referral_rewards}}</div></div></div><div class="ai-info-card-footer">查看详情请访问【团队】页面</div></div>'
];
$updated = 0;
foreach ($templates as $category => $template) {
    $result = $db->query("UPDATE pv_ai_knowledge_base SET answer_template = ? WHERE category = ?", [$template, $category]);
    if ($result) {
        $updated++;
        echo "✅ 已更新: {$category}\n";
    }
}
echo "\n总共更新了 {$updated} 个模板\n";
