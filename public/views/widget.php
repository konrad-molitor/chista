<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/Security/WhitelistChecker.php';

function serveWidget(): void
{
    // Check whitelist
    $whitelist = new WhitelistChecker();
    if (!$whitelist->isRefererAllowed()) {
        $whitelist->sendForbiddenResponse();
        return;
    }

    // Set CORS headers
    foreach ($whitelist->getCorsHeaders() as $header => $value) {
        header("$header: $value");
    }

    header('Content-Type: application/javascript');
    ?>
(function() {
    'use strict';
    
    const CHISTA_API_BASE = '<?= $_ENV['CHISTA_API_BASE'] ?? 'http://localhost:8080' ?>';
    
    // Extract parameters from script tag
    const currentScript = document.currentScript || document.querySelector('script[src*="widget.js"]');
    const contextSrc = currentScript ? currentScript.getAttribute('data-context-src') : null;
    const widgetTitle = currentScript ? (currentScript.getAttribute('data-title') || 'Asistente Virtual') : 'Asistente Virtual';
    
    // Check if widget already exists to prevent duplicates
    if (document.getElementById('chista-widget')) {
        console.warn('Chista: Widget already initialized');
        return;
    }
    
    // Detect React/SPA environment
    const isReact = !!(window.React || document.querySelector('[data-reactroot]') || document.querySelector('[data-react-checksum]'));
    const isVue = !!(window.Vue);
    const isAngular = !!(window.ng);
    
    if (isReact) {
        console.log('Chista: React environment detected, using enhanced compatibility mode');
    }
    
    const styles = `
        #chista-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2147483647;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        #chista-chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        #chista-chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }
        
        #chista-chat-button svg {
            width: 24px;
            height: 24px;
            fill: white;
        }
        
        #chista-chat-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 2147483646;
        }
        
        #chista-chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        #chista-chat-title {
            font-weight: 600;
            font-size: 16px;
        }
        
        #chista-chat-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #chista-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .chista-message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .chista-message.user {
            justify-content: flex-end;
        }
        
        .chista-message-content {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            text-align: left;
        }
        
        .chista-message.bot .chista-message-content {
            background: white;
            color: #333;
            border: 1px solid #e1e5e9;
            border-radius: 18px 18px 18px 4px;
        }
        
        .chista-message.user .chista-message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        
        #chista-chat-input-container {
            padding: 15px;
            background: white;
            border-top: 1px solid #e1e5e9;
            display: flex;
            gap: 10px;
        }
        
        #chista-chat-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            resize: none;
            font-family: inherit;
        }
        
        #chista-chat-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        
        #chista-chat-send:hover {
            transform: scale(1.1);
        }
        
        #chista-chat-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .chista-typing {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 10px 15px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 18px 18px 18px 4px;
            max-width: 80%;
        }
        
        .chista-typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #667eea;
            animation: chista-typing 1.4s infinite ease-in-out;
        }
        
        .chista-typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .chista-typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes chista-typing {
            0%, 80%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            40% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .chista-new-chat-button {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .chista-new-chat-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    `;
    
    // Widget state
    let isOpen = false;
    let isLoading = false;
    let currentChatId = null;
    let messageHistory = [];
    let isInitialized = false;
    
    // Add styles to page
    function addStyles() {
        if (document.getElementById('chista-widget-styles')) {
            return;
        }
        
        const styleSheet = document.createElement('style');
        styleSheet.id = 'chista-widget-styles';
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);
    }
    
    // Create widget elements
    function createWidgetElements() {
        if (document.getElementById('chista-widget')) {
            return;
        }
        
        const widget = document.createElement('div');
        widget.id = 'chista-widget';
        widget.innerHTML = `
            <button id="chista-chat-button" title="Abrir chat">
                <svg viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                </svg>
            </button>
            
            <div id="chista-chat-window">
                <div id="chista-chat-header">
                    <div id="chista-chat-title">${widgetTitle}</div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button class="chista-new-chat-button" id="chista-new-chat">Nuevo chat</button>
                        <button id="chista-chat-close">&times;</button>
                    </div>
                </div>
                <div id="chista-chat-messages"></div>
                <div id="chista-chat-input-container">
                    <input type="text" id="chista-chat-input" placeholder="Escribe tu mensaje...">
                    <button id="chista-chat-send" title="Enviar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(widget);
    }
    
    // Attach event listeners with protection against loss
    function attachEventListeners() {
        const chatButton = document.getElementById('chista-chat-button');
        const chatClose = document.getElementById('chista-chat-close');
        const chatSend = document.getElementById('chista-chat-send');
        const chatInput = document.getElementById('chista-chat-input');
        const newChatButton = document.getElementById('chista-new-chat');
        
        if (chatButton && !chatButton.hasAttribute('data-chista-initialized')) {
            chatButton.addEventListener('click', toggleChat);
            chatButton.setAttribute('data-chista-initialized', 'true');
        }
        
        if (chatClose && !chatClose.hasAttribute('data-chista-initialized')) {
            chatClose.addEventListener('click', toggleChat);
            chatClose.setAttribute('data-chista-initialized', 'true');
        }
        
        if (newChatButton && !newChatButton.hasAttribute('data-chista-initialized')) {
            newChatButton.addEventListener('click', startNewChat);
            newChatButton.setAttribute('data-chista-initialized', 'true');
        }
        
        if (chatSend && !chatSend.hasAttribute('data-chista-initialized')) {
            chatSend.addEventListener('click', () => {
                sendMessage(chatInput.value);
            });
            chatSend.setAttribute('data-chista-initialized', 'true');
        }
        
        if (chatInput && !chatInput.hasAttribute('data-chista-initialized')) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage(chatInput.value);
                }
            });
            chatInput.setAttribute('data-chista-initialized', 'true');
        }
    }
    
    // Load saved session
    function loadSession() {
        const savedChatId = localStorage.getItem('chista_chat_id');
        if (savedChatId) {
            currentChatId = parseInt(savedChatId);
            loadChatHistory();
        }
    }
    
    function saveSession() {
        if (currentChatId) {
            localStorage.setItem('chista_chat_id', currentChatId.toString());
        }
    }
    
    function startNewChat() {
        currentChatId = null;
        messageHistory = [];
        localStorage.removeItem('chista_chat_id');
        const chatMessages = document.getElementById('chista-chat-messages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
        }
        addWelcomeMessage();
    }
    
    function addWelcomeMessage() {
        const welcomeMsg = contextSrc 
            ? '¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?'
            : '¡Hola! ¿En qué puedo ayudarte?';
        addMessage(welcomeMsg, 'bot');
    }
    
    function toggleChat() {
        isOpen = !isOpen;
        const chatWindow = document.getElementById('chista-chat-window');
        const chatInput = document.getElementById('chista-chat-input');
        
        if (chatWindow) {
            chatWindow.style.display = isOpen ? 'flex' : 'none';
        }
        
        if (isOpen && messageHistory.length === 0) {
            addWelcomeMessage();
        }
        
        if (isOpen && chatInput) {
            chatInput.focus();
        }
    }
    
    function addMessage(content, sender) {
        const chatMessages = document.getElementById('chista-chat-messages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `chista-message ${sender}`;
        messageDiv.innerHTML = `<div class="chista-message-content">${content}</div>`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        messageHistory.push({ content, sender });
    }
    
    function addTypingIndicator() {
        const chatMessages = document.getElementById('chista-chat-messages');
        if (!chatMessages) return;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chista-message bot';
        typingDiv.innerHTML = `
            <div class="chista-typing">
                <div class="chista-typing-dot"></div>
                <div class="chista-typing-dot"></div>
                <div class="chista-typing-dot"></div>
            </div>
        `;
        typingDiv.id = 'chista-typing-indicator';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function removeTypingIndicator() {
        const indicator = document.getElementById('chista-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    async function sendMessage(message) {
        if (isLoading || !message.trim()) return;
        
        isLoading = true;
        const chatSend = document.getElementById('chista-chat-send');
        const chatInput = document.getElementById('chista-chat-input');
        
        if (chatSend) chatSend.disabled = true;
        
        addMessage(message, 'user');
        if (chatInput) chatInput.value = '';
        addTypingIndicator();
        
        try {
            const payload = {
                message: message,
                chat_id: currentChatId,
                token: 'widget_token',
                domain: window.location.hostname,
                user_session_id: 'widget_' + Date.now()
            };
            
            if (contextSrc) {
                payload.context_src = contextSrc;
            }
            
            const response = await fetch(CHISTA_API_BASE + '/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                removeTypingIndicator();
                addMessage(data.response, 'bot');
                
                if (data.chat_id) {
                    currentChatId = data.chat_id;
                    saveSession();
                }
            } else {
                throw new Error(data.error || 'Error en la respuesta');
            }
        } catch (error) {
            removeTypingIndicator();
            addMessage('Lo siento, ha ocurrido un error. Por favor, inténtalo de nuevo.', 'bot');
            console.error('Chat error:', error);
        } finally {
            isLoading = false;
            if (chatSend) chatSend.disabled = false;
            if (chatInput) chatInput.focus();
        }
    }
    
    async function loadChatHistory() {
        if (!currentChatId) return;
        
        try {
            const response = await fetch(CHISTA_API_BASE + `/api/chat/${currentChatId}/history`);
            const data = await response.json();
            
            if (response.ok && data.messages) {
                const chatMessages = document.getElementById('chista-chat-messages');
                if (chatMessages) {
                    chatMessages.innerHTML = '';
                }
                messageHistory = [];
                
                data.messages.forEach(msg => {
                    const sender = msg.sender_type === 'user' ? 'user' : 'bot';
                    addMessage(msg.content, sender);
                });
                
                if (messageHistory.length === 0) {
                    addWelcomeMessage();
                }
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
            addWelcomeMessage();
        }
    }
    
    // Widget destruction function
    function destroyWidget() {
        const widget = document.getElementById('chista-widget');
        const styles = document.getElementById('chista-widget-styles');
        
        if (widget) {
            document.body.removeChild(widget);
        }
        if (styles) {
            document.head.removeChild(styles);
        }
        
        isInitialized = false;
    }
    
    // Enhanced initialization for React compatibility
    function initWidget() {
        if (isInitialized || document.getElementById('chista-widget')) {
            console.warn('Chista: Widget already initialized');
            return;
        }
        
        console.log('Chista: Initializing widget...');
        
        // Add delay for React environments
        const initDelay = isReact ? 200 : 50;
        
        setTimeout(() => {
            addStyles();
            createWidgetElements();
            attachEventListeners();
            loadSession();
            isInitialized = true;
            
            // Additional verification for React environments
            if (isReact) {
                setTimeout(() => {
                    const button = document.getElementById('chista-chat-button');
                    if (button && !button.hasAttribute('data-chista-initialized')) {
                        console.warn('Chista: Re-initializing event listeners for React compatibility');
                        attachEventListeners();
                    }
                }, 100);
            }
            
            console.log('Chista: Widget initialized successfully');
        }, initDelay);
    }
    
    // Global API for React applications
    window.ChistaWidget = {
        init: initWidget,
        destroy: destroyWidget,
        isInitialized: () => isInitialized,
        toggle: toggleChat,
        newChat: startNewChat
    };
    
    // Enhanced initialization logic
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initWidget();
    }
    
})();
<?php
} 