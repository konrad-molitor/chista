<?php

declare(strict_types=1);

require_once __DIR__ . '/../api/status.php';
require_once __DIR__ . '/../api/chat.php';
require_once __DIR__ . '/../api/history.php';

function handleApiRequest(string $uri, string $method): void
{
    header('Content-Type: application/json');
    
    $path = str_replace('/api', '', $uri);
    
    $urlParts = parse_url($uri);
    $params = [];
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $params);
    }
    
    switch (true) {
        case $path === '/status':
            handleStatusRequest($method);
            break;
            
        case $path === '/chat':
            handleChatRequest($method);
            break;
            
        case str_starts_with($path, '/chat/') && str_contains($path, '/history'):
            if (preg_match('/\/chat\/(\d+)\/history/', $path, $matches)) {
                $_GET['chat_id'] = $matches[1];
                handleHistoryRequest($method);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid chat history URL format']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
} 