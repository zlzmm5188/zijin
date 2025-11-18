#!/bin/bash
# Cursor 索引状态检查脚本

echo "=========================================="
echo "🔍 Cursor 代码库索引配置检查"
echo "=========================================="
echo ""

# 检查配置文件
echo "📁 检查配置文件..."
if [ -f ".vscode/settings.json" ]; then
    echo "✅ .vscode/settings.json 存在"
    echo "   内容："
    cat .vscode/settings.json | sed 's/^/   /'
else
    echo "❌ .vscode/settings.json 不存在"
fi

echo ""
echo "📁 检查忽略文件..."
if [ -f ".cursorignore" ]; then
    echo "✅ .cursorignore 存在"
    echo "   已排除的文件类型："
    grep -E "^[^#]" .cursorignore | head -10 | sed 's/^/   /'
else
    echo "❌ .cursorignore 不存在"
fi

echo ""
echo "📊 项目文件统计..."
echo "   JavaScript 文件: $(find . -name '*.js' -not -path '*/backups/*' -not -path '*/node_modules/*' | wc -l)"
echo "   HTML 文件: $(find . -name '*.html' -not -path '*/backups/*' | wc -l)"
echo "   CSS 文件: $(find . -name '*.css' -not -path '*/backups/*' | wc -l)"
echo "   Markdown 文件: $(find . -name '*.md' -not -path '*/backups/*' | wc -l)"

echo ""
echo "=========================================="
echo "✅ 配置完成！"
echo "=========================================="
echo ""
echo "📝 下一步操作："
echo "   1. 在 Cursor 中按 Cmd/Ctrl + , 打开设置"
echo "   2. 搜索 'codebase indexing'"
echo "   3. 确保 'Enable Codebase Indexing' 已勾选"
echo "   4. 重启 Cursor 或点击右下角的 'Start indexing'"
echo ""
