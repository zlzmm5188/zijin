<?php
/**
 * AI聊天历史接口
 * GET /api/ai/history
 * 
 * 功能：获取指定会话的聊天历史
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

$conversationId = $_GET['conversation_id'] ?? '';
$limit = (int)($_GET['limit'] ?? 50);

if (empty($conversationId)) {
    Response::error('会话ID不能为空');
}

try {
    $db = Database::getInstance();
    
    // 查询聊天记录
    $messages = $db->fetchAll(
        "SELECT id, message_type as type, message_content as content, 
                user_intent as intent, created_at, 
                UNIX_TIMESTAMP(created_at) as timestamp
         FROM pv_ai_chat_messages 
         WHERE conversation_id = ? 
         ORDER BY id ASC 
         LIMIT ?",
        [$conversationId, $limit]
    );
    
    // 获取消息总数
    $total = $db->fetchOne(
        "SELECT COUNT(*) as count FROM pv_ai_chat_messages WHERE conversation_id = ?",
        [$conversationId]
    );
    
    Response::success([
        'messages' => $messages,
        'total' => (int)($total['count'] ?? 0),
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    error_log('[AI History] Error: ' . $e->getMessage());
    Response::error('获取聊天历史失败');
}
