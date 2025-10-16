# Ignacio Parravicini Challenge — Order Management API

A simplified multi-tenant Order Management API built with Laravel 11, MySQL, and queues.
Each Client (tenant) owns Orders with one or more Items. When an Order is created, an async job simulates invoice generation and logs the result.

Tech Stack

Laravel 11 (Sanctum for API tokens)

PHP 8.3 (via Sail runtime 8.4 image)

MySQL 8

Queue driver: database

Docker Compose (Sail-style)

Optional: phpMyAdmin

# Quick Start
## 1) Clone & install
```
git clone https://github.com/igniparra/tier-one-challenge tier-one-challenge
cd tier-one-challenge

# Install PHP deps
composer install
```

If you installed into a non-empty folder earlier, make sure the Laravel skeleton is present and vendor/ is installed.

## 2) Environment

Copy the example and set your variables:

cp .env.example .env


Minimum required settings (these defaults work with the provided compose):
```
APP_NAME=Tier-One-Challenge
APP_ENV=local
APP_KEY=base64:GENERATE_ME
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=localhost
```

Generate the app key:
```
docker exec -it tierone_challenge-tierone.challenge-1 php artisan key:generate --show
```
then paste it into APP_KEY=. . . on the .env file.

## 3) Run containers

Start services:
```
docker compose up -d
```

You’ll get two containers: the Laravel app and MySQL.

## 4) Migrate & seed
```
docker exec -it tierone_challenge-tierone.challenge-1 php artisan migrate --force
docker exec -it tierone_challenge-tierone.challenge-1 php artisan db:seed
```

This seeds:

A test User: test@tierone.com / secret

Clients (tenants): ACME, Example

Two sample Orders with Items (for ACME)

# API

Authentication uses Laravel Sanctum Personal Access Tokens (Bearer).
Login to obtain a token, then send it with your requests.

## Auth

## Login

POST /api/login
```
{
  "email": "username@example.com",
  "password": "secret"
}
```

Response
```
{
  "user": { "id": 1, "name": "Captain", "email": "username@example.com" },
  "token": "<your-token-here>"
}
```

Use it like:
```
Authorization: Bearer <your-token-here>
Accept: application/json
```

## Me
```
GET /api/me
```

## Logout
```
POST /api/logout (revokes current token)
```

# Orders

## Create Order
```
POST /api/orders (auth required)

{
  "client_id": 1,
  "items": [
    {"name": "Dell Laptop", "quantity": 2, "unit_price": 1200},
    {"name": "Logitech Mouse", "quantity": 3, "unit_price": 25.5}
  ]
}
```

Validated via FormRequest

Dispatches GenerateInvoiceJob (async) → updates status to invoiced and logs a summary

## Get Order by ID
```
GET /api/orders/{id} (auth required)
```

## List Orders by Client
```
GET /api/clients/{client_id}/orders?per_page=15 (auth required)
```
Returns a standard Laravel paginator payload (data, current_page, last_page, links, per_page, total).

cURL examples
## Login
```
curl -X POST http://localhost/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"captain@example.com","password":"secret"}'
```
## Create order
```
curl -X POST http://localhost/api/orders \
  -H "Authorization: Bearer <TOKEN>" -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"client_id":1,"items":[{"name":"Monitor 27","quantity":1,"unit_price":300}]}'
```
## Get order
```
curl -X GET http://localhost/api/orders/1 \
  -H "Authorization: Bearer <TOKEN>" -H "Accept: application/json"
```

## List client orders
```
curl -X GET "http://localhost/api/clients/1/orders?per_page=10" \
  -H "Authorization: Bearer <TOKEN>" -H "Accept: application/json"
```

Architecture
```
app/
 ├── Http/
 │   ├── Controllers/
 │   │   ├── Api/AuthController.php      ← login/me/logout (Sanctum)
 │   │   └── OrderController.php         ← POST/GET endpoints
 │   ├── Requests/
 │   │   └── StoreOrderRequest.php       ← input validation
 │   └── Services/
 │       └── OrderService.php            ← business logic (create, show, list)
 ├── Jobs/
 │   └── GenerateInvoiceJob.php          ← async invoice simulation (logs & status)
 └── Models/
     ├── Client.php
     ├── Order.php                      
     └── OrderItem.php
```

Why Service layer? The challenge requires business rules to be outside controllers.
Why afterCommit? We dispatch the job after DB commit to avoid race conditions (queue:database).

Logging: invoice logs are written to storage/logs/invoice.log with a detailed line of the items:

Invoice generated for order #7 (client_id=1) | Items: 2x Laptop @ 1200.00 = 2400.00, 3x Mouse @ 25.50 = 76.50 | Total: 2476.50

## phpMyAdmin (optional)

Access: http://localhost:8081

# Testing
.env.testing (MySQL testing DB)

Create ./.env.testing with only the overrides you need:
```
APP_ENV=testing
APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=testing
DB_USERNAME=sail
DB_PASSWORD=password
```

Run tests:

docker exec -it docker exec -it tierone_challenge-tierone.challenge-1 php artisan test


Uses RefreshDatabase, runs migrations on the testing DB, and processes jobs inline (QUEUE_CONNECTION=sync).

# Troubleshooting

401 or HTML home for API routes: ensure you send Authorization: Bearer <token> and Accept: application/json. Do not include cookies from visiting / in your API calls.

Jobs not running: confirm QUEUE_CONNECTION=database, run migrations for queues, and ensure the queue worker service is up (docker compose up -d queue).

Worker not picking up new code: php artisan queue:restart inside the worker container, or use queue:listen during development.
