# Nutrition service

## Описание

**Сервис питания** — это микросервис для отслеживания и анализа рациона питания. Пользователи могут вести учет приемов пищи, сохранять информацию о продуктах в общей базе знаний и отслеживать данные о макронутриентах (белки, жиры, углеводы) и калориях. Сервис интегрируется с другими микросервисами для создания комплексного решения по отслеживанию здоровья.

**Основные функции:**
- **Аутентификация пользователей**: Безопасная регистрация, вход и выход с использованием Laravel Sanctum.
- **Учет питания**: Ведение записей о приемах пищи с указанием названия продукта, веса, макронутриентов и даты потребления.
- **База продуктов**: Сохранение и поиск информации о продуктах в общей базе.
- **Анализ питания**: Расчет калорий и агрегация данных о макронутриентах по дням.
- **API-ориентированность**: RESTful API для интеграции с другими микросервисами или фронтенд-приложениями.

**Роль в системе:**
- Управление аутентификацией пользователей и токенами сессий.
- Хранение, получение и анализ данных о питании.
- Взаимодействие с другими микросервисами через API для обмена данными.

## Технологии и зависимости

### Технологический стек
- **PHP**: 8.4
- **Laravel**: 12.1
- **Laravel Sanctum**: Для аутентификации и управления токенами API.
- **Pest PHP**: Для юнит- и функционального тестирования.
- **PostgreSQL**: Для хранения данных.

### Ключевые зависимости
- `laravel/sanctum`: Аутентификация и управление токенами API.
- `pestphp/pest`: Фреймворк для тестирования.
- Полный список зависимостей указан в `composer.json`.

## Начало работы

### Требования
- PHP >= 8.4
- Composer
- PostgreSQL

### Установка

1. **Клонирование репозитория**:
   ```bash
   git clone https://github.com/pabloERSH/nutrition-service.git
   cd nutrition-service
   ```

2. **Установка зависимостей**:
   ```bash
   composer install
   ```

