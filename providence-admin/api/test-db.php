<?php
header('Content-Type: text/plain');
echo "数据库连接测试...\n\n";

$pdo = new PDO(
    "mysql:host=localhost;dbname=providence;charset=utf8mb4",
    "providence",
    "Providence@2024"
);

echo "✅ 数据库连接成功\n\n";

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 1");
$stmt->execute(['Qq123456']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ 找到用户:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "用户名: " . $user['username'] . "\n";
    echo "密码: " . $user['password'] . "\n";
} else {
    echo "❌ 未找到用户\n";
}
