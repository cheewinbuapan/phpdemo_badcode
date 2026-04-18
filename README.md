# Legacy PHP Order Management System

⚠️ **WARNING: This is intentionally vulnerable code for educational/demo purposes only!**

This project demonstrates a "legacy" PHP 7 web application with intentional security vulnerabilities and bad coding practices for code review and refactoring demonstrations.

## 🚨 Intentional Vulnerabilities

- **SQL Injection**: String concatenation in SQL queries
- **Plaintext Passwords**: No password hashing
- **Weak Authentication**: Bypassable admin checks via query params
- **No Input Validation**: Minimal/fake validation
- **Exposed Error Messages**: SQL errors displayed to users
- **No Transactions**: Race conditions possible
- **Spaghetti Code**: Mixed concerns, long functions, poor naming

**DO NOT use this code in production or expose it to the internet!**

## 🛠️ Tech Stack

- PHP 7.4
- MySQL 5.7
- Apache
- Docker & Docker Compose

## 📁 Project Structure

```
phpdemo_badcode/
├── public/                 # Web-accessible files
│   ├── index.php          # Landing page
│   ├── seed.php           # Seed data endpoint
│   ├── register.php       # User registration
│   ├── login.php          # User login
│   ├── order.php          # Create order
│   ├── update_order.php   # Update order
│   ├── confirm_order.php  # Confirm order
│   └── admin/             # Admin endpoints
│       ├── orders.php     # List/search orders
│       └── confirm_orders.php  # Bulk confirm
├── config/                # Configuration files
│   ├── config.php         # DB credentials (hardcoded!)
│   └── db.php             # DB connection (global var)
├── includes/              # Helper functions
│   ├── functions.php      # Business logic (spaghetti)
│   ├── db_helper.php      # DB helpers (vulnerable)
│   └── validation.php     # Minimal validation
├── sql/                   # Database scripts
│   ├── schema.sql         # Table definitions
│   └── seed.sql           # Seed data
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## 🚀 Getting Started

### Prerequisites

- Docker
- Docker Compose
- Node.js (v14+) - for development tools

### Installation & Running

1. **Clone the repository**
   ```bash
   cd f:\project\phpdemo_badcode
   ```

2. **Build and start containers**
   ```bash
   docker-compose up --build
   ```

3. **Wait for MySQL to initialize** (about 30 seconds)

4. **Access the application**
   - Web: http://localhost:8080
   - MySQL: localhost:3306

5. **Seed the database**
   ```bash
   curl -X POST http://localhost:8080/seed.php
   ```

## 🔧 Development Setup

### Git Pre-commit Hooks

This project uses [Husky](https://typicode.github.io/husky/) to enforce PHP syntax checks before committing.

1. **Install development dependencies**
   ```bash
   npm install
   ```

2. **Install Git hooks**
   ```bash
   npx husky install
   ```

3. **Create pre-commit hook**
   ```bash
   npx husky add .husky/pre-commit "npx lint-staged"
   ```

### What Gets Checked

- **PHP Syntax**: All staged `.php` files are validated with `php -l`
- **Purpose**: Prevents committing code with parse errors that would break the application

**Note**: These checks only validate syntax, not code quality or security. The bad code patterns in this project are intentional for educational purposes.

### Bypassing Hooks (Not Recommended)

If you need to commit without running checks:
```bash
git commit --no-verify -m "your message"
```

## 📋 API Endpoints

### 1. Seed Data
```bash
POST /seed.php
# Response: { "message": "Seeded successfully" }
```

### 2. User Registration
```bash
POST /register.php
Content-Type: application/json

{
  "email": "test@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "0812345678",
  "password": "password123",
  "confirm_password": "password123"
}

# Success: 201 { "message": "User created" }
# Email exists: 409 { "message": "Invalid input" }
# Invalid data: 400 { "message": "Invalid input" }
```

### 3. User Login
```bash
POST /login.php
Content-Type: application/json

{
  "email": "user1@test.com",
  "password": "user123"
}

# Success: 200 { "message": "Login success", "user_id": 2, ... }
# Failed: 401 { "message": "Invalid credentials" }
```

### 4. Create Order
```bash
POST /order.php
Content-Type: application/json

{
  "user_id": 2,
  "items": [
    { "product_number": "PRD001", "quantity": 2 },
    { "product_number": "PRD003", "quantity": 1 }
  ]
}

# Success: 201 { "order_number": "ORD-1707654321-1234", ... }
```

### 5. Update Order
```bash
POST /update_order.php
Content-Type: application/json

{
  "order_number": "ORD-1707654321-1234",
  "items": [
    { "product_number": "PRD002", "quantity": 3 }
  ]
}

# Success: 200 { "message": "Order updated" }
```

### 6. Confirm Order
```bash
POST /confirm_order.php
Content-Type: application/json

{
  "order_number": "ORD-1707654321-1234",
  "shipping_address": "123 Main St, Bangkok 10110"
}

# Success: 200 { "message": "Order confirmed" }
```

### 7. Admin - List/Search Orders
```bash
GET /admin/orders.php?admin=true
GET /admin/orders.php?admin=true&search=ORD-170

# Success: 200 { "orders": [...] }
```

### 8. Admin - Bulk Confirm Orders
```bash
POST /admin/confirm_orders.php?admin=true
Content-Type: application/json

{
  "order_ids": [1, 2, 3]
}

# Success: 200 { "message": "Orders confirmed", "count": 3 }
```

## 🧪 Test Credentials

After running seed.php:

- **Admin**: admin@test.com / admin123
- **User 1**: user1@test.com / user123
- **User 2**: user2@test.com / user123

## 💥 Demonstrating Vulnerabilities

### SQL Injection - Login Bypass
```bash
POST /login.php
Content-Type: application/json

{
  "email": "admin@test.com' OR '1'='1",
  "password": "anything"
}
# This will bypass authentication!
```

### SQL Injection - Search
```bash
GET /admin/orders.php?admin=true&search=' OR '1'='1
# Returns all orders
```

### Plaintext Passwords
```bash
# Connect to MySQL
docker exec -it phpdemo_db mysql -uroot -prootpass123 order_db

# View plaintext passwords
SELECT email, password FROM users;
```

### Weak Admin Check
```bash
# Anyone can access admin endpoints by adding ?admin=true
GET /admin/orders.php?admin=true
```

## 🛑 Stopping the Application

```bash
docker-compose down

# Remove volumes (delete database)
docker-compose down -v
```

## 📝 Development Notes

This codebase intentionally violates best practices:

- ❌ No framework (raw PHP)
- ❌ No dependency management (Composer)
- ❌ No password hashing
- ❌ No prepared statements
- ❌ No input sanitization
- ❌ No CSRF protection
- ❌ No proper session management
- ❌ No logging
- ❌ No error handling
- ❌ No tests
- ❌ Mixed concerns (logic + presentation)
- ❌ Global variables
- ❌ Long functions (100+ lines)
- ❌ Poor naming conventions
- ❌ Code duplication
- ❌ No architecture/layers

## 📚 Educational Purpose

This project is meant to:
1. Show common security vulnerabilities
2. Demonstrate legacy PHP patterns
3. Practice code review skills
4. Learn about refactoring opportunities
5. Understand security best practices by seeing violations

## ⚖️ License

Educational/Demo purposes only. Use at your own risk.
