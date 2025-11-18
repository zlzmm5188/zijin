#!/bin/bash
# 批量更新所有HTML文件中的JS/CSS版本号

NEW_VERSION="20250117003"
echo "开始批量更新版本号为: $NEW_VERSION"
echo ""

# 更新 config.js 版本号
find . -name "*.html" -type f ! -path "*/backup/*" -print0 | while IFS= read -r -d '' file; do
    if grep -q "config.js?v=" "$file" 2>/dev/null; then
        sed -i "s|config\.js?v=[^\"']*|config.js?v=$NEW_VERSION|g" "$file"
        echo "✓ $(basename "$file") - config.js"
    fi
done

# 更新 app.js 版本号
find . -name "*.html" -type f ! -path "*/backup/*" -print0 | while IFS= read -r -d '' file; do
    if grep -q "app.js?v=" "$file" 2>/dev/null; then
        sed -i "s|app\.js?v=[^\"']*|app.js?v=$NEW_VERSION|g" "$file"
        echo "✓ $(basename "$file") - app.js"
    fi
done

# 更新 login.js 版本号
find . -name "*.html" -type f ! -path "*/backup/*" -print0 | while IFS= read -r -d '' file; do
    if grep -q "login.js?v=" "$file" 2>/dev/null; then
        sed -i "s|login\.js?v=[^\"']*|login.js?v=$NEW_VERSION|g" "$file"
        echo "✓ $(basename "$file") - login.js"
    fi
done

# 更新 styles.css 版本号
find . -name "*.html" -type f ! -path "*/backup/*" -print0 | while IFS= read -r -d '' file; do
    if grep -q "styles.css?v=" "$file" 2>/dev/null; then
        sed -i "s|styles\.css?v=[^\"']*|styles.css?v=$NEW_VERSION|g" "$file"
        echo "✓ $(basename "$file") - styles.css"
    fi
done

echo ""
echo "✅ 版本号更新完成！"
