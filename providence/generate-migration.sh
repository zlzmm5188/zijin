#!/bin/bash
# Prisma Migration 生成脚本

echo "================================================"
echo "Providence Prisma Migration 生成"
echo "================================================"
echo ""

# 检查prisma是否安装
if ! command -v prisma &> /dev/null; then
    echo "Prisma未安装，正在安装..."
    npm install -g prisma @prisma/client
fi

cd prisma

# 1. 生成Prisma Client
echo "1. 生成Prisma Client..."
npx prisma generate

# 2. 创建迁移
echo ""
echo "2. 创建数据库迁移..."
npx prisma migrate dev --name init_providence_schema

# 3. 查看迁移状态
echo ""
echo "3. 迁移状态："
npx prisma migrate status

echo ""
echo "✅ Migration生成完成"
echo ""
echo "下一步："
echo "1. 检查 prisma/migrations/ 目录"
echo "2. 审查生成的SQL文件"
echo "3. 运行 npx prisma migrate deploy 部署到生产环境"
