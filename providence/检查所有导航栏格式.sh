#!/bin/bash
# 检查所有主页面的导航栏格式一致性

echo "检查5个主页面的导航栏..."
echo ""

FILES=("index.html" "market.html" "projects.html" "messages.html" "profile.html")

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "━━━ $file ━━━"
        # 提取导航栏结构
        sed -n '/<nav class="tabbar"/,/<\/nav>/p' "$file" | grep -E "class=\"tab|class=\"ico|class=\"txt" | head -15
        echo ""
    fi
done

echo "✅ 检查完成"
