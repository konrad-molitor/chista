<?php

declare(strict_types=1);

function handleApiRequest(string $uri, string $method): void
{
    header('Content-Type: application/json');
    
    $path = str_replace('/api', '', $uri);
    
    switch ($path) {
        case '/status':
            echo json_encode([
                'status' => 'active',
                'version' => '1.0.0',
                'timestamp' => date('c')
            ]);
            break;
            
        case '/chat':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';
            
            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Message is required']);
                break;
            }
            
            $responses = [
                'Hola' => '¡Hola! ¿En qué puedo ayudarte hoy?',
                'Ayuda' => 'Estoy aquí para ayudarte. Puedes preguntarme sobre nuestros productos y servicios.',
                'Precio' => 'Para información sobre precios, por favor contacta con nuestro equipo de ventas.',
                'Soporte' => 'Te estoy ayudando ahora mismo. ¿Cuál es tu consulta específica?',
                'Gracias' => '¡De nada! Estoy aquí para ayudarte cuando lo necesites.',
                'Adiós' => '¡Hasta pronto! No dudes en contactarnos si necesitas más ayuda.'
            ];
            
            $response = 'Gracias por tu mensaje. Nuestro equipo revisará tu consulta y te responderá pronto.';
            
            foreach ($responses as $keyword => $reply) {
                if (stripos($message, $keyword) !== false) {
                    $response = $reply;
                    break;
                }
            }
            
            echo json_encode([
                'response' => $response,
                'timestamp' => date('c')
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
} 