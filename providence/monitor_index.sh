#!/bin/bash
# 监控index.html，如果为空则自动恢复
INDEX_FILE="/www/wwwroot/f.abcmall.one/providence/index.html"
BACKUP_DIR="/www/wwwroot/f.abcmall.one/providence/backups"

if [ ! -s "$INDEX_FILE" ]; then
    echo "[$(date)] 检测到index.html为空，正在恢复..." >> /tmp/index_monitor.log
    LATEST_BACKUP=$(ls -t $BACKUP_DIR/index.html.backup_* 2>/dev/null | head -1)
    if [ -n "$LATEST_BACKUP" ]; then
        cp "$LATEST_BACKUP" "$INDEX_FILE"
        chmod 644 "$INDEX_FILE"
        chown www:www "$INDEX_FILE"
        echo "[$(date)] 已从备份恢复: $LATEST_BACKUP" >> /tmp/index_monitor.log
    fi
fi
