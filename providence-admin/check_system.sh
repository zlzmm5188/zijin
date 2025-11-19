#!/bin/bash

# Providence 后台系统部署检查脚本
# 用途：快速检查系统完整性和潜在问题

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Providence 后台系统完整性检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 检查计数器
total_checks=0
passed_checks=0
failed_checks=0
warning_checks=0

# 检查函数
check_file() {
    local file=$1
    local desc=$2
    total_checks=$((total_checks + 1))

    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $desc: $file"
        passed_checks=$((passed_checks + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $desc: $file ${RED}[缺失]${NC}"
        failed_checks=$((failed_checks + 1))
        return 1
    fi
}

check_dir() {
    local dir=$1
    local desc=$2
    total_checks=$((total_checks + 1))

    if [ -d "$dir" ]; then
        local count=$(find "$dir" -type f | wc -l)
        echo -e "${GREEN}✓${NC} $desc: $dir (${count}个文件)"
        passed_checks=$((passed_checks + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $desc: $dir ${RED}[目录不存在]${NC}"
        failed_checks=$((failed_checks + 1))
        return 1
    fi
}

echo "【1】检查核心配置文件"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_file "config/bootstrap.php" "初始化文件"
check_file "config/Database.php" "数据库类"
check_file "config/Auth.php" "认证类"
check_file "config/Response.php" "响应类"
check_file "config/AuditLog.php" "审计日志类"
echo ""

echo "【2】检查API路由"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_file "api/index.php" "API路由入口"
echo ""

echo "【3】检查核心API接口"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_file "api/admin/stats.php" "统计API"
check_file "api/admin/users.php" "用户列表API"
check_file "api/admin/user-detail.php" "用户详情API"
check_file "api/admin/kyc-approve.php" "KYC审核API"
check_file "api/admin/recharges.php" "充值列表API"
check_file "api/admin/recharge-approve.php" "充值审核通过API"
check_file "api/admin/recharge-reject.php" "充值审核拒绝API"
check_file "api/admin/withdrawals.php" "提现列表API"
check_file "api/admin/withdraw-approve.php" "提现审核通过API"
check_file "api/admin/withdraw-reject.php" "提现审核拒绝API"
check_file "api/admin/wallet-logs.php" "钱包流水API"
check_file "api/admin/login-logs.php" "登录历史API"
check_file "api/admin/ribao-users.php" "日利宝用户API"
check_file "api/admin/ribao-config.php" "日利宝配置API"
echo ""

echo "【4】检查前端页面"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_file "admin/index.html" "后台首页"
check_file "admin/dashboard.html" "仪表盘"
check_file "admin/users.html" "用户列表页"
check_file "admin/kyc-review.html" "KYC审核页"
check_file "admin/recharges.html" "充值审核页"
check_file "admin/withdrawals.html" "提现审核页"
check_file "admin/orders.html" "订单列表页"
check_file "admin/projects.html" "项目列表页"
check_file "admin/wallet-logs.html" "钱包流水页"
check_file "admin/login-logs.html" "登录历史页"
check_file "admin/ribao-management.html" "日利宝管理页"
echo ""

echo "【5】检查目录结构"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_dir "admin" "前端页面目录"
check_dir "api/admin" "管理员API目录"
check_dir "api/user" "用户API目录"
check_dir "api/pay" "支付API目录"
check_dir "config" "配置目录"
echo ""

echo "【6】检查文档"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_file "API_DOCUMENTATION.md" "API接口文档"
check_file "SYSTEM_CHECK_REPORT.md" "系统检查报告"
check_file "fix_schema.sql" "数据库修复脚本"
check_file "create_login_logs_table.sql" "登录日志表创建脚本"
echo ""

echo "【7】检查数据库表（需要手动确认）"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
total_checks=$((total_checks + 2))
echo -e "${YELLOW}⚠${NC} 请手动确认以下表是否存在："
echo "   - user_login_logs （登录历史表）"
echo "   - system_config （系统配置表）"
echo ""
echo -e "${YELLOW}如果不存在，请执行：${NC}"
echo -e "   mysql -u root -p providence < create_login_logs_table.sql"
echo -e "   mysql -u root -p providence < create_system_config_table.sql"
warning_checks=$((warning_checks + 2))
echo ""

echo "【8】检查权限（需要手动确认）"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
total_checks=$((total_checks + 1))
echo -e "${YELLOW}⚠${NC} 请确认以下目录具有写权限："
echo "   - api/ （需要执行权限）"
echo "   - config/ （需要读取权限）"
echo ""
warning_checks=$((warning_checks + 1))
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "检查结果汇总"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "总检查项: ${total_checks}"
echo -e "${GREEN}通过: ${passed_checks}${NC}"
echo -e "${RED}失败: ${failed_checks}${NC}"
echo -e "${YELLOW}警告: ${warning_checks}${NC}"
echo ""

if [ $failed_checks -eq 0 ]; then
    echo -e "${GREEN}✅ 系统文件完整性检查通过！${NC}"
    echo ""
    echo "下一步操作："
    echo "1. 确认数据库表已创建"
    echo "2. 执行 fix_schema.sql 修复数据库Schema"
    echo "3. 测试API接口"
    echo "4. 测试前端页面"
else
    echo -e "${RED}❌ 发现 ${failed_checks} 个缺失文件，请检查！${NC}"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "详细信息请查看："
echo "  - API_DOCUMENTATION.md （API接口文档）"
echo "  - SYSTEM_CHECK_REPORT.md （系统检查报告）"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
