# Plan: Legacy PHP 7 Order Management System

สร้าง web application สำหรับจัดการคำสั่งซื้อและคลังสินค้า ด้วย PHP 7.4 + MySQL 5.7 แบบ legacy code ที่มีช่องโหว่ตั้งใจเพื่อใช้เดโม/รีวิว โดยรองรับ 8 features ตาม requirements.md: Auto Seed Data, User Registration, Login, Admin Order Management, Create/Update Order, User Confirm Order, และ Admin Bulk Confirm Orders

ใช้ spaghetti code patterns (mixed logic/I/O, long functions, poor naming, code duplication, no architecture layers) พร้อม SQL injection vulnerabilities และ plaintext/MD5 password storage รันด้วย Docker compose

## Steps

### 1. สร้าง Database Schema
สร้าง sql/schema.sql และ sql/seed.sql สำหรับ 5 ตาราง:
- `users` (email unique, password plaintext, is_admin)
- `products` (product_number, price, stock)
- `product_status` (PENDING/CONFIRMED)
- `orders` (order_number, user_id, status_id, shipping_address)
- `order_details` (order_id, product_id, quantity, subtotal)

พร้อม seed data สำหรับ product_status และ products 10 รายการ

### 2. สร้าง Docker Configuration
สร้าง Dockerfile (PHP 7.4-apache + mysqli extension) และ docker-compose.yml:
- service app: port 8080:80
- service db: MySQL 5.7 port 3306
- environment: MYSQL_DATABASE=order_db
- volumes mount public/

### 3. สร้าง Database Connection (Vulnerable)
สร้าง config/config.php (DB credentials) และ config/db.php (mysqli_connect แบบ global variable `$conn`, exposed error messages)

### 4. สร้าง Helper Functions (Spaghetti Style)
สร้าง includes/:
- functions.php (long functions 100+ lines สำหรับ processOrder, calculateTotal, generateOrderNumber)
- db_helper.php (raw SQL helpers ที่ใช้ string concatenation)
- validation.php (minimal validation)

### 5. Feature 1: Seed Data
สร้าง public/seed.php ที่ include sql/seed.sql และ execute ด้วย mysqli_multi_query, return JSON message, มี SQL injection ใน filename parameter

### 6. Feature 2: User Registration
สร้าง public/register.php:
- รับ JSON (email, first_name, last_name, phone, password, confirm_password)
- validate แบบง่ายๆ
- check duplicate email ด้วย SQL concatenation `WHERE email = '$email'`
- save password เป็น plaintext
- return 201/400/409 ตาม spec

### 7. Feature 3: User Login
สร้าง public/login.php:
- รับ JSON (email, password)
- query ด้วย vulnerable SQL `WHERE email = '$email' AND password = '$password'`
- set `$_SESSION['user_id']`
- return user data พร้อม 200

### 8. Feature 5: Create Order
สร้าง public/order.php (POST):
- รับ JSON (user_id, items: [{product_number, quantity}])
- generate order_number จาก timestamp+random
- loop insert order_details แบบไม่มี transaction
- calculate total_amount ในลูปยาวๆ
- return order_number 201

### 9. Feature 6: Update Order
สร้าง public/update_order.php (POST):
- รับ JSON (order_number, items)
- ไม่เช็ค ownership
- DELETE all order_details ก่อนแล้ว INSERT ใหม่
- vulnerable WHERE clause
- return 200

### 10. Feature 7: Confirm Order
สร้าง public/confirm_order.php (POST):
- รับ JSON (order_number, shipping_address)
- UPDATE ด้วย string concatenation
- ไม่เช็ค status ปัจจุบัน
- return 200

### 11. Feature 4: Admin List Orders
สร้าง public/admin/orders.php (GET):
- รับ query param search
- vulnerable LIKE query `LIKE '%$search%'`
- ไม่เช็ค is_admin จริงจัง (แค่ $_GET['admin']=='true')
- return orders array พร้อม order_details 200

### 12. Feature 8: Admin Bulk Confirm
สร้าง public/admin/confirm_orders.php (POST):
- รับ JSON (order_ids: [])
- loop UPDATE status แบบไม่มี transaction
- ไม่ validate order_ids
- return count 200

### 13. สร้าง Entry Point
สร้าง public/index.php เป็น landing page แสดง available endpoints หรือ simple router

### 14. สร้าง .htaccess (optional)
ตั้งค่า Apache rewrite rules ถ้าต้องการ clean URLs

## Verification

