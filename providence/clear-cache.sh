#!/bin/bash
# 清除前台缓存脚本

VERSION=$(date +%s)

# 给所有JS和CSS文件添加版本号
find /www/wwwroot/providence -name "*.html" -type f -exec sed -i "s/config\.js/config.js?v=$VERSION/g" {} \;
find /www/wwwroot/providence -name "*.html" -type f -exec sed -i "s/app\.js/app.js?v=$VERSION/g" {} \;

echo "✅ 已给所有JS添加版本号: v=$VERSION"
echo ""
echo "现在访问前台应该会加载新的config.js"
