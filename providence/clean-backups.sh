#!/bin/bash
echo "开始清理备份和临时文件..."
echo ""

# 1. 删除备份文件
echo "1. 删除备份文件..."
rm -rf backups-* 2>/dev/null
rm -f *-backup*.html *-backup*.js *-backup*.php 2>/dev/null
rm -f *.bak *.bak-* 2>/dev/null
echo "   ✓ 备份文件已删除"

# 2. 删除old/temp文件
echo "2. 删除old/temp文件..."
rm -f *-old*.html *-old*.js 2>/dev/null
rm -f *-temp*.html *-temp*.js 2>/dev/null
rm -f profile-old-backup.html profile-temp.html 2>/dev/null
rm -f messages-old-*.html 2>/dev/null
echo "   ✓ old/temp文件已删除"

# 3. 删除测试文件
echo "3. 删除测试文件..."
rm -f test-*.html test-*.js 2>/dev/null
rm -f debug-*.html debug-*.js debug-*.php 2>/dev/null
echo "   ✓ 测试文件已删除"

# 4. 删除重复的craft/new/refactored文件
echo "4. 删除重复版本..."
rm -f *-craft.html *-new.html *-refactored.html 2>/dev/null
rm -f *-final.html 2>/dev/null
echo "   ✓ 重复版本已删除"

# 5. 删除多余的kyc文件（保留kyc-verification.js）
echo "5. 清理kyc多余文件..."
rm -f kyc-new.js kyc-verification-clean.js kyc-verification-final.js kyc-verification-full.js kyc-final.js 2>/dev/null
echo "   ✓ kyc多余文件已删除"

# 6. 删除临时脚本
echo "6. 清理临时脚本..."
rm -f *-patch.sh fix-*.sh expand-*.sh batch-*.sh 2>/dev/null
rm -f auto-fix-css.sh js-auto-fix-braces.sh 2>/dev/null
echo "   ✓ 临时脚本已删除"

# 7. 删除旧的总结文档（保留最新的）
echo "7. 清理旧文档..."
find . -maxdepth 1 -name "*REPORT*.txt" -mtime +1 -delete 2>/dev/null
find . -maxdepth 1 -name "*SUMMARY*.txt" -mtime +1 -delete 2>/dev/null
find . -maxdepth 1 -name "BAOTA*.md" -delete 2>/dev/null
echo "   ✓ 旧文档已清理"

echo ""
echo "✅ 清理完成！"
echo ""
echo "统计："
ls -1 *.html 2>/dev/null | wc -l | xargs echo "HTML文件:"
ls -1 *.js 2>/dev/null | wc -l | xargs echo "JS文件:"
ls -1 *.md 2>/dev/null | wc -l | xargs echo "MD文档:"
ls -1 *.sh 2>/dev/null | wc -l | xargs echo "Shell脚本:"
