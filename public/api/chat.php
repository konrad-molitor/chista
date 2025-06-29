<?php

declare(strict_types=1);

require_once __DIR__ . '/openrouter.php';
require_once __DIR__ . '/chat-service.php';
require_once __DIR__ . '/../../src/Security/WhitelistChecker.php';
require_once __DIR__ . '/../../src/Context/ContextLoader.php';

function handleChatRequest(string $method): void
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

    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $chatId = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
    $token = $input['token'] ?? 'default';
    $domain = $input['domain'] ?? 'localhost';
    $userSessionId = $input['user_session_id'] ?? null;
    $contextSrc = $input['context_src'] ?? null;
    


    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        return;
    }

    try {
        $chatService = new ChatService();
        $openRouter = new OpenRouterService();
        $contextLoader = new ContextLoader();
        
        if (!$chatId) {
            $chatId = $chatService->createChat($token, $domain, $userSessionId);
        } else {
            if (!$chatService->chatExists($chatId)) {
                http_response_code(404);
                echo json_encode(['error' => 'Chat not found']);
                return;
            }
        }
        
        $userMessageId = $chatService->saveUserMessage($chatId, $message);
        $chatService->updateChatActivity($chatId);
        
        // Generate AI response
        if ($openRouter->isConfigured()) {
            $context = $chatService->getChatContext($chatId, 10);
            
            if (!empty($context)) {
                array_pop($context);
            }
            
            $externalContext = null;
            if ($contextSrc) {
                $externalContext = $contextLoader->loadContext($contextSrc);
                if (!$externalContext) {
                    error_log("Failed to load context from: " . $contextSrc);
                }
            }
            
            $response = $openRouter->sendMessage($message, $context, $externalContext);
            $aiPowered = true;
        } else {
            $response = getFallbackResponse($message);
            $aiPowered = false;
        }
        $aiMessageId = $chatService->saveAiMessage($chatId, $response, [
            'ai_powered' => $aiPowered,
            'user_message_id' => $userMessageId,
            'context_src' => $contextSrc
        ]);

        echo json_encode([
            'response' => $response,
            'chat_id' => $chatId,
            'message_id' => $aiMessageId,
            'timestamp' => date('c'),
            'ai_powered' => $aiPowered
        ]);
        
    } catch (Exception $e) {
        error_log("Chat API Error: " . $e->getMessage());
        
        if (isset($chatService) && $chatId) {
            try {
                $chatService->saveUserMessage($chatId, $message);
            } catch (Exception $saveError) {
                error_log("Failed to save user message: " . $saveError->getMessage());
            }
        }
        $response = getFallbackResponse($message);
        
        echo json_encode([
            'response' => $response,
            'chat_id' => $chatId,
            'timestamp' => date('c'),
            'ai_powered' => false,
            'fallback' => true,
            'error' => 'AI service temporarily unavailable'
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