1. Build และรัน: `docker-compose up --build` ตรวจสอบ app accessible ที่ localhost:8080
2. Test seed: `curl -X POST http://localhost:8080/seed.php` ควรได้ success message
3. Test registration: POST localhost:8080/register.php ด้วย valid data ควรได้ 201, ซ้ำควรได้ 409
4. Test login: POST localhost:8080/login.php ควรได้ user data กลับมา
5. Test SQL injection: ส่ง email=`' OR '1'='1` ใน login ควร bypass ได้
6. ตรวจสอบ password ใน DB: SELECT จาก users table ควรเห็น plaintext password
7. Test create order: POST order.php ควรได้ order_number กลับมา
8. Test admin endpoints: GET admin/orders.php?search=test ควร return orders
9. Test bulk confirm: POST admin/confirm_orders.php ด้วย order_ids array ควร update status

## Decisions

- **Password Storage**: เลือก plaintext แทน MD5 เพื่อให้เห็นช่องโหว่ชัดเจนที่สุด (แต่อาจเปลี่ยนเป็น MD5 ถ้าต้องการให้ดูน้อยกว่า)
- **API Style**: ใช้ separate PHP files แทน single router เพื่อให้เป็น legacy style มากขึ้น
- **Transaction**: ไม่ใช้เจตนา เพื่อให้เกิด race condition และ inconsistency (bad practice)
- **Error Handling**: Expose SQL errors ตรงๆ เพื่อให้ hacker เห็นโครงสร้าง DB
- **Admin Check**: ใช้ simple query param check แทน proper authentication เพื่อให้ bypass ได้ง่าย

## Database Schema Details

### Table: users
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Table: products
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_number VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Table: product_status
```sql
CREATE TABLE product_status (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_code VARCHAR(20) UNIQUE NOT NULL,
    status_name VARCHAR(100) NOT NULL
);
```

### Table: orders
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status_id INT DEFAULT 1,
    shipping_address TEXT NULL,
    total_amount DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (status_id) REFERENCES product_status(status_id)
);
```

### Table: order_details
```sql
CREATE TABLE order_details (
    order_detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_number VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
```

## API Endpoints Summary

| Feature | Method | Endpoint | Request Body | Response |
|---------|--------|----------|--------------|----------|
| 1. Seed Data | POST | `/seed.php` | - | `{ "message": "Seeded successfully" }` |
| 2. Register | POST | `/register.php` | `{ email, first_name, last_name, phone, password, confirm_password }` | `201: { "message": "User created" }` |
| 3. Login | POST | `/login.php` | `{ email, password }` | `200: { "message": "Login success", "user_id": 1, "email": "..." }` |
| 4. Admin List Orders | GET | `/admin/orders.php` | Query: `?search=OrderNumber/Name` | `200: { "orders": [...] }` |
| 5. Create Order | POST | `/order.php` | `{ user_id, items: [{ product_number, quantity }] }` | `201: { "order_number": "ORD-..." }` |
| 6. Update Order | POST | `/update_order.php` | `{ order_number, items: [{ product_number, quantity }] }` | `200: { "message": "Order updated" }` |
| 7. Confirm Order | POST | `/confirm_order.php` | `{ order_number, shipping_address }` | `200: { "message": "Order confirmed" }` |
| 8. Admin Bulk Confirm | POST | `/admin/confirm_orders.php` | `{ order_ids: [1, 2, 3] }` | `200: { "message": "Orders confirmed", "count": 3 }` |

## Seed Data

### Product Status
```sql
INSERT INTO product_status (status_id, status_code, status_name) VALUES
(1, 'PENDING', 'รอยืนยันคำสั่งซื้อ'),
(2, 'CONFIRMED', 'ยืนยันคำสั่งซื้อ');
```

### Sample Products
```sql
INSERT INTO products (product_number, product_name, price, stock_quantity) VALUES
('PRD001', 'สินค้า A - เสื้อยืด', 299.00, 50),
('PRD002', 'สินค้า B - กางเกงยีนส์', 890.00, 30),
('PRD003', 'สินค้า C - รองเท้าผ้าใบ', 1290.00, 40),
('PRD004', 'สินค้า D - กระเป๋าสะพาย', 650.00, 20),
('PRD005', 'สินค้า E - หมวกแก๊ป', 250.00, 60),
('PRD006', 'สินค้า F - แจ็คเก็ต', 1500.00, 15),
('PRD007', 'สินค้า G - ถุงเท้า', 99.00, 100),
('PRD008', 'สินค้า H - เข็มขัด', 350.00, 35),
('PRD009', 'สินค้า I - กระเป๋าตัง', 450.00, 25),
('PRD010', 'สินค้า J - แว่นกันแดด', 750.00, 18);
```

### Test Users (Optional)
```sql
INSERT INTO users (email, first_name, last_name, phone, password, is_admin) VALUES
('admin@test.com', 'Admin', 'System', '0801111111', 'admin123', 1),
('user1@test.com', 'สมชาย', 'ใจดี', '0812222222', 'user123', 0),
('user2@test.com', 'สมหญิง', 'รักสวย', '0823333333', 'user123', 0);
```
