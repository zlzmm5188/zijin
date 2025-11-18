#!/bin/bash
# 检查并修复所有页面的导航栏内联样式

echo "检查导航栏内联样式..."
echo ""

FILES=("index.html" "market.html" "projects.html" "messages.html" "profile.html")

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        if grep -q '<nav class="tabbar"[^>]*style=' "$file"; then
            echo "⚠️  $file - 发现内联样式"
        else
            echo "✅ $file - 无内联样式"
        fi
    fi
done

echo ""
echo "建议：所有页面都应使用纯净的 <nav class=\"tabbar\"> 标签"
echo "不要添加内联样式，让CSS统一控制"
