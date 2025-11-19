<?php
// 测试education API
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>API测试</title>
</head>
<body>
<h1>Education API 测试</h1>

<h2>1. 文件检查</h2>
<?php
$dataFile = __DIR__ . '/data/education-data.json';
echo "<p>数据文件路径: $dataFile</p>";
echo "<p>文件是否存在: " . (file_exists($dataFile) ? '✅ 是' : '❌ 否') . "</p>";

if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    $totalCourses = count($data['courses'] ?? []);
    echo "<p>总课程数: $totalCourses</p>";

    $fundCourses = array_filter($data['courses'], function($c) { return $c['category'] === 'fund'; });
    $ipoCourses = array_filter($data['courses'], function($c) { return $c['category'] === 'ipo'; });
    $bondCourses = array_filter($data['courses'], function($c) { return $c['category'] === 'bond'; });

    echo "<p>基金课程: " . count($fundCourses) . "</p>";
    echo "<p>IPO课程: " . count($ipoCourses) . "</p>";
    echo "<p>债券课程: " . count($bondCourses) . "</p>";
}
?>

<h2>2. API调用测试</h2>
<script>
async function testAPI() {
    const baseUrl = window.location.origin + window.location.pathname.replace('test-api.php', '');
    const apiUrl = baseUrl + 'education-crawler.php?action=get&category=fund';

    document.getElementById('api-url').textContent = apiUrl;
    document.getElementById('result').textContent = '正在请求...';

    try {
        const response = await fetch(apiUrl);
        const text = await response.text();

        document.getElementById('status').textContent = response.status + ' ' + response.statusText;
        document.getElementById('result').textContent = text;

        try {
            const json = JSON.parse(text);
            document.getElementById('json-result').textContent = JSON.stringify(json, null, 2);
            document.getElementById('course-count').textContent = json.data?.courses?.length || 0;
        } catch (e) {
            document.getElementById('json-result').textContent = '无法解析为JSON: ' + e.message;
        }
    } catch (error) {
        document.getElementById('result').textContent = '请求失败: ' + error.message;
    }
}

window.onload = testAPI;
</script>

<p>API地址: <code id="api-url"></code></p>
<p>HTTP状态: <span id="status"></span></p>
<p>课程数量: <span id="course-count"></span></p>

<h3>原始响应:</h3>
<pre id="result" style="background:#f5f5f5; padding:10px; border:1px solid #ddd; max-height:300px; overflow:auto;"></pre>

<h3>JSON格式:</h3>
<pre id="json-result" style="background:#f5f5f5; padding:10px; border:1px solid #ddd; max-height:300px; overflow:auto;"></pre>

<hr>
<p><a href="education.html">返回教育页面</a></p>
</body>
</html>
