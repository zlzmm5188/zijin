#!/bin/bash
# Providence API接口同步检查工具
# 检查前端API调用与后端接口、数据库的同步状态

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

FRONTEND_DIR="/www/wwwroot/f.abcmall.one/providence"
BACKEND_DIR="/www/wwwroot/gmo/gmo.com/app"
DB_HOST="118.107.19.74"
DB_USER="exchange"
DB_PASS="f86emm2t6jHTwp8L"
DB_NAME="exchange"

echo "================================================"
echo "Providence API接口同步检查"
echo "时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================"
echo ""

ISSUES=0

# ========== 1. 提取前端API调用 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📱 前端API调用分析${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

cd "$FRONTEND_DIR"

# 提取所有API端点
echo "正在扫描前端API调用..."
API_CALLS=$(grep -rh "apiRequest\|fetch.*api\|API\." *.js *.html 2>/dev/null | \
    grep -o "'/[a-z/]*'" | \
    sort | uniq)

echo "发现的API端点："
echo "$API_CALLS" | sed 's/^/  /'
echo ""

# 保存到临时文件
echo "$API_CALLS" > /tmp/frontend-apis.txt

# ========== 2. 检查后端控制器 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🖥️  后端控制器检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ -d "$BACKEND_DIR" ]; then
    cd "$BACKEND_DIR"

    # 查找所有Controller文件
    CONTROLLERS=$(find . -name "*Controller.php" -o -name "*.php" | grep -i controller)

    echo "后端控制器文件："
    echo "$CONTROLLERS" | head -10 | sed 's/^/  /'
    echo ""

    # 提取后端路由
    echo "后端已定义的路由："
    grep -rh "public function" api/controller/*.php 2>/dev/null | \
        grep -o "function [a-zA-Z_]*" | \
        sed 's/function /  /' | \
        sort | uniq | head -20
else
    echo -e "${RED}✗ 后端目录不存在: $BACKEND_DIR${NC}"
    ((ISSUES++))
fi

echo ""

# ========== 3. 数据库表结构检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🗄️  数据库表结构检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if command -v mysql &> /dev/null; then
    # 获取所有表
    TABLES=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | tail -n +2)

    if [ $? -eq 0 ]; then
        table_count=$(echo "$TABLES" | wc -l)
        echo -e "${GREEN}✓ 数据库连接成功${NC}"
        echo "数据库: $DB_NAME"
        echo "表数量: $table_count"
        echo ""

        echo "主要数据表："
        echo "$TABLES" | grep -E "user|fund|project|vip|team|kyc" | sed 's/^/  /' | head -15
        echo ""

        # 检查关键表是否存在
        for table in fa_user fa_fund fa_fund_project fa_user_team fa_vip_config; do
            if echo "$TABLES" | grep -q "^$table$"; then
                echo -e "${GREEN}✓ 表 $table 存在${NC}"
            else
                echo -e "${YELLOW}⚠ 表 $table 不存在${NC}"
                ((ISSUES++))
            fi
        done
    else
        echo -e "${RED}✗ 数据库连接失败${NC}"
        echo "请检查数据库配置"
        ((ISSUES++))
    fi
else
    echo -e "${YELLOW}⚠ mysql命令不可用，跳过数据库检查${NC}"
fi

echo ""

# ========== 4. API接口对照 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🔗 前后端接口对照${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# 检查前端调用的API是否在后端实现
echo "检查以下API端点的实现状态："
echo ""

# 常用API端点清单
declare -A api_endpoints=(
    ["/user/user/index"]="用户信息"
    ["/user/user/invite"]="邀请信息"
    ["/user/team/team"]="团队列表"
    ["/fund/project/all"]="项目列表"
    ["/fund/project/detail"]="项目详情"
    ["/api/kyc/status"]="实名状态"
    ["/api/trial/status"]="体验金状态"
    ["/api/trial/claim"]="领取体验金"
    ["/admin/vip/config"]="VIP配置"
    ["/login/reg/account"]="用户注册"
)

for endpoint in "${!api_endpoints[@]}"; do
    desc="${api_endpoints[$endpoint]}"

    # 检查后端是否有对应的实现
    if [ -d "$BACKEND_DIR" ]; then
        # 提取路径的controller和方法
        controller=$(echo "$endpoint" | cut -d'/' -f2 | sed 's/^./\U&/')
        method=$(echo "$endpoint" | cut -d'/' -f3)

        # 搜索后端文件
        if find "$BACKEND_DIR" -name "${controller}.php" 2>/dev/null | grep -q .; then
            controller_file=$(find "$BACKEND_DIR" -name "${controller}.php" 2>/dev/null | head -1)
            if grep -q "function $method" "$controller_file" 2>/dev/null; then
                echo -e "${GREEN}✓ $endpoint${NC} ($desc) - 后端已实现"
            else
                echo -e "${YELLOW}⚠ $endpoint${NC} ($desc) - 后端方法缺失"
                ((ISSUES++))
            fi
        else
            echo -e "${YELLOW}⚠ $endpoint${NC} ($desc) - 后端控制器缺失"
            ((ISSUES++))
        fi
    fi
done

echo ""

# ========== 5. 数据表字段检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📊 数据表字段检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if command -v mysql &> /dev/null; then
    # 检查fa_user表的关键字段
    echo "检查 fa_user 表字段："
    required_fields="id username mobile realname money level kyc_status invite_code"

    for field in $required_fields; do
        result=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
            -e "SHOW COLUMNS FROM fa_user LIKE '$field';" 2>/dev/null | tail -n +2)

        if [ ! -z "$result" ]; then
            echo -e "${GREEN}  ✓ $field${NC}"
        else
            echo -e "${RED}  ✗ $field (缺失)${NC}"
            ((ISSUES++))
        fi
    done
fi

echo ""

# ========== 6. API响应格式检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📡 API响应格式检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

echo "检查前端是否正确处理API响应..."

cd "$FRONTEND_DIR"

# 检查是否有正确的错误处理
if grep -rq "response.code\|response.msg\|data.code" *.js 2>/dev/null; then
    echo -e "${GREEN}✓ 前端有API响应处理${NC}"
else
    echo -e "${YELLOW}⚠ 前端缺少统一的API响应处理${NC}"
    ((ISSUES++))
fi

# 检查是否有token处理
if grep -rq "token\|authorization" *.js config.js 2>/dev/null; then
    echo -e "${GREEN}✓ 前端有token处理${NC}"
else
    echo -e "${RED}✗ 前端缺少token处理${NC}"
    ((ISSUES++))
fi

echo ""

# ========== 总结 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}检查结果总结${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ $ISSUES -eq 0 ]; then
    echo -e "${GREEN}✅ 前后端接口同步正常！${NC}"
    exit 0
else
    echo -e "${YELLOW}发现 $ISSUES 个需要关注的问题${NC}"
    echo ""
    echo "建议："
    echo "1. 检查后端是否实现了所有前端调用的接口"
    echo "2. 确认数据库表结构与前端需求一致"
    echo "3. 测试所有API接口是否正常返回"
    exit 1
fi
