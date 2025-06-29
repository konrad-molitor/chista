<?php

declare(strict_types=1);

class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
        $this->baseUrl = $_ENV['OPENROUTER_BASE_URL'] ?? 'https://openrouter.ai/api/v1';
        $this->model = $_ENV['OPENROUTER_MODEL'] ?? 'anthropic/claude-3-haiku';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function sendMessage(string $message, array $context = [], ?string $externalContext = null): string
    {
        $messages = [];
        
        // Add system message with external context if provided
        if ($externalContext) {
            $messages[] = [
                'role' => 'system',
                'content' => "Contexto del sitio web:\n\n" . $externalContext . "\n\nPor favor, responde basándote en este contexto y siempre en español."
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => 'Eres un asistente virtual útil que responde en español. Ayuda a los usuarios con sus consultas de manera amigable y profesional.'
            ];
        }

        // Add conversation history
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['sender_type'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ];
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'HTTP-Referer: ' . ($_SERVER['HTTP_REFERER'] ?? 'http://localhost'),
                'X-Title: Chista AI Assistant'
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("OpenRouter API error: HTTP $httpCode");
        }

        $result = json_decode($response, true);
        
        if (!$result || !isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response from OpenRouter API');
        }

        return trim($result['choices'][0]['message']['content']);
    }

    public function getAvailableModels(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/models',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            return $result['data'] ?? [];
        }

        return [];
    }
} 