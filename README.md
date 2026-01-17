# Just Eat Laravel Backend

Laravel backend for the just-eat.ch clone — handles restaurants, orders, user auth, payments, and delivery tracking.

## Stack

- PHP 8.3 / Laravel 12
- MySQL 8.4
- Redis (sessions + cache + queues)
- Stripe / TWINT payment integrations

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Or with Docker:

```bash
docker compose up -d
```
