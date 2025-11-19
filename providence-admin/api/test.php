<?php
/**
 * API测试页面
 * 访问: http://YOUR_IP/providence-admin/api/test.php
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PROVIDENCE API 测试</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .test-btn { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .test-btn:hover { background: #2980b9; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .code { font-family: 'Courier New', monospace; background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🚀 PROVIDENCE API 测试工具</h1>
    
    <?php
    // 测试数据库连接
    require_once __DIR__ . '/../config/bootstrap.php';
    
    echo '<div class="status info">';
    echo '<h3>📊 系统状态检查</h3>';
    
    try {
        $db = Database::getInstance();
        echo '<p>✅ 数据库连接: <strong>正常</strong></p>';
        
        // 统计数据
        $userCount = $db->count('users');
        $projectCount = $db->count('projects');
        $vipCount = $db->count('vip_levels');
        
        echo '<p>✅ 用户数量: <strong>' . $userCount . '</strong></p>';
        echo '<p>✅ 项目数量: <strong>' . $projectCount . '</strong></p>';
        echo '<p>✅ VIP等级: <strong>' . $vipCount . '</strong></p>';
        
    } catch (Exception $e) {
        echo '<p>❌ 数据库错误: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
    ?>
    
    <div class="status success">
        <h3>✅ API接口列表</h3>
        <table>
            <thead>
                <tr>
                    <th>接口名称</th>
                    <th>请求方式</th>
                    <th>API路径</th>
                    <th>说明</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>用户登录</td>
                    <td>POST</td>
                    <td><code class="code">/login/login/account</code></td>
                    <td>需要username和password</td>
                </tr>
                <tr>
                    <td>用户注册</td>
                    <td>POST</td>
                    <td><code class="code">/login/reg/account</code></td>
                    <td>需要username和password</td>
                </tr>
                <tr>
                    <td>用户信息</td>
                    <td>GET</td>
                    <td><code class="code">/user/user/index</code></td>
                    <td>需要token认证</td>
                </tr>
                <tr>
                    <td>项目列表</td>
                    <td>GET</td>
                    <td><code class="code">/fund/project/all</code></td>
                    <td>无需认证</td>
                </tr>
                <tr>
                    <td>项目详情</td>
                    <td>GET</td>
                    <td><code class="code">/fund/project/detail?id=1</code></td>
                    <td>无需认证</td>
                </tr>
                <tr>
                    <td>投资项目</td>
                    <td>POST</td>
                    <td><code class="code">/fund/project/add</code></td>
                    <td>需要token认证</td>
                </tr>
                <tr>
                    <td>VIP等级列表</td>
                    <td>GET</td>
                    <td><code class="code">/user/level/list</code></td>
                    <td>无需认证</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="status info">
        <h3>📝 测试示例</h3>
        
        <h4>1. 测试注册接口</h4>
        <pre>curl -X POST http://<?php echo $_SERVER['HTTP_HOST']; ?>/providence-admin/api/login/reg/account \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456"
  }'</pre>
        
        <h4>2. 测试登录接口</h4>
        <pre>curl -X POST http://<?php echo $_SERVER['HTTP_HOST']; ?>/providence-admin/api/login/login/account \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456"
  }'</pre>
        
        <h4>3. 测试项目列表（无需登录）</h4>
        <pre>curl http://<?php echo $_SERVER['HTTP_HOST']; ?>/providence-admin/api/fund/project/all</pre>
        
        <button class="test-btn" onclick="testProjectList()">点击测试项目列表</button>
        <div id="result"></div>
    </div>
    
    <div class="status success">
        <h3>🔧 配置信息</h3>
        <p><strong>API Base URL:</strong> <code class="code">http://<?php echo $_SERVER['HTTP_HOST']; ?>/providence-admin/api</code></p>
        <p><strong>服务器IP:</strong> <code class="code"><?php echo $_SERVER['SERVER_ADDR']; ?></code></p>
        <p><strong>PHP版本:</strong> <code class="code"><?php echo phpversion(); ?></code></p>
        <p><strong>项目路径:</strong> <code class="code">/www/wwwroot/providence-admin</code></p>
    </div>
    
    <script>
    function testProjectList() {
        const url = '/providence-admin/api/fund/project/all';
        document.getElementById('result').innerHTML = '<p>正在测试...</p>';
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = 
                    '<h4>✅ 测试成功！</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = 
                    '<h4>❌ 测试失败</h4><p>' + error.message + '</p>';
            });
    }
    </script>
</body>
</html>