3. **Настройка окружения**:
   - Скопируйте файл примера окружения:
     ```bash
     cp .env.example .env
     ```
   - Обновите `.env` с настройками базы данных и других параметров:
     ```
     DB_CONNECTION=pgsql
     DB_HOST=127.0.0.1
     DB_PORT=5432
     DB_DATABASE=your_db_name
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

4. **Генерация ключа приложения**:
   ```bash
   php artisan key:generate
   ```

5. **Запуск миграций базы данных**:
   ```bash
   php artisan migrate
   ```
6. **Запуск планировщика Laravel для автоматического удаления старых записей о приёмах пищи**:
   
   Добавьте строку в crontab:
   ```bash
   cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
   ```

7. **Запуск локального сервера**:
   ```bash
   php artisan serve
   ```
   Сервис будет доступен по адресу: [http://localhost:8000](http://localhost:8000).

## Запуск через Docker
### Требования
- Docker
- Docker-compose

1. **Настройка окружения**:
    Проверьте `.env`, следующие значения должны быть заданы:
    ```
    POSTGRES_DB=your_container_db_name
    POSTGRES_USER=your_container_db_username
    POSTGRES_PASSWORD=your_container_db_password
    PGADMIN_DEFAULT_EMAIL=your_container_pgadmin_login_email
    PGADMIN_DEFAULT_PASSWORD=your_container_pgadmin_password
    ```

2. **Запуск локального сервера**:
    Создать и запустить все требуемые контейнеры:
    ```bash
    docker-compose up -d --build
    ```
    После этого, сам сервис и pgadmin станут доступны на портах, указанных в docker-compose.yml.
    
    Для остановки сервиса используется команда:
    ```bash
    docker-compose stop
    ```
    Для повторного запуска:
    ```bash
    docker-compose start
    ```
    Для остановки и удаления всех созданных контейнеров:
    ```bash
    docker-compose down
    ```

## Документация API

Сервис питания предоставляет RESTful API под префиксом `/api/v1`. Все эндпоинты, кроме регистрации и входа, требуют аутентификации через токен Sanctum, передаваемый в заголовке `Authorization` как `Bearer <token>`.

### Эндпоинты аутентификации

- **POST /api/v1/register**
  - **Описание**: Регистрирует нового пользователя.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры**:
    - `name` (строка, обязательный)
    - `email` (строка, обязательный, уникальный)
    - `password` (строка, обязательный, минимум 8 символов)
  - **Пример запроса**:
    ```bash
    curl -X POST http://localhost:8000/api/v1/register \
         -H "Content-Type: application/json" \
         -d '{"name":"Иван Иванов","email":"ivan@example.com","password":"password123"}'
    ```
  - **Ответ (201 Created)**:
    ```json
    {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "token": "<sanctum_token>"
    }
    ```

- **POST /api/v1/login**
  - **Описание**: Аутентифицирует пользователя и возвращает токен Sanctum.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры**:
    - `email` (строка, обязательный)
    - `password` (строка, обязательный)
  - **Заголовки**:
    - `Accept: application/json`
  - **Пример запроса**:
    ```bash
    curl -X POST http://localhost:8000/api/v1/login \
         -H "Content-Type: application/json" \
         -H "Accept: application/json" \
         -d '{"email":"ivan@example.com","password":"password123"}'
    ```
  - **Ответ (200 OK)**:
    ```json
    {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "token": "<sanctum_token>"
    }
    ```

- **POST /api/v1/logout**
  - **Описание**: Инвалидирует текущий токен пользователя.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Ответ (200 OK)**:
    ```json
    {
      "message": "Logged out, token removed"
    }
    ```

- **GET /api/v1/user**
  - **Описание**: Возвращает информацию об аутентифицированном пользователе.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
  - **Ответ (200 OK)**:
    ```json
    {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com"
    }
    ```

### Эндпоинты питания

- **GET /api/v1/eaten-foods**
  - **Описание**: Возвращает постраничный список приемов пищи пользователя, подсчитывает калории.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
  - **Параметры запроса**:
    - `per_page` (целое число, необязательный, по умолчанию: 10, макс: 20)
    - `page` (целое число, необязательный, по умолчанию: 1)
  - **Ответ (200 OK)**:
    ```json
    {
      "data": [
        {
          "id": 1,
          "food_name": "Яблоко",
          "proteins": "0.50",
          "fats": "0.20",
          "carbs": "14.00",
          "weight": "100.00",
          "eaten_at": "2025-05-02",
          "kcal": 52.20
        }
      ],
      "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 1,
        "last_page": 1
      }
    }
    ```

- **POST /api/v1/eaten-foods**
  - **Описание**: Регистрирует новый прием пищи. Можно отправить свою инофрмацию о продукте, не отправляя food_id (id сохраненного продукта), а можно взять сохранненую информацию, передав food_id и не передавая food_name, proteins, fats, carbs.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры**:
    - `food_name` (строка, необязательный)
    - `food_id` (целое число, необязательный, ссылка на сохраненный продукт)
    - `proteins` (число, необязательный, обязателен при наличии `food_name`)
    - `fats` (число, необязательный, обязателен при наличии `food_name`)
    - `carbs` (число, необязательный, обязателен при наличии `food_name`)
    - `weight` (число, обязательный)
    - `eaten_at` (строка, обязательный, формат: YYYY-MM-DD)
  - **Пример запроса**:
    ```bash
    curl -X POST http://localhost:8000/api/v1/eaten-foods \
         -H "Authorization: Bearer <token>" \
         -H "Content-Type: application/json" \
         -d '{"food_name":"Собственный рецепт","proteins":10,"fats":20,"carbs":30,"weight":150,"eaten_at":"2025-05-02"}'
    ```
  - **Ответ (201 Created)**:
    ```json
    {
      "message": "Food saved successfully",
      "data": {
        "food_name": "Собственный рецепт",
        "proteins": "10.00",
        "fats": "20.00",
        "carbs": "30.00",
        "weight": "150.00",
        "eaten_at": "2025-05-02"
      }
    }
    ```

- **DELETE /api/v1/eaten-foods/{id}**
  - **Описание**: Удаляет запись о приеме пищи.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
  - **Ответ (200 OK)**:
    ```json
    {
      "message": "Food deleted successfully"
    }
    ```

- **GET /api/v1/eaten-foods/show-by-date**
  - **Описание**: Возвращает приемы пищи за указанную дату с агрегированными данными о питании. Подсчитывает суммарную информацию о макронутриентах и калориях.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры запроса**:
    - `date` (строка, обязательный, формат: YYYY-MM-DD)
    - `per_page` (целое число, необязательный, по умолчанию: 10, макс: 20)
    - `page` (целое число, необязательный, по умолчанию: 1)
  - **Ответ (200 OK)**:
    ```json
    {
      "data": {
        "items": [
          {
            "id": 1,
            "food_name": "Яблоко",
            "proteins": "0.50",
            "fats": "0.20",
            "carbs": "14.00",
            "weight": "100.00",
            "eaten_at": "2025-05-02",
            "kcal": 52.20
          }
        ],
        "Total proteins": 0.5,
        "Total fats": 0.2,
        "Total carbs": 14,
        "Total kcal": 52.2
      },
      "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 1,
        "last_page": 1
      }
    }
    ```

- **GET /api/v1/saved-foods**
  - **Описание**: Возвращает постраничный список сохраненных продуктов, подсчитывает калорийность на 100 грамм.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
  - **Параметры запроса**:
    - `per_page` (целое число, необязательный, по умолчанию: 10, макс: 20)
    - `page` (целое число, необязательный, по умолчанию: 1)
  - **Ответ (200 OK)**:
    ```json
    {
      "data": [
        {
          "id": 1,
          "food_name": "Яблоко",
          "proteins": "0.50",
          "fats": "0.20",
          "carbs": "14.00",
          "kcal": 59.8
        }
      ],
      "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 1,
        "last_page": 1
      }
    }
    ```

- **POST /api/v1/saved-foods**
  - **Описание**: Сохраняет новый продукт в базе данных.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры**:
    - `food_name` (строка, обязательный)
    - `proteins` (число, обязательный)
    - `fats` (число, обязательный)
    - `carbs` (число, обязательный)
  - **Ответ (201 Created)**:
    ```json
    {
      "message": "Food saved successfully",
      "data": {
        "food_name": "Яблоко",
        "proteins": "0.50",
        "fats": "0.20",
        "carbs": "14.00"
      }
    }
    ```

- **PATCH /api/v1/saved-foods/{id}**
  - **Описание**: Обновляет сохраненный продукт.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Ответ (200 OK)**:
    ```json
    {
      "message": "Food updated successfully",
        "data": {
            "id": 4,
            "user_id": 2,
            "food_name": "Крупа гречневая",
            "proteins": "13.00",
            "fats": "2.50",
            "carbs": "34.00",
        }
    }
    ```

- **DELETE /api/v1/saved-foods/{id}**
  - **Описание**: Удаляет сохраненный продукт.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
  - **Ответ (200 OK)**:
    ```json
    {
      "message": "Food deleted successfully"
    }
    ```
    
- **GET /api/v1/saved-foods/search**
  - **Описание**: Поиск сохраненных продуктов по названию.
  - **Заголовки**:
    - `Authorization: Bearer <token>`
    - `Accept: application/json`
    - `Content-type: application/json`
  - **Параметры**:
    - `food_name` (строка, обязательный)
  - **Ответ (200 OK)**:
    ```json
    {
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "food_name": "яблоко",
            "proteins": "0.50",
            "fats": "0.20",
            "carbs": "14.00",
            "created_at": "2025-05-15T19:43:29.000000Z",
            "updated_at": "2025-05-15T19:43:29.000000Z",
            "kcal": 59.8
        },
        {
            "id": 2,
            "user_id": 1,
            "food_name": "яблоко green",
            "proteins": "0.50",
            "fats": "0.20",
            "carbs": "22.00",
            "created_at": "2025-05-15T19:43:55.000000Z",
            "updated_at": "2025-05-15T19:43:55.000000Z",
            "kcal": 91.8
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 5,
        "total": 2,
        "last_page": 1
    }
}
    ```

## Тестирование

### Настройка
1. Создайте файл окружения для тестирования:
   ```bash
   cp .env.example .env.testing
   ```
2. Настройте `.env.testing`, указав подключение к тестовой базе данных PostgreSQL:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=your_test_db_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. Выполните миграции для тестового окружения:
   ```bash
   php artisan migrate --env=testing
   ```

### Запуск тестов
Запустите все тесты с помощью Pest:
```bash
php artisan test
```
Или с анализом покрытия (перед этим нужно установить Xdebug или PCOV):
```bash
php artisan test --coverage
```

Тестовый набор включает:
- Юнит-тесты для вспомогательных функций (например, `KcalCountHelper`).
- Функциональные тесты для аутентификации (`AuthTest`).
- Функциональные тесты для учета питания (`EatenFoodControllerTest`).
- Функциональные тесты для сохраненных продуктов (`SavedFoodControllerTest`).

## Контакты

- **Разработчик**: Ершов Павел
- **Telegram**: @pave1ershov
- **Email**: pave1ershov@yandex.ru
- **Проблемы**: Сообщайте о багах или запрашивайте новые функции через [GitHub Issues](https://github.com/pabloERSH/nutrition-service/issues).

---
