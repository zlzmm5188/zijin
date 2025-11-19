<?php
require_once __DIR__ . '/../config/bootstrap.php';

// 模拟POST请求
$_SERVER['REQUEST_METHOD'] = 'POST';

// 测试数据
$testData = [
    'username' => 'TestUser123',
    'password' => 'Test@123',
    'invite' => 'g138688'
];

echo "开始注册测试...\n";
echo "用户名: " . $testData['username'] . "\n";
echo "密码: " . $testData['password'] . "\n";
echo "邀请码: " . $testData['invite'] . "\n\n";

// 模拟输入
file_put_contents('php://input', json_encode($testData));

try {
    $db = Database::getInstance();
    echo "数据库连接成功\n";
    
    $userCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM " . $db->getPrefix() . "users");
    echo "当前用户数: " . $userCount['cnt'] . "\n\n";
    
    // 测试邀请码查询
    $invite = $testData['invite'];
    $parent = $db->fetchOne("SELECT id, username FROM " . $db->getPrefix() . "users WHERE invite_code = :code OR uid = :uid LIMIT 1", ['code' => $invite, 'uid' => $invite]);
    
    if ($parent) {
        echo "找到邀请人: ID=" . $parent['id'] . ", 用户名=" . $parent['username'] . "\n";
    } else {
        echo "未找到邀请人: " . $invite . "\n";
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
