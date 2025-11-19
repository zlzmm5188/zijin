<?php
/**
 * API签名验证（企业级）
 * HMAC-SHA256 + Nonce防重放
 */
class SignValidator {
    private static $secretKey = 'Providence_HMAC_Secret_Key_2024_Enterprise';
    
    /**
     * 生成签名
     * HMAC-SHA256(secret, method + '\n' + path + '\n' + timestamp + '\n' + nonce + '\n' + sha256(body))
     */
    public static function generateSign($method, $path, $timestamp, $nonce, $body = '') {
        $bodyHash = hash('sha256', $body);
        $message = $method . "\n" . $path . "\n" . $timestamp . "\n" . $nonce . "\n" . $bodyHash;
        return hash_hmac('sha256', $message, self::$secretKey);
    }
    
    /**
     * 验证签名（企业级）
     */
    public static function validate() {
        $headers = getallheaders();
        
        $signature = $headers['X-Signature'] ?? $headers['x-signature'] ?? '';
        $timestamp = $headers['X-Timestamp'] ?? $headers['x-timestamp'] ?? 0;
        $nonce = $headers['X-Nonce'] ?? $headers['x-nonce'] ?? '';
        
        if (empty($signature) || empty($timestamp) || empty($nonce)) {
            Response::error('缺少签名参数', -1);
        }
        
        // 1. 检查时间窗（±300秒）
        if (abs(time() - $timestamp) > 300) {
            Response::error('请求已过期', -1);
        }
        
        // 2. Nonce去重（Redis或数据库）
        $db = Database::getInstance();
        $exists = $db->fetchOne(
            "SELECT 1 FROM " . $db->getPrefix() . "api_nonces WHERE nonce = :nonce",
            ['nonce' => $nonce]
        );
        
        if ($exists) {
            Response::error('Nonce已被使用，请勿重放', -1);
        }
        
        // 3. 验证签名
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $body = file_get_contents('php://input');
        
        $validSign = self::generateSign($method, $path, $timestamp, $nonce, $body);
        
        if ($signature !== $validSign) {
            // 记录可疑请求
            AuditLog::log('risk', '签名验证失败', 'SYSTEM', 0, 'invalid_sign', null, null,
                ['ip' => $_SERVER['REMOTE_ADDR'], 'path' => $path]);
            
            Response::error('签名验证失败', -1);
        }
        
        // 4. 记录Nonce（5分钟过期）
        $db->insert('api_nonces', [
            'nonce' => $nonce
        ]);
        
        // 清理过期nonce（异步任务做更好）
        $db->query(
            "DELETE FROM " . $db->getPrefix() . "api_nonces WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
        
        return true;
    }
}
