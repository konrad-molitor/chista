<div align="center">
  <img src="public/assets/img/logo.png" alt="Chista Logo" width="200"/>
  
  # Chista
  
  AI-powered customer support chat system named after the Zoroastrian deity of wisdom and knowledge.
</div>

## Description

Chista is an embeddable customer support chat widget that uses an AI agent for automatic responses and allows human operators to join conversations when needed.

### Key Features

- 🤖 **AI Agent**: Automatic responses via OpenRouter API
- 💬 **Live Operators**: Human operator can join conversations
- 🔧 **Embeddable Widget**: Easy integration into any website
- 🗄️ **Message History**: All chats saved in MySQL database
- 🔐 **Security**: Token-based authentication with domain protection
- 📊 **Operator Panel**: Convenient interface for chat management

## Technologies

- **Backend**: PHP-FPM
- **Frontend**: jQuery + Bootstrap
- **Database**: MySQL
- **AI**: OpenRouter API
- **Containerization**: Docker

## Quick Start

### Requirements

- Docker and Docker Compose
- PHP 8.1+
- Composer

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd chista
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Configure variables in `.env`:
```env
DB_HOST=mysql
DB_NAME=chista
DB_USER=chista_user
DB_PASSWORD=secure_password
OPENROUTER_API_KEY=your_api_key
```

4. Start containers:
```bash
docker-compose up -d
```

5. Install dependencies:
```bash
docker-compose exec php composer install
```

6. Run migrations:
```bash
docker-compose exec php php src/migrations/migrate.php
```

### Usage

- **Chat Widget**: `http://localhost/widget.js`
- **Operator Panel**: `http://localhost/operator/`
- **API**: `http://localhost/api/`

## Integration

To embed the widget on your website:

```html
<script>
  window.chistaConfig = {
    token: 'your_token_here',
    domain: 'your-domain.com'
  };
</script>
<script src="http://your-chista-domain.com/widget.js"></script>
```

## Development

### Project Structure

```
chista/
├── public/              # Web root
├── src/                 # PHP source code
├── assets/              # CSS, JS, images
├── config/              # Configuration files
├── database/            # Migrations and schemas
└── docker/              # Docker configuration
```

### Development Commands

```bash
# Start in development mode
docker-compose up

# View logs
docker-compose logs -f php

# Connect to PHP container
docker-compose exec php bash

# Run migrations
docker-compose exec php php src/migrations/migrate.php

# Create new token
docker-compose exec php php src/cli/create-token.php --project="Project Name" --domain="example.com"
```

## API

### Main Endpoints

- `POST /api/chat/start` - Start new chat
- `POST /api/chat/message` - Send message
- `GET /api/chat/history/{chatId}` - Get chat history
- `POST /api/chat/request-human` - Request human operator
- `GET /api/operator/chats` - List chats for operator

## License

MIT License

## Support

For questions and support, please create an issue in this repository.

---

*"Chista illuminates the path to knowledge through conversation"* 