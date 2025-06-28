<?php

declare(strict_types=1);

function handleStatusRequest(): void
{
    echo json_encode([
        'status' => 'active',
        'version' => '1.0.0',
        'timestamp' => date('c'),
        'ai_provider' => 'OpenRouter',
        'model' => $_ENV['OPENROUTER_MODEL'] ?? 'gpt-3.5-turbo'
    ]);
} 