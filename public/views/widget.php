<?php

declare(strict_types=1);

function serveWidget(): void
{
    header('Content-Type: application/javascript');
    ?>
(function() {
    'use strict';
    
    const CHISTA_API_BASE = '<?= $_ENV['CHISTA_API_BASE'] ?? 'http://localhost:8080' ?>';
    
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
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.5;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        @media (max-width: 480px) {
            #chista-chat-window {
                width: calc(100vw - 40px);
                height: calc(100vh - 40px);
                bottom: 20px;
                right: 20px;
                left: 20px;
                top: 20px;
            }
        }
    `;
    
    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
    
    const widgetHTML = `
        <div id="chista-widget">
            <button id="chista-chat-button" aria-label="Abrir chat de soporte">
                <svg viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                </svg>
            </button>
            
            <div id="chista-chat-window">
                <div id="chista-chat-header">
                    <div id="chista-chat-title">Soporte Chista</div>
                    <button id="chista-chat-close" aria-label="Cerrar chat">&times;</button>
                </div>
                
                <div id="chista-chat-messages">
                    <div class="chista-message bot">
                        <div class="chista-message-content">
                            ¡Hola! Soy Chista, tu asistente de soporte. ¿En qué puedo ayudarte hoy?
                        </div>
                    </div>
                </div>
                
                <div id="chista-chat-input-container">
                    <textarea id="chista-chat-input" placeholder="Escribe tu mensaje..." rows="1"></textarea>
                    <button id="chista-chat-send" aria-label="Enviar mensaje">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    function isWidgetInitialized() {
        return !!document.getElementById('chista-widget');
    }
    
    function attachEventListeners() {
        const chatButton = document.getElementById('chista-chat-button');
        const chatWindow = document.getElementById('chista-chat-window');
        const chatClose = document.getElementById('chista-chat-close');
        const chatInput = document.getElementById('chista-chat-input');
        const chatSend = document.getElementById('chista-chat-send');
        const chatMessages = document.getElementById('chista-chat-messages');
        
        if (!chatButton || !chatWindow || !chatInput || !chatSend || !chatMessages) {
            return false;
        }
        
        if (chatButton.hasAttribute('data-chista-initialized')) {
            return true;
        }
        
        let isOpen = false;
        
        function toggleChat() {
            isOpen = !isOpen;
            chatWindow.style.display = isOpen ? 'flex' : 'none';
            if (isOpen) {
                chatInput.focus();
            }
        }
        
        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chista-message ${isUser ? 'user' : 'bot'}`;
            messageDiv.innerHTML = `<div class="chista-message-content">${content}</div>`;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chista-message bot';
            typingDiv.id = 'chista-typing-indicator';
            typingDiv.innerHTML = `
                <div class="chista-typing">
                    <div class="chista-typing-dot"></div>
                    <div class="chista-typing-dot"></div>
                    <div class="chista-typing-dot"></div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function hideTyping() {
            const typingIndicator = document.getElementById('chista-typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        async function sendMessage(message) {
            try {
                const response = await fetch(`${CHISTA_API_BASE}/api/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        timestamp: new Date().toISOString()
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data.response || 'Lo siento, no pude procesar tu mensaje. Inténtalo de nuevo.';
            } catch (error) {
                console.error('Chista API Error:', error);
                return 'Lo siento, hay un problema con la conexión. Inténtalo más tarde.';
            }
        }
        
        async function handleSubmit() {
            const message = chatInput.value.trim();
            if (!message) return;
            
            addMessage(message, true);
            chatInput.value = '';
            chatSend.disabled = true;
            
            showTyping();
            const response = await sendMessage(message);
            hideTyping();
            addMessage(response);
            chatSend.disabled = false;
            chatInput.focus();
        }
        
        function autoResize() {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        }
        
        chatButton.addEventListener('click', toggleChat);
        chatClose.addEventListener('click', toggleChat);
        chatSend.addEventListener('click', handleSubmit);
        
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        });
        
        chatInput.addEventListener('input', autoResize);
        
        document.addEventListener('click', (e) => {
            if (isOpen && !chatWindow.contains(e.target) && !chatButton.contains(e.target)) {
                toggleChat();
            }
        });
        
        chatButton.setAttribute('data-chista-initialized', 'true');
        chatClose.setAttribute('data-chista-initialized', 'true');
        chatSend.setAttribute('data-chista-initialized', 'true');
        
        console.log('Chista widget loaded successfully!');
        return true;
    }
    
    function init() {
        if (isWidgetInitialized()) {
            return;
        }
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
        
        const success = attachEventListeners();
        
        if (!success) {
            setTimeout(() => {
                if (!attachEventListeners()) {
                    console.error('Chista: Failed to initialize widget after retry');
                }
            }, 100);
        }
    }
    
    function initWidget() {
        if (isWidgetInitialized()) {
            return;
        }
        
        const isReact = !!(window.React || document.querySelector('[data-reactroot]') || document.querySelector('[data-react-app]'));
        const isVue = !!(window.Vue);
        const isAngular = !!(window.ng);
        
        if (isReact || isVue || isAngular) {
            setTimeout(() => {
                init();
                setTimeout(() => {
                    const button = document.getElementById('chista-chat-button');
                    if (button && !button.hasAttribute('data-chista-initialized')) {
                        attachEventListeners();
                    }
                }, 200);
            }, 150);
        } else {
            init();
        }
    }
    
    function destroyWidget() {
        const widget = document.getElementById('chista-widget');
        if (widget) {
            widget.remove();
            console.log('Chista widget destroyed');
        }
    }
    
    window.ChistaWidget = {
        init: initWidget,
        destroy: destroyWidget,
        isInitialized: isWidgetInitialized,
        version: '1.0.0'
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        setTimeout(initWidget, 50);
    }
})();
    <?php
} 