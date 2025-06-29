<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/Database/Connection.php';

use Chista\Database\Connection;

class ChatService 
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Connection::getInstance();
    }
    
    /**
     * Creates a new chat session
     */
    public function createChat(string $token = 'default', string $domain = 'localhost', ?string $userSessionId = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO chats (token, domain, user_session_id, status) 
            VALUES (?, ?, ?, 'active')
        ");
        
        $stmt->execute([$token, $domain, $userSessionId]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Gets chat information
     */
    public function getChat(int $chatId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM chats WHERE id = ?
        ");
        
        $stmt->execute([$chatId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Updates chat last activity timestamp
     */
    public function updateChatActivity(int $chatId): void
    {
        $stmt = $this->db->prepare("
            UPDATE chats SET updated_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        
        $stmt->execute([$chatId]);
    }
    
    /**
     * Saves user message
     */
    public function saveUserMessage(int $chatId, string $content, ?array $metadata = null): int
    {
        return $this->saveMessage($chatId, 'user', $content, $metadata);
    }
    
    /**
     * Saves AI message
     */
    public function saveAiMessage(int $chatId, string $content, ?array $metadata = null): int
    {
        return $this->saveMessage($chatId, 'ai', $content, $metadata);
    }
    
    /**
     * Saves operator message
     */
    public function saveOperatorMessage(int $chatId, string $content, ?array $metadata = null): int
    {
        return $this->saveMessage($chatId, 'operator', $content, $metadata);
    }
    
    /**
     * Generic method for saving messages
     */
    private function saveMessage(int $chatId, string $senderType, string $content, ?array $metadata = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO messages (chat_id, sender_type, content, metadata) 
            VALUES (?, ?, ?, ?)
        ");
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        $stmt->execute([$chatId, $senderType, $content, $metadataJson]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Gets chat message history
     */
    public function getChatMessages(int $chatId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT id, sender_type, content, timestamp, metadata 
            FROM messages 
            WHERE chat_id = ? 
            ORDER BY timestamp ASC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$chatId, $limit, $offset]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($messages as &$message) {
            if ($message['metadata']) {
                $message['metadata'] = json_decode($message['metadata'], true);
            }
        }
        
        return $messages;
    }
    
    /**
     * Gets recent message context for AI
     */
    public function getChatContext(int $chatId, int $limit = 10): array
    {
        $messages = $this->getChatMessages($chatId, $limit);
        $context = [];
        
        foreach ($messages as $message) {
            $context[] = [
                'sender_type' => $message['sender_type'],
                'content' => $message['content']
            ];
        }
        
        return $context;
    }
    
    /**
     * Checks if chat exists
     */
    public function chatExists(int $chatId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM chats WHERE id = ?");
        $stmt->execute([$chatId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Gets active chats
     */
    public function getActiveChats(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   (SELECT content FROM messages m WHERE m.chat_id = c.id ORDER BY timestamp DESC LIMIT 1) as last_message,
                   (SELECT COUNT(*) FROM messages m WHERE m.chat_id = c.id) as message_count
            FROM chats c 
            WHERE c.status = 'active' 
            ORDER BY c.updated_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 