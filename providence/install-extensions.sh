#!/bin/bash
# Cursor/VS Code 插件批量安装脚本

echo "=========================================="
echo "🔌 Cursor/VS Code 插件批量安装"
echo "=========================================="
echo ""

# 检查是否有 code 或 cursor 命令
if command -v code &> /dev/null; then
    CMD="code"
elif command -v cursor &> /dev/null; then
    CMD="cursor"
else
    echo "❌ 未找到 code 或 cursor 命令"
    echo ""
    echo "请手动安装插件："
    echo "1. 按 Cmd/Ctrl + Shift + X 打开扩展面板"
    echo "2. 搜索并安装以下插件："
    echo ""
    exit 1
fi

echo "✅ 找到命令: $CMD"
echo ""

# 插件列表（按优先级排序）
EXTENSIONS=(
    # 第一优先级
    "bmewburn.vscode-intelephense-client"  # PHP Intelephense
    "dbaeumer.vscode-eslint"                # ESLint
    "esbenp.prettier-vscode"                # Prettier
    "formulahendry.auto-rename-tag"         # Auto Rename Tag
    
    # 第二优先级
    "usernamehw.errorlens"                  # Error Lens
    "christian-kohler.path-intellisense"   # Path Intellisense
    "cweijan.vscode-mysql-client2"         # MySQL
    "timonwong.shellcheck"                  # ShellCheck
    
    # 第三优先级
    "yzhang.markdown-all-in-one"            # Markdown All in One
    "eamodio.gitlens"                       # GitLens
    "pranaygp.vscode-css-peek"              # CSS Peek
    "mads-hartmann.bash-ide-vscode"         # Bash IDE
    "streetsidesoftware.code-spell-checker" # Code Spell Checker
    "gruntfuggly.todo-tree"                 # Todo Tree
    "felixfbecker.php-intellisense"         # PHP IntelliSense
    "eriklynd.json-tools"                   # JSON Tools
)

echo "📦 准备安装 ${#EXTENSIONS[@]} 个插件..."
echo ""

# 安装插件
INSTALLED=0
FAILED=0

for ext in "${EXTENSIONS[@]}"; do
    echo -n "安装: $ext ... "
    if $CMD --install-extension "$ext" --force &> /dev/null; then
        echo "✅"
        ((INSTALLED++))
    else
        echo "❌"
        ((FAILED++))
    fi
done

echo ""
echo "=========================================="
echo "✅ 安装完成！"
echo "   成功: $INSTALLED 个"
echo "   失败: $FAILED 个"
echo "=========================================="
echo ""
echo "💡 提示："
echo "   如果安装失败，请手动在扩展市场中搜索插件名称安装"
echo "   按 Cmd/Ctrl + Shift + X 打开扩展面板"
