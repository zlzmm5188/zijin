<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>实名状态检查工具</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .status-yes { color: green; font-weight: bold; }
        .status-no { color: red; font-weight: bold; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h1>🔍 实名认证状态检查工具</h1>

    <?php
    // 数据库配置
    $host = '127.0.0.1';
    $dbname = 'v2_abcmall_one';
    $username = 'v2_abcmall_one';
    $password = 'HT3QtZMSnXrF1Ajc';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 查询最近10个用户的实名状态
        $stmt = $pdo->query("
            SELECT
                id,
                username,
                realname,
                idcard as id_card,
                CASE
                    WHEN realname IS NOT NULL AND realname != '' AND idcard IS NOT NULL AND idcard != ''
                    THEN '已实名'
                    ELSE '未实名'
                END as kyc_status,
                FROM_UNIXTIME(created_at) as reg_time
            FROM fa_user
            ORDER BY id DESC
            LIMIT 10
        ");

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>最近注册用户实名状态</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>账号</th><th>真实姓名</th><th>身份证号</th><th>实名状态</th><th>注册时间</th></tr>";

        foreach ($users as $user) {
            $statusClass = $user['kyc_status'] === '已实名' ? 'status-yes' : 'status-no';
            $realname = $user['realname'] ?: '-';
            $idCard = $user['id_card'] ? substr($user['id_card'], 0, 6) . '********' . substr($user['id_card'], -4) : '-';

            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$realname}</td>";
            echo "<td>{$idCard}</td>";
            echo "<td class='{$statusClass}'>{$user['kyc_status']}</td>";
            echo "<td>{$user['reg_time']}</td>";
            echo "</tr>";
        }

        echo "</table>";

        // 统计
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN realname IS NOT NULL AND realname != '' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN realname IS NULL OR realname = '' THEN 1 ELSE 0 END) as unverified
            FROM fa_user
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<h2>统计信息</h2>";
        echo "<p>总用户数：{$stats['total']}</p>";
        echo "<p class='status-yes'>已实名：{$stats['verified']}</p>";
        echo "<p class='status-no'>未实名：{$stats['unverified']}</p>";

    } catch (PDOException $e) {
        echo "<p style='color:red;'>数据库错误：" . $e->getMessage() . "</p>";
    }
    ?>

    <hr>
    <p style="font-size:12px;color:#666;">
        访问地址：https://agx.bi/check-kyc-status.php
    </p>
</body>
</html>
