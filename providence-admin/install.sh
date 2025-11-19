#!/bin/bash
# PROVIDENCE 后台系统安装脚本

echo "========================================="
echo "  PROVIDENCE 后台管理系统安装向导"
echo "========================================="
echo ""

# 检查MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ 未检测到MySQL，请先安装MySQL"
    exit 1
fi

# 获取MySQL密码
read -sp "请输入MySQL root密码: " MYSQL_PASSWORD
echo ""

# 创建数据库和用户
echo "正在创建数据库..."
mysql -uroot -p"$MYSQL_PASSWORD" << EOF
CREATE DATABASE IF NOT EXISTS providence DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'providence'@'localhost' IDENTIFIED BY 'Providence@2024';
GRANT ALL PRIVILEGES ON providence.* TO 'providence'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo "✅ 数据库创建成功"
else
    echo "❌ 数据库创建失败"
    exit 1
fi

# 导入数据库结构
echo "正在导入数据库结构..."
mysql -uroot -p"$MYSQL_PASSWORD" providence < database/install.sql

if [ $? -eq 0 ]; then
    echo "✅ 数据库导入成功"
else
    echo "❌ 数据库导入失败"
    exit 1
fi

# 设置权限
echo "正在设置文件权限..."
chmod -R 755 .
chmod -R 777 logs/
chmod -R 777 uploads/

echo ""
echo "========================================="
echo "  ✅ 安装完成！"
echo "========================================="
echo ""
echo "管理后台地址: http://YOUR_DOMAIN/providence-admin/admin/"
echo "默认管理员: admin"
echo "默认密码: admin123"
echo ""
echo "请修改 config/database.php 中的数据库配置"
echo "请修改 config/app.php 中的应用配置"
echo ""
