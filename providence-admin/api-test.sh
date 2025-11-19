#!/bin/bash
# API测试脚本

API_URL="https://api.frevix.top"

echo "========================================="
echo "  PROVIDENCE API 测试"
echo "========================================="
echo ""

echo "测试1：项目列表API"
echo "请求: GET ${API_URL}/fund/project/all"
echo ""
curl -s "${API_URL}/fund/project/all" | python3 -m json.tool 2>/dev/null || curl -s "${API_URL}/fund/project/all"
echo ""
echo ""

echo "测试2：VIP等级列表"
echo "请求: GET ${API_URL}/user/level/list"
echo ""
curl -s "${API_URL}/user/level/list" | python3 -m json.tool 2>/dev/null || curl -s "${API_URL}/user/level/list"
echo ""
echo ""

echo "========================================="
echo "  ✅ 测试完成"
echo "========================================="
echo ""
echo "如果以上都返回JSON数据，说明API配置成功！"
echo ""
