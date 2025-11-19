<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>账号注册登录API测试</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h2 { color: #333; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        label { font-weight: bold; display: block; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>账号注册登录API测试工具</h1>

    <div class="test-section">
        <h2>1. 测试账号注册接口</h2>
        <label>账号（8位以上，大小写字母）：</label>
        <input type="text" id="regUsername" value="TestUser123" placeholder="例如：TestUser123">
        
        <label>密码（8位以上，大小写字母+特殊符号）：</label>
        <input type="password" id="regPassword" value="TestPass123!@#" placeholder="例如：TestPass123!@#">
        
        <label>邀请码：</label>
        <input type="text" id="regInvite" value="" placeholder="请输入有效的邀请码">
        
        <button onclick="testRegister()">测试注册</button>
        <div id="regResult" class="result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>2. 测试账号登录接口</h2>
        <label>账号：</label>
        <input type="text" id="loginUsername" value="TestUser123" placeholder="已注册的账号">
        
        <label>密码：</label>
        <input type="password" id="loginPassword" value="TestPass123!@#" placeholder="密码">
        
        <button onclick="testLogin()">测试登录</button>
        <div id="loginResult" class="result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>3. 测试现有手机号登录接口（对比）</h2>
        <label>手机号：</label>
        <input type="tel" id="mobile" value="8613800138000" placeholder="86+手机号">
        
        <label>密码：</label>
        <input type="password" id="mobilePassword" value="123456" placeholder="密码">
        
        <button onclick="testMobileLogin()">测试手机号登录</button>
        <div id="mobileLoginResult" class="result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>4. 查看后端状态</h2>
        <button onclick="checkBackendStatus()">检查后端</button>
        <div id="statusResult" class="result" style="display:none;"></div>
    </div>

    <script>
        const API_BASE = 'https://v2.abcmall.one/index.php';

        async function testRegister() {
            const username = document.getElementById('regUsername').value;
            const password = document.getElementById('regPassword').value;
            const invite = document.getElementById('regInvite').value;
            const resultDiv = document.getElementById('regResult');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = '请求中...';
            resultDiv.className = 'result';

            try {
                const response = await fetch(API_BASE + '/login/reg/account', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password, invite})
                });

                const text = await response.text();
                resultDiv.textContent = `状态码: ${response.status}\n\n响应内容:\n${text}`;
                
                try {
                    const data = JSON.parse(text);
                    resultDiv.className = (data.code === 200) ? 'result success' : 'result error';
                } catch(e) {
                    resultDiv.className = 'result error';
                }
            } catch (error) {
                resultDiv.textContent = '请求失败: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        async function testLogin() {
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            const resultDiv = document.getElementById('loginResult');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = '请求中...';
            resultDiv.className = 'result';

            try {
                const response = await fetch(API_BASE + '/login/login/account', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password, system: 1})
                });

                const text = await response.text();
                resultDiv.textContent = `状态码: ${response.status}\n\n响应内容:\n${text}`;
                
                try {
                    const data = JSON.parse(text);
                    resultDiv.className = (data.code === 200) ? 'result success' : 'result error';
                    if (data.code === 200 && data.data && data.data.token) {
                        resultDiv.textContent += '\n\n✅ Token: ' + data.data.token.substring(0, 30) + '...';
                    }
                } catch(e) {
                    resultDiv.className = 'result error';
                }
            } catch (error) {
                resultDiv.textContent = '请求失败: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        async function testMobileLogin() {
            const mobile = document.getElementById('mobile').value;
            const password = document.getElementById('mobilePassword').value;
            const resultDiv = document.getElementById('mobileLoginResult');
            
            resultDiv.style.display = 'block';
            resultDiv.textContent = '请求中...';
            resultDiv.className = 'result';

            try {
                const response = await fetch(API_BASE + '/login/login/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        area: 86,
                        username: mobile,
                        password: password,
                        system: 1
                    })
                });

                const text = await response.text();
                resultDiv.textContent = `状态码: ${response.status}\n\n响应内容:\n${text}`;
                
                try {
                    const data = JSON.parse(text);
                    resultDiv.className = (data.code === 200) ? 'result success' : 'result error';
                } catch(e) {
                    resultDiv.className = 'result error';
                }
            } catch (error) {
                resultDiv.textContent = '请求失败: ' + error.message;
                resultDiv.className = 'result error';
            }
        }

        async function checkBackendStatus() {
            const resultDiv = document.getElementById('statusResult');
            resultDiv.style.display = 'block';
            resultDiv.textContent = '检查中...';
            resultDiv.className = 'result';

            let status = '后端状态检查:\n\n';

            // 测试1: 基础连接
            try {
                const r1 = await fetch('https://v2.abcmall.one/');
                status += `✅ 后端服务器在线 (状态码: ${r1.status})\n`;
            } catch(e) {
                status += `❌ 后端服务器离线: ${e.message}\n`;
            }

            // 测试2: index.php
            try {
                const r2 = await fetch('https://v2.abcmall.one/index.php');
                status += `✅ index.php可访问 (状态码: ${r2.status})\n`;
            } catch(e) {
                status += `❌ index.php不可访问: ${e.message}\n`;
            }

            // 测试3: 现有接口
            try {
                const r3 = await fetch(API_BASE + '/user/level/list');
                const d3 = await r3.json();
                status += `✅ 现有API正常 (/user/level/list)\n`;
            } catch(e) {
                status += `❌ 现有API异常: ${e.message}\n`;
            }

            //  测试4: 新接口
            try {
                const r4 = await fetch(API_BASE + '/login/reg/account', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username: '', password: '', invite: ''})
                });
                const text = await r4.text();
                if (r4.status === 404) {
                    status += `❌ 新注册接口不存在 (404)\n`;
                } else {
                    status += `✅ 新注册接口已创建 (状态码: ${r4.status})\n响应: ${text}\n`;
                }
            } catch(e) {
                status += `❌ 新注册接口测试失败: ${e.message}\n`;
            }

            resultDiv.textContent = status;
        }

        // 页面加载时自动检查
        window.addEventListener('DOMContentLoaded', function() {
            checkBackendStatus();
        });
    </script>
</body>
</html>

