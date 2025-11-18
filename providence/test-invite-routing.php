<?php
/**
 * 测试邀请链接路由功能
 * 访问: http://your-domain.com/test-invite-routing.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>邀请链接路由测试</h1>";
echo "<pre>";

// 1. 检查环境变量
echo "=== 环境信息 ===\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '未设置') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '未设置') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '未设置') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '未设置') . "\n";
echo "\n";

// 2. 检查文件是否存在
echo "=== 文件检查 ===\n";
$files = [
    'check_invite.php',
    'invite-landing.html',
    'index.html'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    echo "$file: " . ($exists ? "✓ 存在" : "✗ 不存在") . "\n";
    if ($exists) {
        echo "  路径: $fullPath\n";
        echo "  权限: " . substr(sprintf('%o', fileperms($fullPath)), -4) . "\n";
    }
}
echo "\n";

// 3. 测试子域名解析
echo "=== 子域名解析测试 ===\n";
$testHosts = [
    'ABC123.4kp3l0iq.top',
    'test123.4kp3l0iq.top',
    $_SERVER['HTTP_HOST'] ?? 'current-host'
];

$supportedDomains = ['4kp3l0iq.top'];

foreach ($testHosts as $host) {
    echo "测试: $host\n";
    $matched = false;
    foreach ($supportedDomains as $domain) {
        $pattern = '/^([a-zA-Z0-9]+)\.' . preg_quote($domain, '/') . '$/';
        if (preg_match($pattern, $host, $matches)) {
            $inviteCode = strtoupper($matches[1]);
            echo "  ✓ 匹配子域名，邀请码: $inviteCode\n";
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        echo "  ✗ 不是子域名或不在支持列表中\n";
    }
}
echo "\n";

// 4. 测试数据库连接
echo "=== 数据库连接测试 ===\n";
$host = '127.0.0.1';
$dbname = 'v2_abcmall_one';
$username = 'v2_abcmall_one';
$password = 'HT3QtZMSnXrF1Ajc';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ 数据库连接成功\n";

    // 测试查询邀请码
    $testCode = 'ABC123';
    $stmt = $pdo->prepare("SELECT id, invite FROM fa_user WHERE UPPER(invite) = :invite LIMIT 1");
    $stmt->execute(['invite' => strtoupper($testCode)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "✓ 邀请码 '$testCode' 在数据库中存在 (用户ID: {$user['id']})\n";
    } else {
        echo "✗ 邀请码 '$testCode' 在数据库中不存在\n";
    }

    // 列出前5个邀请码
    $stmt = $pdo->query("SELECT invite FROM fa_user WHERE invite IS NOT NULL AND invite != '' LIMIT 5");
    $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\n数据库中的邀请码示例（前5个）:\n";
    foreach ($codes as $code) {
        echo "  - $code\n";
    }

} catch (PDOException $e) {
    echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Nginx配置建议
echo "=== Nginx配置建议 ===\n";
echo "请在Nginx配置中添加以下规则:\n";
echo "\n";
echo "location = / {\n";
echo "    try_files /check_invite.php =404;\n";
echo "    fastcgi_pass unix:/tmp/php-cgi-74.sock;\n";
echo "    fastcgi_index check_invite.php;\n";
echo "    include fastcgi.conf;\n";
echo "}\n";

echo "</pre>";
?>
