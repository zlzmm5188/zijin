<?php
/**
 * AI服务核心类 - 统一管理AI回复逻辑
 */
class AIService {
    private $db;
    private $dataService;
    private $gptService;
    private $responseFormatter;

    public function __construct($db) {
        $this->db = $db;
        $this->dataService = new DataService($db);
        $this->gptService = new GPTService();
        $this->responseFormatter = new ResponseFormatter();
    }

    /**
     * 处理用户消息
     */
    public function processMessage($message, $userId, $conversationId) {
        // 1. 获取用户数据（确保数据一致性）
        $userData = $userId > 0 ? $this->dataService->getUserData($userId) : null;

        // 2. 识别用户意图
        $intent = $this->recognizeIntent($message);

        // 3. 检查是否需要登录
        if ($intent && $intent['requires_auth'] == 1 && $userId == 0) {
            return $this->handleRequireLogin($conversationId);
        }

        // 4. 生成回复
        return $this->generateResponse($message, $userData, $intent, $userId, $conversationId);
    }

    /**
     * 识别用户意图
     */
    private function recognizeIntent($message) {
        $message = mb_strtolower($message);
        $sql = "SELECT * FROM pv_ai_knowledge_base WHERE status = 1 ORDER BY priority DESC";
        $knowledgeList = $this->db->fetchAll($sql);

        foreach ($knowledgeList as $item) {
            // 关键词匹配
            $keywords = json_decode($item['keywords'], true);
            if ($keywords) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos($message, $keyword) !== false) {
                        return $item;
                    }
                }
            }

            // 正则匹配
            if (!empty($item['question_pattern'])) {
                if (@preg_match('/'. $item['question_pattern'] . '/ui', $message)) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * 生成回复
     */
    private function generateResponse($message, $userData, $intent, $userId, $conversationId) {
        // 数据查询类别（必须使用实际数据）
        $dataQueryCategories = ['balance', 'investment', 'vip', 'ribao', 'team'];
        $isDataQuery = $intent && in_array($intent['category'], $dataQueryCategories);

        // 对于数据查询，强制使用实际数据
        if ($isDataQuery && $userData) {
            return $this->handleDataQuery($intent, $userData, $userId, $conversationId);
        }

        // 非数据查询，使用GPT生成人性化回复
        return $this->handleGeneralQuery($message, $userData, $intent, $userId, $conversationId);
    }

    /**
     * 处理数据查询（余额、投资等）
     */
    private function handleDataQuery($intent, $userData, $userId, $conversationId) {
        // 1. 生成卡片格式的数据展示（使用实际数据）
        $cardHtml = $this->responseFormatter->formatDataCard($intent, $userData);

        // 2. 尝试用GPT生成简短的人性化开头
        $greeting = '';
        try {
            $greeting = $this->gptService->generateGreeting($intent, $userData);
            // 清理greeting，移除可能的HTML标签（GPT不应该返回HTML）
            $greeting = strip_tags($greeting);
            $greeting = trim($greeting);
        } catch (Exception $e) {
            error_log('[GPT Greeting Error] ' . $e->getMessage());
        }

        // 3. 组合回复（确保卡片HTML不被转义）
        if ($greeting && strlen($greeting) > 5) {
            // 问候语转义后放在文本消息div中，卡片HTML直接拼接（不转义）
            $aiMessage = '<div class="ai-text-message"><p>' . htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') . '</p></div>' . $cardHtml;
        } else {
            // 只有卡片，不包裹在文本消息div中
            $aiMessage = $cardHtml;
        }

        // 4. 生成操作按钮
        $actionButtons = $this->responseFormatter->generateActionButtons($intent, $userData);

        // 5. 保存消息
        $this->saveMessage($conversationId, $userId, 'ai', $aiMessage, $intent['category'], 95);

        return [
            'message' => $aiMessage,
            'intent' => $intent['category'],
            'confidence' => 95,
            'actionButtons' => $actionButtons
        ];
    }

    /**
     * 处理一般查询（使用GPT）
     */
    private function handleGeneralQuery($message, $userData, $intent, $userId, $conversationId) {
        $aiMessage = null;
        $confidence = 85;
        $actionButtons = [];

        // 尝试使用GPT生成回复
        try {
            $gptResponse = $this->gptService->generateResponse($message, $userData, $intent, $userId);
            if ($gptResponse && !empty($gptResponse['message'])) {
                $aiMessage = $gptResponse['message'];
                $confidence = 95;
                $actionButtons = $gptResponse['actionButtons'] ?? $this->responseFormatter->generateActionButtons($intent, $userData);
            }
        } catch (Exception $e) {
            error_log('[GPT Error] ' . $e->getMessage());
        }

        // GPT失败时降级到知识库
        if (empty($aiMessage)) {
            if ($intent) {
                $aiMessage = $this->responseFormatter->formatTemplate($intent, $userData);
                $confidence = 90;
            } else {
                $aiMessage = $this->getDefaultResponse();
                $confidence = 50;
            }
            $actionButtons = $this->responseFormatter->generateActionButtons($intent, $userData);
        }

        // 保存消息
        $this->saveMessage($conversationId, $userId, 'ai', $aiMessage, $intent['category'] ?? null, $confidence);

        return [
            'message' => $aiMessage,
            'intent' => $intent['category'] ?? 'unknown',
            'confidence' => $confidence,
            'actionButtons' => $actionButtons
        ];
    }

    /**
     * 处理需要登录的情况
     */
    private function handleRequireLogin($conversationId) {
        $aiMessage = "抱歉，查询此信息需要先登录。请点击下方按钮登录。";
        $actionButtons = [
            ['text' => '🔐 去登录', 'action' => 'navigate:login.html', 'type' => 'primary']
        ];

        $this->saveMessage($conversationId, 0, 'ai', $aiMessage, 'require_login', 95);

        return [
            'message' => $aiMessage,
            'intent' => 'require_login',
            'confidence' => 95,
            'actionButtons' => $actionButtons
        ];
    }

    /**
     * 获取默认回复
     */
    private function getDefaultResponse() {
        return "抱歉，我还不太理解您的问题。\n\n您可以问我：\n💰 我的余额\n📊 我的投资\n👑 VIP等级\n👥 团队数据\n\n或者直接点击下方快捷按钮。";
    }

    /**
     * 保存消息（公开方法，供外部调用）
     */
    public function saveMessage($conversationId, $userId, $type, $content, $intent, $confidence) {
        $this->db->query(
            "INSERT INTO pv_ai_chat_messages
             (conversation_id, user_id, message_type, message_content, user_intent, ai_confidence, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$conversationId, $userId, $type, $content, $intent, $confidence, $_SERVER['REMOTE_ADDR'] ?? '']
        );
    }

    /**
     * 更新会话
     */
    public function updateConversation($conversationId, $userId) {
        $exists = $this->db->fetchOne(
            "SELECT id FROM pv_ai_conversations WHERE conversation_id = ?",
            [$conversationId]
        );

        if ($exists) {
            $this->db->query(
                "UPDATE pv_ai_conversations
                 SET message_count = message_count + 2, last_message_at = NOW()
                 WHERE conversation_id = ?",
                [$conversationId]
            );
        } else {
            $this->db->query(
                "INSERT INTO pv_ai_conversations (conversation_id, user_id, message_count, last_message_at)
                 VALUES (?, ?, 2, NOW())",
                [$conversationId, $userId]
            );
        }
    }
}
