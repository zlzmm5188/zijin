# 定时任务说明

## 任务列表

### 1. daily-earnings.php - 每日收益发放
**功能**: 自动为所有进行中的投资订单发放每日收益

**执行时间**: 每天 00:01

**配置方法**（宝塔面板）:
1. 登录宝塔面板
2. 计划任务 -> 添加任务
3. 任务类型: Shell脚本
4. 任务名称: PROVIDENCE每日收益发放
5. 执行周期: 每天 0点1分
6. 脚本内容:
```bash
php /www/wwwroot/providence-admin/cron/daily-earnings.php >> /www/wwwroot/providence-admin/logs/earnings.log 2>&1
```

### 2. vip-upgrade.php - VIP自动升级
**功能**: 根据用户累计投资额自动升级VIP等级

**执行时间**: 每天 01:00

**配置方法**:
```bash
php /www/wwwroot/providence-admin/cron/vip-upgrade.php >> /www/wwwroot/providence-admin/logs/vip-upgrade.log 2>&1
```

## 手动测试

```bash
# 测试收益发放
cd /www/wwwroot/providence-admin/cron
php daily-earnings.php

# 测试VIP升级
php vip-upgrade.php
```

## 日志文件

- 收益发放日志: `/www/wwwroot/providence-admin/logs/earnings.log`
- VIP升级日志: `/www/wwwroot/providence-admin/logs/vip-upgrade.log`

## 注意事项

1. 确保脚本有执行权限: `chmod +x *.php`
2. 定期检查日志文件
3. 建议先手动测试，确认无误后再配置定时任务
