<?php
/**
 * 响应格式化类 - 统一管理回复格式化
 */
class ResponseFormatter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 格式化数据卡片（用于余额、投资等数据查询）
     */
    public function formatDataCard($intent, $userData) {
        $template = $intent['answer_template'];

        // 替换模板变量
        if (!empty($intent['requires_data'])) {
            $requiredFields = is_string($intent['requires_data'])
                ? array_map('trim', explode(',', $intent['requires_data']))
                : [];

            foreach ($requiredFields as $field) {
                if (empty($field)) continue;

                $value = $userData && isset($userData[$field])
                    ? $userData[$field]
                    : $this->getDefaultValue($field);

                $template = str_replace('{{' . $field . '}}', $value, $template);
            }
        }

        // 替换所有剩余的变量
        if (preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches)) {
            foreach ($matches[1] as $var) {
                $var = trim($var);
                $value = $userData && isset($userData[$var])
                    ? $userData[$var]
                    : $this->getDefaultValue($var);
                $template = str_replace('{{' . $var . '}}', $value, $template);
            }
        }

        return $template;
    }

    /**
     * 格式化模板（用于知识库回复）
     */
    public function formatTemplate($intent, $userData) {
        return $this->formatDataCard($intent, $userData);
    }

    /**
     * 生成操作按钮
     */
    public function generateActionButtons($intent, $userData) {
        if (!$intent) {
            return [
                ['text' => '💰 查余额', 'action' => 'query:我的余额', 'type' => 'primary'],
                ['text' => '📊 查投资', 'action' => 'query:我的投资'],
                ['text' => '👑 查VIP', 'action' => 'query:VIP等级'],
            ];
        }

        $buttons = [];

        switch ($intent['category']) {
            case 'balance':
                $buttons[] = ['text' => '💰 查余额', 'action' => 'query:我的余额', 'type' => 'primary'];
                $buttons[] = ['text' => '💳 去充值', 'action' => 'api:recharge'];
                break;
            case 'investment':
                $buttons[] = ['text' => '📊 查投资', 'action' => 'query:我的投资', 'type' => 'primary'];
                $buttons[] = ['text' => '🔍 浏览项目', 'action' => 'navigate:projects-list.html'];
                break;
            case 'vip':
                $buttons[] = ['text' => '👑 查VIP', 'action' => 'query:VIP等级', 'type' => 'primary'];
                $buttons[] = ['text' => '⭐ VIP中心', 'action' => 'api:vip'];
                break;
            case 'recharge':
                $buttons[] = ['text' => '💳 去充值', 'action' => 'api:recharge', 'type' => 'primary'];
                $buttons[] = ['text' => '💰 查余额', 'action' => 'query:我的余额'];
                break;
            case 'withdraw':
                $buttons[] = ['text' => '💰 去提现', 'action' => 'api:withdraw', 'type' => 'primary'];
                $buttons[] = ['text' => '💳 查余额', 'action' => 'query:我的余额'];
                break;
            case 'ribao':
                $buttons[] = ['text' => '📈 日利宝', 'action' => 'query:日利宝', 'type' => 'primary'];
                $buttons[] = ['text' => '💰 查余额', 'action' => 'query:我的余额'];
                break;
            case 'team':
                $buttons[] = ['text' => '👥 查团队', 'action' => 'query:我的团队', 'type' => 'primary'];
                $buttons[] = ['text' => '📊 查投资', 'action' => 'query:我的投资'];
                break;
            case 'kyc':
                $buttons[] = ['text' => '✅ 去认证', 'action' => 'navigate:kyc.html', 'type' => 'primary'];
                $buttons[] = ['text' => '📋 了解流程', 'action' => 'query:实名认证'];
                break;
            case 'password_reset':
                $buttons[] = ['text' => '🔐 找回密码', 'action' => 'navigate:messages.html?intent=password_reset', 'type' => 'primary'];
                $buttons[] = ['text' => '✅ 去认证', 'action' => 'navigate:kyc.html'];
                break;
            case 'greeting':
                $buttons[] = ['text' => '💰 查余额', 'action' => 'query:我的余额', 'type' => 'primary'];
                $buttons[] = ['text' => '📊 查投资', 'action' => 'query:我的投资'];
                break;
            case 'company':
                $buttons[] = ['text' => '📊 产品分析', 'action' => 'query:产品分析', 'type' => 'primary'];
                $buttons[] = ['text' => '📢 通知公告', 'action' => 'query:通知公告'];
                break;
            case 'product':
                $buttons[] = ['text' => '🏢 公司介绍', 'action' => 'query:公司介绍', 'type' => 'primary'];
                $buttons[] = ['text' => '💰 去投资', 'action' => 'navigate:projects-list.html'];
                break;
            case 'notice':
                $buttons[] = ['text' => '📢 查看详情', 'action' => 'navigate:messages.html', 'type' => 'primary'];
                $buttons[] = ['text' => '🏢 公司介绍', 'action' => 'query:公司介绍'];
                break;
        }
        
        return $buttons;
    }

    /**
     * 获取默认值
     */
    private function getDefaultValue($field) {
        switch ($field) {
            case 'vip_level':
                return '0';
            case 'vip_rate':
                return '0';
            case 'vip_benefits':
                return '普通会员';
            case 'balance_cny':
            case 'balance_usdt':
            case 'ribao_balance':
            case 'ribao_total_profit':
            case 'ribao_yesterday_profit':
            case 'total_invest':
            case 'total_profit':
            case 'team_performance':
                return '0.00';
            case 'active_count':
            case 'team_count':
            case 'points':
                return '0';
            default:
                return '';
        }
    }
}
