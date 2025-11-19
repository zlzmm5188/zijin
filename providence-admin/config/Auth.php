<?php
/**
 * 用户认证类
 */
class Auth {
    private static $config;

    public static function init() {
        self::$config = require __DIR__ . '/app.php';
    }

    /**
     * 生成Token
     */
    public static function generateToken($userId, $username) {
        self::init();
        $payload = [
            'user_id' => $userId,
            'username' => $username,
            'exp' => time() + 86400 * 7, // 7天过期
            'iat' => time()
        ];

        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", self::$config['jwt_secret']);

        return "$header.$payload.$signature";
    }

    /**
     * 验证Token
     */
    public static function verifyToken($token) {
        self::init();
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // 验证签名
        $validSignature = hash_hmac('sha256', "$header.$payload", self::$config['jwt_secret']);
        if ($signature !== $validSignature) {
            return false;
        }

        // 解析payload
        $data = json_decode(base64_decode($payload), true);

        // 检查是否过期
        if (isset($data['exp']) && $data['exp'] < time()) {
            return false;
        }

        return $data;
    }

    /**
     * 从请求头获取Token
     */
    public static function getToken() {
        // 从header获取
        $headers = getallheaders();
        if (isset($headers['token'])) {
            return $headers['token'];
        }
        if (isset($headers['Token'])) {
            return $headers['Token'];
        }
        if (isset($headers['Authorization'])) {
            return str_replace('Bearer ', '', $headers['Authorization']);
        }

        // 从GET/POST获取
        return $_GET['token'] ?? $_POST['token'] ?? null;
    }

    /**
     * 获取当前用户信息
     */
    public static function user() {
        // 无登录模式：始终返回 guest 用户（带管理员权限）
        return [
            'id' => 1,
            'user_id' => 1,  // 兼容 API 中使用的 user_id 字段
            'username' => 'guest',
            'is_admin' => true,  // 无登录模式下，guest 用户拥有管理员权限
        ];
        // 以下代码已禁用，不再检查 token
        // $token = self::getToken();
        // if (!$token) {
        //     return null;
        // }
        // return self::verifyToken($token);
    }

    /**
     * 检查是否登录
     */
    public static function check() {
        return self::user() !== null;
    }

    /**
     * 密码加密
     */
    public static function hashPassword($password) {
        self::init();
        return md5(md5($password) . self::$config['password_salt']);
    }
}
