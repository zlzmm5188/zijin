#!/bin/bash

# Providence API Scanner - 一键运行和查看脚本
# 使用方法: bash run-scan-and-view.sh

echo "════════════════════════════════════════════════════════════════"
echo "   🚀 Providence API 扫描工具 - 一键运行"
echo "════════════════════════════════════════════════════════════════"
echo ""

# 检查 Node.js 是否安装
if ! command -v node &> /dev/null; then
    echo "❌ 错误: 未找到 Node.js"
    echo "请先安装 Node.js: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js 版本: $(node --version)"
echo ""

# 运行扫描器
echo "🔍 正在运行 API 扫描器..."
echo "---"
node api-validation-scanner.js
echo ""

# 显示结果位置
echo "════════════════════════════════════════════════════════════════"
echo "   📊 扫描完成！"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "📄 生成的文件："
echo "  1. API_VALIDATION_REPORT.md        - 完整技术报告 (1731行)"
echo "  2. API错误总结.md                  - 中文执行摘要"
echo "  3. API_INTEGRATION_FIX_GUIDE.md    - 修复指南"
echo "  4. README_API_SCAN.md              - 使用说明"
echo "  5. view-scan-results.html          - 可视化结果页面 ⭐"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo "   🌐 查看扫描结果"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "方式1 - 在浏览器中查看（推荐）："
echo "  用浏览器打开: $(pwd)/view-scan-results.html"
echo ""
echo "方式2 - 命令行查看中文摘要："
echo "  cat API错误总结.md"
echo ""
echo "方式3 - 查看完整报告："
echo "  cat API_VALIDATION_REPORT.md | less"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "📊 扫描结果摘要："
echo ""
cat << 'EOF'
  ├─ 前端 API 调用:     41 个
  ├─ 后端 API 实现:     113 个
  ├─ 🔴 严重错误:       14 个
  └─ ⚠️  警告:          294 个

🎯 关键问题:
  1. 密码重置 API 缺失 (6个接口)     - 🔴 HIGH
  2. 日利宝转入路径错误              - 🔴 HIGH
  3. 返回码检查不统一 (200+处)       - 🔴 HIGH
  4. 积分兑换历史 API 缺失           - 🟡 MEDIUM

💡 下一步:
  • 在浏览器中打开 view-scan-results.html 查看详细结果
  • 参考 API_INTEGRATION_FIX_GUIDE.md 进行修复
  • 修复后重新运行此脚本验证改进
EOF
echo ""
echo "════════════════════════════════════════════════════════════════"
echo "✅ 完成！"
echo "════════════════════════════════════════════════════════════════"
