<?php
/**
 * API响应类（修复CORS）
 */
class Response {
    /**
     * 成功响应
     */
    public static function success($data = null, $message = '操作成功') {
        self::output([
            'code' => 1,
            'message' => $message,
            'data' => $data,
            'time' => time()
        ]);
    }
    
    /**
     * 失败响应
     */
    public static function error($message = '操作失败', $code = -1, $data = null) {
        self::output([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'time' => time()
        ]);
    }
    
    /**
     * 输出JSON
     */
    private static function output($data) {
        // 清除所有输出缓冲
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // CORS头（修复跨域）⭐
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, token, sign, timestamp, X-Signature, X-Timestamp, X-Nonce, Idempotency-Key');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        // OPTIONS 预检请求
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 分页响应
     */
    public static function paginate($list, $total, $page = 1, $pageSize = 20) {
        self::success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ]);
    }
}
