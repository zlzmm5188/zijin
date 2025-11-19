<?php
/**
 * AI聊天接口 - Providence智能顾问（重构版）
 * POST /api/ai/chat
 * 响应格式：code: 200（匹配前端）
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 加载服务类
require_once __DIR__ . '/AIService.php';
require_once __DIR__ . '/DataService.php';
require_once __DIR__ . '/GPTService.php';
require_once __DIR__ . '/ResponseFormatter.php';

header('Content-Type: application/json; charset=utf-8');

// 接收POST数据
$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$conversationId = trim($input['conversation_id'] ?? '');

if (empty($message)) {
    echo json_encode(['code' => 400, 'message' => '消息不能为空', 'data' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

// 生成会话ID
if (empty($conversationId)) {
    $conversationId = 'conv-' . time() . '-' . uniqid();
}

try {
    $db = Database::getInstance();

    // 获取Token（支持多种方式，兼容不同前端实现）
    $token = '';
    
    // 1. 从HTTP头获取（PHP中HTTP头会转换为大写并加HTTP_前缀）
    // 前端发送 'token' -> PHP中为 $_SERVER['HTTP_TOKEN']
    // 前端发送 'Authorization' -> PHP中为 $_SERVER['HTTP_AUTHORIZATION']
    if (function_exists('getallheaders')) {
        $allHeaders = getallheaders();
        if ($allHeaders) {
            // 检查各种可能的header名称（大小写不敏感）
            foreach ($allHeaders as $key => $value) {
                $lowerKey = strtolower($key);
                if ($lowerKey === 'token' || $lowerKey === 'authorization') {
                    $token = $value;
                    break;
                }
            }
        }
    }
    
    // 如果getallheaders()不可用，使用$_SERVER
    if (empty($token)) {
        if (isset($_SERVER['HTTP_TOKEN'])) {
            $token = $_SERVER['HTTP_TOKEN'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
        }
    }

    // 2. 从POST数据获取（前端可能放在body中）
    if (empty($token) && isset($input['token'])) {
        $token = $input['token'];
    }

    // 3. 从GET参数获取（备用）
    if (empty($token) && isset($_GET['token'])) {
        $token = $_GET['token'];
    }

    // 清理token格式（移除Bearer前缀）
    $token = str_replace('Bearer ', '', trim($token));

    $userId = 0;
    $userData = null;

    // 验证token并获取用户ID
    if (!empty($token)) {
        try {
            $authData = Auth::verifyToken($token);
            if ($authData && isset($authData['user_id'])) {
                $userId = (int)$authData['user_id'];
                error_log("[AI Chat] Token验证成功，用户ID: {$userId}, Token长度: " . strlen($token));
            } else {
                error_log("[AI Chat] Token验证失败，返回数据: " . json_encode($authData));
            }
        } catch (Exception $e) {
            error_log("[AI Chat] Token验证异常: " . $e->getMessage());
        }
    } else {
        error_log("[AI Chat] 未提供Token - 检查了HTTP头、POST和GET参数");
    }

    // 创建AI服务实例
    $aiService = new AIService($db);

    // 保存用户消息
    $aiService->saveMessage($conversationId, $userId, 'user', $message, null, null);

    // 处理消息并生成回复
    $response = $aiService->processMessage($message, $userId, $conversationId);

    // 更新会话
    $aiService->updateConversation($conversationId, $userId);

    // 返回响应（code: 200格式）
    echo json_encode([
        'code' => 200,
        'message' => '处理成功',
        'data' => [
            'message' => $response['message'],
            'intent' => $response['intent'],
            'confidence' => $response['confidence'],
            'actionButtons' => $response['actionButtons'],
            'conversation_id' => $conversationId,
            'timestamp' => time()
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('[AI Chat] Error: ' . $e->getMessage());
    error_log('[AI Chat] Trace: ' . $e->getTraceAsString());
    
    // 确保输出JSON格式，即使出错
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 500,
        'message' => 'AI服务暂时不可用: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    error_log('[AI Chat] Fatal Error: ' . $e->getMessage());
    error_log('[AI Chat] Trace: ' . $e->getTraceAsString());
    
    // 确保输出JSON格式，即使出错
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 500,
        'message' => 'AI服务发生严重错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
