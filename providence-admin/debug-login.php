<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/bootstrap.php';

$db = Database::getInstance();
echo "表前缀: [" . $db->getPrefix() . "]\n\n";

// 测试查询
$sql = "SELECT * FROM users WHERE username = 'Qq123456' AND status = 1";
echo "SQL: $sql\n\n";

try {
    $user = $db->fetchOne($sql);
    
    if ($user) {
        echo "✅ 找到用户:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "用户名: " . $user['username'] . "\n";
        echo "密码hash: " . $user['password'] . "\n";
        echo "状态: " . $user['status'] . "\n\n";
        
        // 测试密码
        $inputHash = Auth::hashPassword('Qq123456');
        echo "输入密码hash: $inputHash\n\n";
        
        if ($user['password'] === $inputHash) {
            echo "✅ 密码匹配！登录应该成功！\n";
        } else {
            echo "❌ 密码不匹配！\n";
            echo "数据库: " . $user['password'] . "\n";
            echo "输入: " . $inputHash . "\n";
        }
    } else {
        echo "❌ 未找到用户\n";
    }
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}
