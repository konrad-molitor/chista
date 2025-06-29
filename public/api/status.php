<?php

declare(strict_types=1);

require_once __DIR__ . '/openrouter.php';
require_once __DIR__ . '/../../src/Security/WhitelistChecker.php';

function handleStatusRequest(string $method): void
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

    try {
        $openRouter = new OpenRouterService();
        $isConfigured = $openRouter->isConfigured();
        
        $status = [
            'service' => 'chista',
            'version' => '1.0.0',
            'status' => 'active',
            'timestamp' => date('c'),
            'ai' => [
                'configured' => $isConfigured,
                'provider' => $isConfigured ? 'OpenRouter' : 'fallback'
            ]
        ];

        if ($isConfigured) {
            $status['ai']['model'] = $_ENV['OPENROUTER_MODEL'] ?? 'unknown';
        }

        echo json_encode($status);

    } catch (Exception $e) {
        error_log("Status API Error: " . $e->getMessage());
        
        echo json_encode([
            'service' => 'chista',
            'version' => '1.0.0',
            'status' => 'error',
            'timestamp' => date('c'),
            'error' => 'Service temporarily unavailable'
        ]);
    }
} 