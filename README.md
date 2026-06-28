# Order Payment API

A production-ready, extensible Order and Payment Management API built with Laravel 10+ following SOLID principles, Domain-Driven Design, and PSR-12 standards with 100% clean code architecture.

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Payment Gateways](#payment-gateways)
- [Adding New Payment Gateways](#adding-new-payment-gateways)
- [Database Schema](#database-schema)
- [Testing](#testing)
- [Code Quality](#code-quality)

## ✨ Features

- **Domain-Driven Design** - Clean separation of concerns with modular architecture
- **Extensible Payment System** - Add new payment gateways in just 3 steps
- **Strategy Pattern** - Flexible payment gateway handling with `PaymentGatewayInterface`
- **JWT Authentication** - Secure token-based API authentication
- **Transaction Safety** - Database transactions ensure data consistency
- **Comprehensive Validation** - Form request validation with strict rules
- **Resource Pattern** - Consistent API responses with Resource classes
- **Error Handling** - Unified exception handling with clean JSON responses
- **Test Coverage** - 11+ unit and feature tests with RefreshDatabase trait
- **Code Formatting** - 100% PSR-12 compliant with Laravel Pint

## 🛠️ Tech Stack

- **Framework**: Laravel 10+
- **Language**: PHP 8.2+
- **Database**: MySQL 8.x
- **Authentication**: JWT (tymon/jwt-auth 2.0)
- **Testing**: PHPUnit with Laravel Testing utilities
- **Code Quality**: Laravel Pint (PSR-12)

## 🏗️ Architecture

### Domain-Driven Design (DDD)

The application is organized into self-contained domain modules:

```
app/
├── Core/
│   ├── Base/
│   │   ├── BaseController.php
│   │   ├── BaseService.php
│   │   └── BaseRepository.php
│   ├── Traits/
│   │   ├── ApiResponses.php
│   │   ├── ValidatesData.php
│   │   └── HasEvents.php
│   └── Exceptions/
│       ├── ApiException.php
│       ├── OrderException.php
│       └── PaymentException.php
│
├── Domain/
│   ├── Order/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   ├── DTOs/
│   │   └── Enums/
│   │
│   ├── Payment/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Gateways/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── DTOs/
│   │
│   └── User/
│       ├── Models/
│       ├── Controllers/
│       ├── Requests/
│       └── Resources/
│
├── Http/
│   └── Controllers/Api/
│
└── Providers/
    └── AppServiceProvider.php
```

### Key Design Patterns

1. **Strategy Pattern** - Payment gateway implementations are interchangeable
2. **Repository Pattern** - Data access abstraction through repositories
3. **Service Layer** - Business logic encapsulation
4. **DTO Pattern** - Type-safe data transfer objects
5. **Resource Pattern** - Consistent API response formatting

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.x
- XAMPP or Laravel Sail

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd order-payment-api
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Configure database** (in `.env`)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_payment_api
DB_USERNAME=root
DB_PASSWORD=
```

5. **Create MySQL database**
```bash
mysql -u root -e "CREATE DATABASE order_payment_api;"
```

6. **Run migrations**
```bash
php artisan migrate
```

7. **Start the server**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 📡 API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login and get JWT token |
| POST | `/api/logout` | Logout and invalidate token |
| POST | `/api/refresh` | Refresh JWT token |
| GET | `/api/me` | Get authenticated user profile |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders` | List user's orders (paginated) |
| POST | `/api/orders` | Create new order |
| GET | `/api/orders/{id}` | Get order details |
| PUT | `/api/orders/{id}` | Update order (status, customer info, items) |
| DELETE | `/api/orders/{id}` | Delete order (if no payments) |

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/payments/process` | Process payment for order |
| GET | `/api/payments` | List all payments |
| GET | `/api/payments/order/{id}` | List payments for specific order |

## 🔐 Authentication

The API uses JWT (JSON Web Tokens) for stateless authentication.

### Register
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "secret123"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": null
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Using the Token
Include the token in the `Authorization` header:
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 💳 Payment Gateways

### Supported Gateways

Currently supported payment gateways:

- **Credit Card** - Direct credit card processing
- **PayPal** - PayPal payment processing

### Configuration

Configure gateways in `.env`:

```env
PAYMENT_GATEWAY_DEFAULT=credit_card
PAYMENT_GATEWAY_CREDIT_CARD_ENABLED=true
PAYMENT_GATEWAY_PAYPAL_ENABLED=true
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_SECRET=your_secret
```

Or update `config/payment.php`:

```php
'gateways' => [
    'credit_card' => [
        'enabled' => true,
        'timeout' => 30,
    ],
    'paypal' => [
        'enabled' => true,
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'timeout' => 30,
    ],
]
```

## 🔌 Adding New Payment Gateways

### 3-Step Process to Add a New Gateway

#### Step 1: Create Gateway Implementation

Create a new gateway class implementing `PaymentGatewayInterface`:

```php
<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function process(float $amount, string $currency, array $data): array
    {
        // Implement Stripe payment processing
        return [
            'status' => 'successful',
            'payment_id' => 'stripe_' . uniqid(),
            'reference' => uniqid(),
        ];
    }
}
```

#### Step 2: Register in AppServiceProvider

Update `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    $this->app->singleton(PaymentGatewayManager::class, function (): PaymentGatewayManager {
        $manager = new PaymentGatewayManager;
        $config = config('payment.gateways', []);

        foreach ($config as $name => $gatewayConfig) {
            if (! ($gatewayConfig['enabled'] ?? false)) {
                continue;
            }

            $gateway = match ($name) {
                'credit_card' => new CreditCardGateway,
                'paypal' => new PayPalGateway,
                'stripe' => new StripeGateway,  // Add this line
                default => null,
            };

            if ($gateway !== null) {
                $manager->register($name, $gateway);
            }
        }

        return $manager;
    });
}
```

#### Step 3: Add Configuration

Update `config/payment.php`:

```php
'gateways' => [
    // ... existing gateways
    'stripe' => [
        'enabled' => env('PAYMENT_GATEWAY_STRIPE_ENABLED', false),
        'secret' => env('STRIPE_SECRET_KEY'),
        'timeout' => 30,
    ],
]
```

And in `.env`:

```env
PAYMENT_GATEWAY_STRIPE_ENABLED=true
STRIPE_SECRET_KEY=your_stripe_key
```

That's it! Your new gateway is now available.

## 📊 Database Schema

### Orders Table
```sql
CREATE TABLE orders (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    currency CHAR(3) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### Order Items Table
```sql
CREATE TABLE order_items (
    id BIGINT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP
);
```

### Payments Table
```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    payment_id VARCHAR(255) UNIQUE,
    reference VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL,
    currency CHAR(3) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP
);
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Feature/OrderPaymentApiTest.php
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Test Structure

- **Unit Tests** - Individual component testing
- **Feature Tests** - End-to-end API testing with RefreshDatabase
- **Test Cases Covered**:
  - ✅ Customer can create an order
  - ✅ Payment can mark order as paid
  - ✅ Order status transitions are validated
  - ✅ Payments only for confirmed orders
  - ✅ Order update validation
  - ✅ Cannot delete orders with payments
  - ✅ Invalid payment gateway rejection
  - ✅ JWT authentication and authorization

## ✅ Code Quality

### PSR-12 Compliance

All code follows PSR-12 standards. Check and fix formatting:

```bash
# Check formatting
./vendor/bin/pint --test

# Fix formatting
./vendor/bin/pint
```

### Code Structure

- **100% Type Hints** - Full PHP type declarations
- **SOLID Principles** - Single responsibility, open/closed, Liskov substitution
- **DRY** - No code duplication
- **Clean Code** - Readable, maintainable, well-documented
- **No Hardcoded Logic** - Configuration-driven approach

## 📦 Project Structure

```
project/
├── app/                          # Application code
├── bootstrap/                    # Framework bootstrapping
├── config/                       # Configuration files
├── database/
│   ├── migrations/               # Database migrations
│   ├── factories/                # Model factories
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
├── resources/                    # View and frontend resources
├── routes/                       # API routes
├── storage/                      # Logs and cache
├── tests/                        # Test suite
├── vendor/                       # Composer dependencies
├── .env.example                  # Environment template
├── artisan                       # Laravel command
├── composer.json                 # PHP dependencies
└── README.md                     # This file
```

## 🔄 Business Rules

### Order Status Flow
- **PENDING** → Can transition to CONFIRMED or CANCELLED
- **CONFIRMED** → Can transition to CANCELLED only
- **CANCELLED** → Terminal state (no transitions)

### Order Update Rules
- Customer details (name, email) can only be updated for PENDING orders
- Order items can only be updated for PENDING orders
- Status updates follow the status transition rules

### Payment Rules
- Payments can only be processed for CONFIRMED orders
- Only one successful payment allowed per order
- Payment amount must match order total (rounded to 2 decimals)
- Only valid payment methods accepted (credit_card, paypal)

## 📚 API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "customer_name": "John Doe",
    "total": 100.00
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Invalid payment gateway",
  "errors": null
}
```

### Validation Error Response
```json
{
  "message": "The amount must be at least 0.01.",
  "errors": {
    "amount": ["The amount must be at least 0.01."]
  }
}
```

## 🐛 Exception Handling

The application uses a unified exception handler in `bootstrap/app.php`:

- **OrderException** - Order-related business logic errors
- **PaymentException** - Payment processing errors
- **ApiException** - General API errors

All exceptions return standardized JSON responses with appropriate HTTP status codes.

## 📞 Support & Documentation

For detailed API documentation, import the Postman collection available in the `docs/` directory.

### Quick Links
- [Laravel Documentation](https://laravel.com/docs)
- [JWT Auth Package](https://github.com/tymondesigns/jwt-auth)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

**Built with ❤️ following Clean Code Architecture and Production Standards**
# laravel-order-payment-api
