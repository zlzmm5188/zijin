#!/bin/bash
# 清除缓存脚本

echo "清除Providence后台缓存..."

# 添加版本号到HTML文件
VERSION=$(date +%s)

echo "当前版本号: $VERSION"

# 方法1: 创建带版本号的重定向
cat > /www/wwwroot/providence-admin/admin/index.html.new << EOF
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="0;url=index.html?v=$VERSION">
<script>location.href='index.html?v=$VERSION';</script>
</head>
<body>Loading...</body>
</html>
EOF

echo "✅ 版本号已添加: v=$VERSION"
echo ""
echo "请访问: https://houtai.frevix.top/?v=$VERSION"
echo ""
echo "或按 Ctrl + Shift + R 强制刷新"
