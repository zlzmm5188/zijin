<?php

/**
 * Telegram Bot 通知服务（带按钮样式）
 * 用于发送系统通知到Telegram
 */

class TelegramNotify
{
    // Telegram Bot配置
    private static $botToken = '8041026981:AAHUv4O2sxz-xV3xEXxGmWhBQ32TDy99t7Y';
    private static $chatId = '-1002722181708'; // 默认接收人（群组/频道ID）
    private static $apiBase = 'https://api.telegram.org/bot';

    /**
     * 发送文本消息（支持按钮）
     *
     * @param string $message 消息内容
     * @param string|null $chatId 接收人ID（可选，默认使用配置的chatId）
     * @param string $parseMode 解析模式：'Markdown'、'HTML' 或 null
     * @param array|null $buttons 按钮数组 [['text' => '按钮文字', 'callback_data' => 'data']]
     * @return array|false 返回API响应或false
     */
    public static function sendMessage($message, $chatId = null, $parseMode = 'Markdown', $buttons = null)
    {
        $chatId = $chatId ?: self::$chatId;

        if (empty($message) || empty($chatId)) {
            error_log('[TelegramNotify] 消息内容或Chat ID为空');
            return false;
        }

        $url = self::$apiBase . self::$botToken . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'disable_web_page_preview' => true
        ];

        if ($parseMode) {
            $data['parse_mode'] = $parseMode;
        }

        // 添加按钮
        if ($buttons && is_array($buttons)) {
            $keyboard = ['inline_keyboard' => [$buttons]];
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log('[TelegramNotify] HTTP错误: ' . $httpCode . ' - ' . $response);
                return false;
            }

            $result = json_decode($response, true);

            if (!$result || !$result['ok']) {
                error_log('[TelegramNotify] API错误: ' . ($result['description'] ?? '未知错误'));
                return false;
            }

            return $result;
        } catch (Exception $e) {
            error_log('[TelegramNotify] 异常: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送用户注册通知
     */
    public static function notifyUserRegistered($username, $userId, $inviteCode = '')
    {
        $message = "👤 *用户名*: `{$username}`\n";
        $message .= "🆔 *用户ID*: `{$userId}`\n";
        if ($inviteCode) {
            $message .= "🔗 *邀请码*: `{$inviteCode}`\n";
        }
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s');

        $buttons = [
            ['text' => '🎉 新用户注册', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送充值通知
     */
    public static function notifyRecharge($username, $userId, $amount, $currency, $orderId)
    {
        $message = "👤 *用户*: `{$username}` (ID: {$userId})\n";
        $message .= "💵 *金额*: `{$amount} {$currency}`\n";
        $message .= "📝 *订单号*: `{$orderId}`\n";
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s') . "\n";
        $message .= "\n⚠️ 请及时审核处理";

        $buttons = [
            ['text' => '💰 充值申请', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送提现通知
     */
    public static function notifyWithdraw($username, $userId, $amount, $currency, $orderId)
    {
        $message = "👤 *用户*: `{$username}` (ID: {$userId})\n";
        $message .= "💵 *金额*: `{$amount} {$currency}`\n";
        $message .= "📝 *订单号*: `{$orderId}`\n";
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s') . "\n";
        $message .= "\n⚠️ 请及时审核处理";

        $buttons = [
            ['text' => '💸 提现申请', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送投资通知
     */
    public static function notifyInvestment($username, $userId, $projectName, $amount, $currency)
    {
        $message = "👤 *用户*: `{$username}` (ID: {$userId})\n";
        $message .= "📦 *项目*: {$projectName}\n";
        $message .= "💵 *金额*: `{$amount} {$currency}`\n";
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s');

        $buttons = [
            ['text' => '📊 新投资订单', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送KYC认证通知
     */
    public static function notifyKYC($username, $userId, $realname)
    {
        $message = "👤 *用户*: `{$username}` (ID: {$userId})\n";
        $message .= "📝 *真实姓名*: {$realname}\n";
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s') . "\n";
        $message .= "\n⚠️ 请登录后台审核";

        $buttons = [
            ['text' => '🆔 KYC认证申请', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送系统错误通知
     */
    public static function notifyError($errorType, $errorMessage, $file = '', $line = 0)
    {
        $message = "🔴 *类型*: {$errorType}\n";
        $message .= "📄 *消息*: `{$errorMessage}`\n";
        if ($file) {
            $message .= "📂 *文件*: {$file}:{$line}\n";
        }
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s');

        $buttons = [
            ['text' => '❌ 系统错误', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 发送自定义通知
     */
    public static function notify($title, $content, $icon = '📢')
    {
        $message = $content . "\n";
        $message .= "⏰ *时间*: " . date('Y-m-d H:i:s');

        $buttons = [
            ['text' => "{$icon} {$title}", 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }

    /**
     * 测试连接
     */
    public static function testConnection()
    {
        $message = "✅ Telegram Bot通知功能正常\n";
        $message .= "⏰ 测试时间: " . date('Y-m-d H:i:s');

        $buttons = [
            ['text' => '🔔 Providence系统测试', 'callback_data' => 'ignore']
        ];

        return self::sendMessage($message, null, 'Markdown', $buttons);
    }
}
