<?php

declare(strict_types=1);

class OpenRouterService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
        $this->model = $_ENV['OPENROUTER_MODEL'] ?? 'gpt-3.5-turbo';
        $this->baseUrl = $_ENV['OPENROUTER_BASE_URL'] ?? 'https://openrouter.ai/api/v1';
        
        if (empty($this->apiKey)) {
            throw new Exception('OPENROUTER_API_KEY not configured');
        }
    }

    public function sendMessage(string $message, array $context = []): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Eres Chista, un asistente de soporte al cliente inteligente y útil. Responde siempre en español de manera amigable y profesional. Ayuda a los usuarios con sus consultas sobre productos y servicios.'
            ]
        ];

        // Add context messages if provided
        foreach ($context as $msg) {
            $messages[] = $msg;
        }

        // Add current user message
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

        $response = $this->makeRequest('/chat/completions', $data);
        
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        throw new Exception('Invalid response from OpenRouter API');
    }

    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: ' . ($_ENV['APP_URL'] ?? 'http://localhost:8080'),
            'X-Title: Chista AI Support'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: {$error}");
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("OpenRouter API Error: HTTP {$httpCode} - {$response}");
            throw new Exception("OpenRouter API returned HTTP {$httpCode}");
        }

        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from OpenRouter API');
        }

        return $decoded;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
} 