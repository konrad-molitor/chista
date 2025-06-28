<?php

declare(strict_types=1);

require_once __DIR__ . '/openrouter.php';

function handleChatRequest(string $method): void
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';

    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        return;
    }

    try {
        $openRouter = new OpenRouterService();
        
        if ($openRouter->isConfigured()) {
            // Use AI response
            $response = $openRouter->sendMessage($message);
        } else {
            // Fallback to keyword matching if OpenRouter is not configured
            $response = getFallbackResponse($message);
        }

        echo json_encode([
            'response' => $response,
            'timestamp' => date('c'),
            'ai_powered' => $openRouter->isConfigured()
        ]);
        
    } catch (Exception $e) {
        error_log("Chat API Error: " . $e->getMessage());
        
        // Fallback to keyword matching on error
        $response = getFallbackResponse($message);
        
        echo json_encode([
            'response' => $response,
            'timestamp' => date('c'),
            'ai_powered' => false,
            'fallback' => true
        ]);
    }
}

function getFallbackResponse(string $message): string
{
    $responses = [
        'Hola' => '¡Hola! ¿En qué puedo ayudarte hoy?',
        'Ayuda' => 'Estoy aquí para ayudarte. Puedes preguntarme sobre nuestros productos y servicios.',
        'Precio' => 'Para información sobre precios, por favor contacta con nuestro equipo de ventas.',
        'Soporte' => 'Te estoy ayudando ahora mismo. ¿Cuál es tu consulta específica?',
        'Gracias' => '¡De nada! Estoy aquí para ayudarte cuando lo necesites.',
        'Adiós' => '¡Hasta pronto! No dudes en contactarnos si necesitas más ayuda.',
        'Problema' => 'Comprendo que tienes un problema. ¿Podrías darme más detalles para ayudarte mejor?',
        'Error' => 'Lamento que hayas encontrado un error. Vamos a solucionarlo juntos.',
        'Funciona' => '¿Hay algo específico que no está funcionando como esperabas?',
        'Cuenta' => 'Para temas relacionados con tu cuenta, puedo ayudarte con información general.',
        'Contraseña' => 'Para restablecer tu contraseña, ve a la página de inicio de sesión y selecciona "¿Olvidaste tu contraseña?"'
    ];

    // Simple keyword matching
    foreach ($responses as $keyword => $reply) {
        if (stripos($message, $keyword) !== false) {
            return $reply;
        }
    }

    return 'Gracias por tu mensaje. Te ayudo en lo que pueda. ¿Podrías ser más específico sobre tu consulta?';
} 