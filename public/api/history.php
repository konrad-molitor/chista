<?php

declare(strict_types=1);

require_once __DIR__ . '/chat-service.php';
require_once __DIR__ . '/../../src/Security/WhitelistChecker.php';

function handleHistoryRequest(string $method): void
{
    // Check whitelist first
    $whitelist = new WhitelistChecker();
    if (!$whitelist->isRefererAllowed()) {
        $whitelist->sendForbiddenResponse();
        return;
    }

    // Set CORS headers
    foreach ($whitelist->getCorsHeaders() as $header => $value) {
        header("$header: $value");
    }

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;

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

        $messages = $chatService->getChatMessages($chatId);

        echo json_encode([
            'chat_id' => $chatId,
            'messages' => $messages,
            'count' => count($messages),
            'timestamp' => date('c')
        ]);

    } catch (Exception $e) {
        error_log("History API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
} 