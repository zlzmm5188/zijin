<?php
/**
 * 审计日志服务类
 */
class AuditLog {
    /**
     * 记录审计日志
     * @param string $module 模块：member/project/finance/risk/promotion
     * @param string $action 操作
     * @param string $operatorType user/admin/system
     * @param int $operatorId 操作者ID
     * @param string $targetType 目标类型
     * @param int $targetId 目标ID
     * @param array $beforeData 变更前数据
     * @param array $afterData 变更后数据
     */
    public static function log($module, $action, $operatorType, $operatorId, $targetType = null, $targetId = null, $beforeData = null, $afterData = null) {
        $db = Database::getInstance();

        return $db->insert('audit_log', [
            'module' => $module,
            'action' => $action,
            'actor_type' => $operatorType,  // 修正：operator_type -> actor_type
            'actor_id' => $operatorId,      // 修正：operator_id -> actor_id
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_data' => $beforeData ? json_encode($beforeData, JSON_UNESCAPED_UNICODE) : null,
            'after_data' => $afterData ? json_encode($afterData, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
