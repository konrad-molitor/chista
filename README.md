# Chista

Система чата техподдержки с AI-агентом, названная в честь зороастрического божества мудрости и знаний.

## Описание

Chista - это встраиваемый виджет чата техподдержки, который использует AI-агента для автоматических ответов и позволяет операторам-людям подключаться к разговору при необходимости.

### Основные возможности

- 🤖 **AI-агент**: Автоматические ответы через OpenRouter API
- 💬 **Живые операторы**: Возможность подключения человека-оператора
- 🔧 **Встраиваемый виджет**: Легко интегрируется в любой веб-сайт
- 🗄️ **История сообщений**: Сохранение всех чатов в MySQL
- 🔐 **Безопасность**: Система токенов и защита по доменам
- 📊 **Панель оператора**: Удобный интерфейс для управления чатами

## Технологии

- **Backend**: PHP-FPM
- **Frontend**: jQuery + Bootstrap
- **База данных**: MySQL
- **AI**: OpenRouter API
- **Контейнеризация**: Docker

## Быстрый старт

### Требования

- Docker и Docker Compose
- PHP 8.1+
- Composer

### Установка

1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd chista
```

2. Скопируйте файл окружения:
```bash
cp .env.example .env
```

3. Настройте переменные в `.env`:
```env
DB_HOST=mysql
DB_NAME=chista
DB_USER=chista_user
DB_PASSWORD=secure_password
OPENROUTER_API_KEY=your_api_key
```

4. Запустите контейнеры:
```bash
docker-compose up -d
```

5. Установите зависимости:
```bash
docker-compose exec php composer install
```

6. Выполните миграции:
```bash
docker-compose exec php php src/migrations/migrate.php
```

### Использование

- **Виджет чата**: `http://localhost/widget.js`
- **Панель оператора**: `http://localhost/operator/`
- **API**: `http://localhost/api/`

## Интеграция

Для встраивания виджета на ваш сайт:

```html
<script>
  window.chistaConfig = {
    token: 'your_token_here',
    domain: 'your-domain.com'
  };
</script>
<script src="http://your-chista-domain.com/widget.js"></script>
```

## Разработка

### Структура проекта

```
chista/
├── public/              # Веб-корень
├── src/                 # PHP исходники
├── assets/              # CSS, JS, изображения
├── config/              # Конфигурационные файлы
├── database/            # Миграции и схемы
└── docker/              # Docker конфигурация
```

### Команды разработки

```bash
# Запуск в режиме разработки
docker-compose up

# Просмотр логов
docker-compose logs -f php

# Подключение к контейнеру PHP
docker-compose exec php bash

# Выполнение миграций
docker-compose exec php php src/migrations/migrate.php

# Создание нового токена
docker-compose exec php php src/cli/create-token.php --project="Project Name" --domain="example.com"
```

## API

### Основные эндпоинты

- `POST /api/chat/start` - Начать новый чат
- `POST /api/chat/message` - Отправить сообщение
- `GET /api/chat/history/{chatId}` - Получить историю чата
- `POST /api/chat/request-human` - Запросить оператора
- `GET /api/operator/chats` - Список чатов для оператора

## Лицензия

MIT License

## Поддержка

Для вопросов и поддержки создайте issue в этом репозитории.

---

*"Chista illuminates the path to knowledge through conversation"* 