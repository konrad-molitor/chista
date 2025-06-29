<?php

declare(strict_types=1);

require_once __DIR__ . '/chat-service.php';

function handleHistoryRequest(string $method, array $params): void
{
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $chatId = isset($params['chat_id']) ? (int)$params['chat_id'] : null;
    
    if (!$chatId) {
        http_response_code(400);
        echo json_encode(['error' => 'Chat ID is required']);
        return;
    }

    try {
        $chatService = new ChatService();
        
        if (!$chatService->chatExists($chatId)) {
            http_response_code(404);
            echo json_encode(['error' => 'Chat not found']);
            return;
        }
        
        $chat = $chatService->getChat($chatId);
        $limit = isset($params['limit']) ? min((int)$params['limit'], 100) : 50;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        
        $messages = $chatService->getChatMessages($chatId, $limit, $offset);
        
        echo json_encode([
            'chat_id' => $chatId,
            'chat' => $chat,
            'messages' => $messages,
            'count' => count($messages),
            'limit' => $limit,
            'offset' => $offset,
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        error_log("History API Error: " . $e->getMessage());
        
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to retrieve chat history'
        ]);
    }
} 