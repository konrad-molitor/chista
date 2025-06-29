<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Chista\Database\Connection;

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}

require_once __DIR__ . '/openrouter.php';
require_once __DIR__ . '/../../src/Security/WhitelistChecker.php';
require_once __DIR__ . '/../../src/Database/Connection.php';

function handleStatusRequest(string $method): void
{
    $whitelist = new WhitelistChecker();
    if (!$whitelist->isRefererAllowed()) {
        $whitelist->sendForbiddenResponse();
        return;
    }

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
        $dbConnected = Connection::testConnection();
        
        $status = [
            'service' => 'chista',
            'version' => '1.0.0',
            'status' => 'active',
            'timestamp' => date('c'),
            'database' => [
                'connected' => $dbConnected,
                'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'not set'),
                'name' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'not set')
            ],
            'ai' => [
                'configured' => $isConfigured,
                'provider' => $isConfigured ? 'OpenRouter' : 'fallback'
            ]
        ];



        if ($isConfigured) {
            $status['ai']['model'] = getenv('OPENROUTER_MODEL') ?: ($_ENV['OPENROUTER_MODEL'] ?? 'unknown');
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