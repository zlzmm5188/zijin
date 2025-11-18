<?php
/**
 * 幂等性服务
 * 防止重复提交（充值/提现/下单）
 */
class IdempotencyService {
    /**
     * 检查并记录幂等性键
     * @return array|null 如果是重复请求，返回之前的响应；否则返回null
     */
    public static function check($key, $userId, $endpoint) {
        if (empty($key)) {
            return null; // 未提供key，不启用幂等性
        }
        
        $db = Database::getInstance();
        
        // 查询是否已存在
        $existing = $db->fetchOne(
            "SELECT response FROM " . $db->getPrefix() . "idempotency_keys WHERE idempotency_key = :key AND user_id = :uid",
            ['key' => $key, 'uid' => $userId]
        );
        
        if ($existing) {
            // 已存在，返回之前的响应
            return json_decode($existing['response'], true);
        }
        
        return null;
    }
    
    /**
     * 保存幂等性响应
     */
    public static function save($key, $userId, $endpoint, $response) {
        if (empty($key)) {
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $db->insert('idempotency_keys', [
                'idempotency_key' => $key,
                'user_id' => $userId,
                'api_endpoint' => $endpoint,
                'response' => json_encode($response, JSON_UNESCAPED_UNICODE)
            ]);
        } catch (Exception $e) {
            // 忽略重复键错误
            error_log("Idempotency key save failed: " . $e->getMessage());
        }
    }
    
    /**
     * 清理过期的幂等性键（7天前的）
     */
    public static function cleanup() {
        $db = Database::getInstance();
        $db->query(
            "DELETE FROM " . $db->getPrefix() . "idempotency_keys WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
    }
}
