#!/bin/bash
echo "============================================"
echo "  PROVIDENCE 快速安装"  
echo "============================================"
echo ""
echo "请选择安装方式："
echo "1. 自动安装（需要输入MySQL密码）"
echo "2. 生成SQL文件，通过宝塔面板导入"
echo ""
read -p "请选择 (1/2): " choice

if [ "$choice" == "1" ]; then
    read -sp "请输入MySQL root密码: " mysql_pwd
    echo ""
    
    # 创建数据库
    mysql -uroot -p"$mysql_pwd" << EOF
CREATE DATABASE IF NOT EXISTS providence DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'providence'@'localhost' IDENTIFIED BY 'Providence@2024';
GRANT ALL PRIVILEGES ON providence.* TO 'providence'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    if [ $? -eq 0 ]; then
        echo "✅ 数据库创建成功"
        
        # 导入SQL
        mysql -uroot -p"$mysql_pwd" providence < database/install.sql
        
        if [ $? -eq 0 ]; then
            echo "✅ 数据导入成功"
            echo ""
            echo "============================================"
            echo "  安装完成！"
            echo "============================================"
            echo "默认管理员: admin"
            echo "默认密码: admin123"
            echo "API地址: http://$(hostname -I | awk '{print $1}')/providence-admin/api"
        else
            echo "❌ 数据导入失败"
        fi
    else
        echo "❌ 数据库创建失败"
    fi
    
elif [ "$choice" == "2" ]; then
    echo ""
    echo "============================================"
    echo "  通过宝塔面板手动导入"
    echo "============================================"
    echo ""
    echo "步骤："
    echo "1. 登录宝塔面板"
    echo "2. 数据库 -> 添加数据库"
    echo "   - 数据库名: providence"
    echo "   - 用户名: providence"
    echo "   - 密码: Providence@2024"
    echo ""
    echo "3. 点击 providence 数据库 -> 导入"
    echo "4. 选择文件: /www/wwwroot/providence-admin/database/install.sql"
    echo "5. 点击导入"
    echo ""
    echo "SQL文件位置: $(pwd)/database/install.sql"
    echo ""
else
    echo "无效选择"
fi
