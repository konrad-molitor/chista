<?php

declare(strict_types=1);

require_once __DIR__ . '/../api/status.php';
require_once __DIR__ . '/../api/chat.php';

function handleApiRequest(string $uri, string $method): void
{
    header('Content-Type: application/json');
    
    $path = str_replace('/api', '', $uri);
    
    switch ($path) {
        case '/status':
            handleStatusRequest();
            break;
            
        case '/chat':
            handleChatRequest($method);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
} 