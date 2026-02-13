## Plan: Migrate Legacy PHP to Secure Laravel 11 Application

การแปลงเว็บแอป Order Management จาก legacy PHP 7.4 (มี SQL injection, plaintext passwords, ไม่มี authorization) ไปเป็น Laravel 11 application ที่ปลอดภัย พร้อมใช้งาน production และปฏิบัติตาม OWASP Top 10 2025 ทุกข้อ โดยรักษา feature parity ทุกอย่าง (user registration/login, order CRUD, order confirmation, admin search/bulk confirm)

คู่มือกำหนดให้ใช้ PHP 8.3, Laravel 11, Eloquent ORM exclusively, Laravel Breeze สำหรับ authentication, Form Requests สำหรับ validation, Policies สำหรับ authorization, และ Database transactions สำหรับทุก multi-step operations

---

**Steps**

### **Phase 1: Laravel Project Initialization**

1. สร้าง Laravel 11.x project ในโฟลเดอร์ใหม่ `phpdemo-laravel` ข้างนอก workspace ปัจจุบัน ด้วย `composer create-project laravel/laravel phpdemo-laravel "11.*"`

2. ติดตั้ง Laravel Breeze สำหรับ authentication scaffolding: `composer require laravel/breeze --dev` และ `php artisan breeze:install blade`

3. ติดตั้ง development tools:
   - Laravel Pint: `composer require laravel/pint --dev`
   - Larastan: `composer require nunomaduro/larastan:^2.0 --dev`

4. Configure [.env](.env) ด้วยค่าเดียวกับ database เดิม:
   - `DB_CONNECTION=mysql`, `DB_HOST=db`, `DB_DATABASE=order_db`
   - `DB_USERNAME=root`, `DB_PASSWORD` จาก [config/config.php](config/config.php)
   - Set `APP_ENV=local`, `APP_DEBUG=true` (จะเปลี่ยนเป็น false ใน production)

5. สร้าง `.gitignore` ให้รวม `.env` และ copy สร้าง `.env.example` (ลบ sensitive values ออก)

---

### **Phase 2: Database Migrations**

6. สร้าง migration สำหรับ `product_status` table โดยอ้างอิงจาก [sql/schema.sql](sql/schema.sql#L1-L8):
   - Columns: `status_id` (PK), `status_code` (UNIQUE), `status_name`
   - No timestamps (`$table->timestamps()` ไม่ต้องใส่)
   - สร้าง ProductStatusSeeder ที่ seed ค่า 2 records: PENDING (id=1), CONFIRMED (id=2) จาก [sql/seed.sql](sql/seed.sql#L4-L5)

7. แก้ไข Laravel's default users migration (`database/migrations/*_create_users_table.php`):
   - เปลี่ยน PK เป็น `user_id` แทน `id`: `$table->id('user_id')`
   - เพิ่ม columns: `first_name`, `last_name`, `phone`, `is_admin` (boolean, default false)
   - Keep `password` (string 255) แต่จะใช้ hashing
   - เพิ่ม unique index บน `email`
   - Use `$table->timestamps()` (Laravel convention)

8. สร้าง migration สำหรับ `products` table:
   - PK: `product_id`, columns: `product_number` (UNIQUE), `product_name`, `product_description`, `price` (decimal:10,2), `stock_quantity` (integer, default 0)
   - Indexes: unique on `product_number`
   - Timestamps

9. สร้าง migration สำหรับ `orders` table:
   - PK: `order_id`, columns: `order_number` (UNIQUE), `user_id` (FK), `status_id` (FK, default 1), `shipping_address` (text, nullable), `total_amount` (decimal:10,2)
   - Foreign keys: `user_id` → `users(user_id)`, `status_id` → `product_status(status_id)`
   - Indexes: unique on `order_number`, index on `user_id`
   - Timestamps

10. สร้าง migration สำหรับ `order_details` table:
    - PK: `order_detail_id`, columns: `order_id` (FK, onDelete CASCADE), `product_id` (FK), `product_number`, `quantity`, `unit_price` (decimal:10,2), `subtotal` (decimal:10,2)
    - Foreign keys with proper cascading
    - **No timestamps** (`public $timestamps = false` ใน model)

11. สร้าง ProductSeeder ด้วยข้อมูล 10 products จาก [sql/seed.sql](sql/seed.sql#L13-L24) (PRD001-PRD010 พร้อม Thai product names)

12. สร้าง UserSeeder สำหรับ test users แต่ใช้ `Hash::make()` สำหรับ passwords แทน plaintext - ตัวอย่าง: admin@test.com / Admin@123, user1@test.com / User@123

---

### **Phase 3: Eloquent Models & Relationships**

13. สร้าง [app/Models/ProductStatus.php](app/Models/ProductStatus.php):
    - `protected $table = 'product_status'`, `protected $primaryKey = 'status_id'`, `public $timestamps = false`
    - Constants: `const PENDING = 1; const CONFIRMED = 2;`
    - Relationship: `hasMany(Order::class, 'status_id')`

14. แก้ไข Laravel's default [app/Models/User.php](app/Models/User.php):
    - `protected $primaryKey = 'user_id'`
    - `$fillable`: email, first_name, last_name, phone, password, is_admin
    - `$hidden`: password, remember_token
    - `$casts`: is_admin → boolean, email_verified_at → datetime
    - Relationship: `hasMany(Order::class, 'user_id', 'user_id')`
    - Accessor: `getFullNameAttribute()` returns "first_name last_name"

15. สร้าง [app/Models/Product.php](app/Models/Product.php):
    - `protected $primaryKey = 'product_id'`
    - `$fillable`: product_number, product_name, product_description, price, stock_quantity
    - `$casts`: price → decimal:2, stock_quantity → integer
    - Relationship: `hasMany(OrderDetail::class, 'product_id')`

16. สร้าง [app/Models/Order.php](app/Models/Order.php):
    - `protected $primaryKey = 'order_id'`
    - `$fillable`: order_number, user_id, status_id, shipping_address, total_amount
    - `$casts`: total_amount → decimal:2
    - Relationships: `belongsTo(User::class)`, `belongsTo(ProductStatus::class)`, `hasMany(OrderDetail::class)`
    - Query scopes: `scopePending($query)` และ `scopeConfirmed($query)`
    - Model event ใน `boot()`: auto-generate `order_number` = "ORD-{timestamp}-{random 4 digits}" เมื่อ creating

17. สร้าง [app/Models/OrderDetail.php](app/Models/OrderDetail.php):
    - `protected $primaryKey = 'order_detail_id'`, `public $timestamps = false`
    - `$fillable`: order_id, product_id, product_number, quantity, unit_price, subtotal
    - `$casts`: quantity → integer, unit_price → decimal:2, subtotal → decimal:2
    - Relationships: `belongsTo(Order::class)`, `belongsTo(Product::class)`

---

### **Phase 4: Authentication Migration**

18. แก้ไข Laravel Breeze's generated controllers ให้รองรับ custom user fields:
    - [app/Http/Controllers/Auth/RegisteredUserController.php](app/Http/Controllers/Auth/RegisteredUserController.php): เพิ่ม validation และ fields สำหรับ first_name, last_name, phone
    - Validation rules: first_name/last_name (required, string, max:100), phone (required, string, max:20)

19. สร้าง [app/Http/Requests/RegisterRequest.php](app/Http/Requests/RegisterRequest.php):
    - Rules: email (required, email, unique:users), password (required, confirmed, min:8, Password::min(8)->letters()->mixedCase()->numbers()->symbols()), first_name/last_name/phone (required)

20. แก้ไข [routes/web.php](routes/web.php):
    - เพิ่ม rate limiting บน auth routes: `Route::middleware(['throttle:5,1'])->group()` สำหรับ login/register (max 5 attempts per minute ตาม A07 checklist)

21. แก้ไข Breeze's login view [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php) ให้ใช้ error message เดียวกันสำหรับ invalid email และ password (ป้องกัน username enumeration)

---

### **Phase 5: Authorization - Policies & Middleware**

22. สร้าง [app/Policies/OrderPolicy.php](app/Policies/OrderPolicy.php):
    - Method `view(User $user, Order $order)`: return `$user->user_id === $order->user_id || $user->is_admin`
    - Method `update(User $user, Order $order)`: return `$user->user_id === $order->user_id && $order->status_id === ProductStatus::PENDING`
    - Method `confirm(User $user, Order $order)`: return `$user->user_id === $order->user_id && $order->status_id === ProductStatus::PENDING`

23. Register OrderPolicy ใน [app/Providers/AuthServiceProvider.php](app/Providers/AuthServiceProvider.php):
    - `protected $policies = [Order::class => OrderPolicy::class]`

24. สร้าง [app/Http/Middleware/EnsureUserIsAdmin.php](app/Http/Middleware/EnsureUserIsAdmin.php):
    - Check `auth()->check() && auth()->user()->is_admin`
    - Abort 403 ถ้าไม่ใช่ admin

25. Register admin middleware ใน [app/Http/Kernel.php](app/Http/Kernel.php) หรือ [bootstrap/app.php](bootstrap/app.php) (Laravel 11 structure)

26. สร้าง [app/Http/Middleware/SecurityHeaders.php](app/Http/Middleware/SecurityHeaders.php):
    - Set headers: X-Frame-Options: DENY, X-Content-Type-Options: nosniff, X-XSS-Protection: 1; mode=block, Strict-Transport-Security: max-age=31536000

---

### **Phase 6: Business Logic - Service Layer**

27. สร้าง [app/Services/OrderService.php](app/Services/OrderService.php) เพื่อย้าย business logic จาก [includes/functions.php](includes/functions.php):

    **Method `createOrder(User $user, array $items)`** - แทน `processOrder()`:
    - Validate `$items` array format (product_id, quantity)
    - Wrap ใน `DB::transaction()` (A04 requirement)
    - Loop: fetch Product with `lockForUpdate()`, check stock availability
    - Create Order record (order_number auto-generated จาก model event)
    - Create OrderDetail records พร้อม calculate subtotal
    - Update Order's total_amount
    - Return Order instance
    - แก้ไข: ไม่มี SQL injection (ใช้ Eloquent), มี transaction, มี stock check

    **Method `updateOrder(Order $order, array $items)`** - แทน `updateOrderItems()`:
    - Validate order status (only PENDING)
    - Wrap ใน `DB::transaction()`
    - แทนที่จะ DELETE all + RE-INSERT: ใช้ pattern ที่ดีกว่า (UPDATE existing, INSERT new, DELETE removed)
    - Sync order details โดยเปรียบเทียบ old vs new items
    - Recalculate total_amount
    - Return updated Order

    **Method `confirmOrder(Order $order, string $shippingAddress)`** - แทน `confirmOrderWithAddress()`:
    - Validate order status (only PENDING)
    - Update shipping_address และเปลี่ยน status_id = ProductStatus::CONFIRMED
    - Log event ไปยัง security channel (A09 requirement)
    - Return updated Order
    - แก้ไข: ไม่มี SQL injection, เปลี่ยน status ด้วย (bug ในโค้ดเดิม)

    **Method `bulkConfirmOrders(array $orderIds)`** - แทน `bulkConfirmOrders()`:
    - Wrap ใน `DB::transaction()`
    - Fetch orders where order_id IN $orderIds AND status_id = PENDING
    - Update all to status_id = CONFIRMED
    - Log bulk action
    - Return count of updated orders

28. ถ้า OrderService ต้องการ dependency injection: inject ผ่าน constructor (e.g., Logger)

---

### **Phase 7: Form Requests & Validation**

29. สร้าง [app/Http/Requests/CreateOrderRequest.php](app/Http/Requests/CreateOrderRequest.php):
    - Rules: `products` (required, array, min:1), `products.*.product_id` (required, exists:products,product_id), `products.*.quantity` (required, integer, min:1)

30. สร้าง [app/Http/Requests/UpdateOrderRequest.php](app/Http/Requests/UpdateOrderRequest.php):
    - เหมือน CreateOrderRequest แต่เพิ่ม `order_number` (required, exists:orders,order_number)

31. สร้าง [app/Http/Requests/ConfirmOrderRequest.php](app/Http/Requests/ConfirmOrderRequest.php):
    - Rules: `order_number` (required, exists:orders,order_number), `shipping_address` (required, string, max:1000)

32. สร้าง [app/Http/Requests/SearchOrdersRequest.php](app/Http/Requests/SearchOrdersRequest.php):
    - Rules: `search` (nullable, string, max:100, regex:alphanumeric/spaces only เพื่อป้องกัน SQL injection ถึงแม้ใช้ Eloquent)

---

### **Phase 8: Controllers - User-Facing Features**

33. สร้าง [app/Http/Controllers/ProductController.php](app/Http/Controllers/ProductController.php):
    - Method `index()`: list all products ด้วย `Product::all()`, return view [resources/views/products/index.blade.php](resources/views/products/index.blade.php)

34. สร้าง [app/Http/Controllers/OrderController.php](app/Http/Controllers/OrderController.php):

    **Method `create()`** - แทน [public/order.php](public/order.php):
    - Fetch all products
    - Return view [resources/views/orders/create.blade.php](resources/views/orders/create.blade.php) (form with checkboxes + quantity inputs)

    **Method `store(CreateOrderRequest $request)`**:
    - Call `OrderService::createOrder(auth()->user(), $request->validated()['products'])`
    - Redirect to `show()` with success message

    **Method `edit($orderNumber)`** - แทน [public/update_order.php](public/update_order.php):
    - Fetch Order by order_number with `Order::where('order_number', $orderNumber)->firstOrFail()`
    - **Authorization**: `$this->authorize('update', $order)`
    - Load orderDetails and all products
    - Return view [resources/views/orders/edit.blade.php](resources/views/orders/edit.blade.php)

    **Method `update(UpdateOrderRequest $request, $orderNumber)`**:
    - Fetch Order, authorize, call `OrderService::updateOrder()`
    - Redirect back with success

    **Method `show($orderNumber)`**:
    - Fetch Order with orderDetails.product relationships
    - Authorize 'view'
    - Return view [resources/views/orders/show.blade.php](resources/views/orders/show.blade.php)

    **Method `showConfirmForm($orderNumber)`** - แทน [public/confirm_order.php](public/confirm_order.php) GET:
    - Fetch Order, authorize 'confirm'
    - Return view [resources/views/orders/confirm.blade.php](resources/views/orders/confirm.blade.php) (form for shipping address)

    **Method `confirm(ConfirmOrderRequest $request, $orderNumber)`** - POST:
    - Fetch Order, authorize, call `OrderService::confirmOrder()`
    - Redirect to `show()` with success

    **Method `index()`** - แสดง order history ของ user:
    - Fetch `auth()->user()->orders()->with('status', 'orderDetails')->latest()->get()`
    - Return view [resources/views/orders/index.blade.php](resources/views/orders/index.blade.php)

35. Define routes ใน [routes/web.php](routes/web.php):
    - `Route::middleware(['auth'])->group()`:
        - `Route::get('/products', [ProductController, 'index'])`
        - `Route::get('/orders', [OrderController, 'index'])`
        - `Route::get('/orders/create', [OrderController, 'create'])`
        - `Route::post('/orders', [OrderController, 'store'])`
        - `Route::get('/orders/{orderNumber}', [OrderController, 'show'])`
        - `Route::get('/orders/{orderNumber}/edit', [OrderController, 'edit'])`
        - `Route::put('/orders/{orderNumber}', [OrderController, 'update'])`
        - `Route::get('/orders/{orderNumber}/confirm', [OrderController, 'showConfirmForm'])`
        - `Route::post('/orders/{orderNumber}/confirm', [OrderController, 'confirm'])`

---

### **Phase 9: Controllers - Admin Features**

36. สร้าง [app/Http/Controllers/Admin/OrderController.php](app/Http/Controllers/Admin/OrderController.php):

    **Method `index(SearchOrdersRequest $request)`** - แทน [public/admin/orders.php](public/admin/orders.php):
    - ถ้ามี search query: `Order::whereHas('user', function($q) use ($search) { $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"); })->orWhere('order_number', 'like', "%{$search}%")->with('user', 'status')->get()`
    - ถ้าไม่มี: `Order::with('user', 'status')->latest()->get()`
    - Return view [resources/views/admin/orders/index.blade.php](resources/views/admin/orders/index.blade.php) (table with checkboxes, search form)
    - แก้ไข: ไม่มี SQL injection, ไม่มี N+1 query (ใช้ eager loading)

    **Method `bulkConfirm(Request $request)`** - แทน [public/admin/confirm_orders.php](public/admin/confirm_orders.php):
    - Validate `order_ids` (required, array, each exists:orders,order_id)
    - Call `OrderService::bulkConfirmOrders($request->order_ids)`
    - Redirect back with success message

37. Define admin routes ใน [routes/web.php](routes/web.php):
    - `Route::middleware(['auth', 'admin'])->prefix('admin')->group()`:
        - `Route::get('/orders', [Admin\OrderController, 'index'])`
        - `Route::post('/orders/bulk-confirm', [Admin\OrderController, 'bulkConfirm'])`

---

### **Phase 10: Blade Templates**

38. สร้าง base layout [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php):
    - Include navigation menu (ถ้า guest: login/register, ถ้า user: orders/products/logout, ถ้า admin: +admin menu)
    - Include CSRF meta tag
    - Responsive design (Bootstrap หรือ Tailwind ตามที่ Breeze ติดตั้ง)

39. สร้าง [resources/views/orders/create.blade.php](resources/views/orders/create.blade.php):
    - Form POST to `/orders` with `@csrf`
    - Checkbox list ของ products พร้อม quantity input (min=1)
    - Submit button

40. สร้าง [resources/views/orders/edit.blade.php](resources/views/orders/edit.blade.php):
    - Form PUT to `/orders/{orderNumber}` with `@csrf` และ `@method('PUT')`
    - Pre-filled quantities สำหรับ products ที่มีอยู่แล้วใน order
    - Checkbox list ของ all products

41. สร้าง [resources/views/orders/confirm.blade.php](resources/views/orders/confirm.blade.php):
    - แสดง order summary (items, total)
    - Form POST to `/orders/{orderNumber}/confirm` with `@csrf`
    - Textarea for shipping_address (required)

42. สร้าง [resources/views/orders/show.blade.php](resources/views/orders/show.blade.php):
    - แสดง order details: order_number, status, total_amount, shipping_address
    - Table of order_details (product name, quantity, unit price, subtotal)
    - Buttons: Edit (ถ้า PENDING และ can('update')), Confirm (ถ้า PENDING และ can('confirm'))

43. สร้าง [resources/views/orders/index.blade.php](resources/views/orders/index.blade.php):
    - Table of user's orders: order_number (link to show), status, total, created_at

44. สร้าง [resources/views/admin/orders/index.blade.php](resources/views/admin/orders/index.blade.php):
    - Search form (GET to same route)
    - Form POST to `/admin/orders/bulk-confirm` with `@csrf`
    - Table with checkboxes (name="order_ids[]" value=order_id)
    - Columns: checkbox, order_number, user name/email, status, total, created_at
    - Expandable row สำหรับแสดง order details (ใช้ JavaScript หรือ Alpine.js)
    - Bulk confirm button

---

### **Phase 11: Security Configuration**

45. แก้ไข [config/session.php](config/session.php):
    - `'lifetime' => 120` (2 hours)
    - `'expire_on_close' => false`
    - `'encrypt' => true`
    - `'http_only' => true`
    - `'same_site' => 'strict'`
    - `'secure' => env('SESSION_SECURE_COOKIE', true)` (force HTTPS in production)

46. Register SecurityHeaders middleware globally ใน HTTP kernel หรือ bootstrap/app.php

47. แก้ไข [.env.example](.env.example):
    - Remove all sensitive values (keep only variable names)
    - Document required variables: DB_*, APP_KEY, etc.

48. สร้าง `.env.production.example` ด้วย production settings:
    - `APP_ENV=production`, `APP_DEBUG=false`, `APP_LOG_LEVEL=error`
    - `SESSION_SECURE_COOKIE=true`

---

### **Phase 12: Logging Configuration**

49. แก้ไข [config/logging.php](config/logging.php):
    - เพิ่ม 'security' channel: driver=daily, path=storage/logs/security.log, level=info, days=90

50. สร้าง event listeners สำหรับ authentication events:
    - [app/Listeners/LogSuccessfulLogin.php](app/Listeners/LogSuccessfulLogin.php): log to 'security' channel ด้วย user_id, email, IP, user_agent
    - [app/Listeners/LogFailedLogin.php](app/Listeners/LogFailedLogin.php): log failed attempts

51. Register listeners ใน [app/Providers/EventServiceProvider.php](app/Providers/EventServiceProvider.php):
    - `Login::class => [LogSuccessfulLogin::class]`
    - `Failed::class => [LogFailedLogin::class]`

52. เพิ่ม logging statements ใน OrderService methods:
    - `createOrder()`: log order creation with user_id, order_number, total
    - `confirmOrder()`: log confirmation
    - `bulkConfirmOrders()`: log bulk action with admin user_id

53. แก้ไข [app/Exceptions/Handler.php](app/Exceptions/Handler.php):
    - Log AuthorizationException to security channel

---

### **Phase 13: Docker Configuration**

54. สร้าง [Dockerfile](Dockerfile) ใหม่สำหรับ Laravel:
    - Base image: `FROM php:8.3-fpm-alpine`
    - Install extensions: pdo_mysql, mbstring, xml, bcmath, opcache, zip, gd
    - Install Composer 2.x
    - Copy application files
    - Set permissions for storage/ และ bootstrap/cache/
    - CMD: php-fpm

55. สร้าง [docker-compose.yml](docker-compose.yml) ใหม่:
    - Service `app`: build from Dockerfile, volumes: ./phpdemo-laravel
    - Service `web`: nginx:alpine, volumes: nginx config + ./phpdemo-laravel/public
    - Service `db`: mysql:8.0, environment variables from .env
    - Service `redis`: redis:alpine (optional, for caching)

56. สร้าง nginx configuration file สำหรับ Laravel (ชี้ root ไปที่ public/, try_files for Laravel routing)

---

### **Phase 14: Testing**

57. สร้าง [tests/Feature/Auth/LoginTest.php](tests/Feature/Auth/LoginTest.php):
    - Test successful login with correct credentials
    - Test failed login with wrong password
    - Test rate limiting (6th attempt should fail)
    - Test session regeneration after login

58. สร้าง [tests/Feature/Auth/RegisterTest.php](tests/Feature/Auth/RegisterTest.php):
    - Test successful registration
    - Test password validation (weak password should fail)
    - Test duplicate email validation
    - Test password is hashed in database (not plaintext)

59. สร้าง [tests/Feature/OrderTest.php](tests/Feature/OrderTest.php):
    - Test user can create order
    - Test user can view own order
    - Test user CANNOT view other user's order (403 forbidden)
    - Test user can update own PENDING order
    - Test user CANNOT update CONFIRMED order
    - Test user CANNOT update other user's order
    - Test order confirmation changes status and saves shipping address
    - Test order total calculation is correct

60. สร้าง [tests/Feature/Admin/OrderManagementTest.php](tests/Feature/Admin/OrderManagementTest.php):
    - Test admin can view all orders
    - Test non-admin CANNOT access admin routes (403)
    - Test admin search functionality
    - Test bulk confirm updates multiple orders
    - Test bulk confirm uses transaction (simulate failure)

61. สร้าง [tests/Unit/Services/OrderServiceTest.php](tests/Unit/Services/OrderServiceTest.php):
    - Test createOrder() creates order and order_details in transaction
    - Test updateOrder() correctly syncs order_details
    - Test confirmOrder() changes status
    - Test transaction rollback on failure

62. Run tests: `php artisan test --coverage` และตรวจสอบให้ coverage ≥80% สำหรับ critical paths

---

### **Phase 15: Data Migration Strategy**

63. สร้าง artisan command [app/Console/Commands/MigrateUserPasswords.php](app/Console/Commands/MigrateUserPasswords.php):
    - Fetch all users from old database
    - สำหรับแต่ละ user: ถ้า password ยังเป็น plaintext (ตรวจสอบด้วย length < 60 chars), hash ด้วย `Hash::make()`
    - หรือ: force password reset โดยเซ็ต password เป็น random string และส่ง password reset email

64. สร้าง database validation script:
    - Check for orphaned order_details (order_id ไม่มีใน orders table)
    - Check for orders with total_amount ≠ sum of order_details subtotals
    - Export report ของข้อมูลผิดปกติ

65. วางแผน data migration:
    - Option A: Export data จาก old DB, transform, import ผ่าน seeders
    - Option B: Point Laravel ที่ existing database, run password migration command, เพิ่ม indexes ผ่าน migration

---

### **Phase 16: Code Quality & Static Analysis**

66. Run Laravel Pint: `./vendor/bin/pint` เพื่อให้โค้ดเป็นไปตาม PSR-12

67. Run Larastan: `./vendor/bin/phpstan analyse --memory-limit=2G --level=5` และแก้ไข type errors

68. Setup pre-commit hook (optional):
    - ใช้ Git hooks หรือ Husky
    - Run Pint และ PHPStan ก่อน commit

---

### **Phase 17: Final Security Audit**

69. Verify OWASP Top 10 2025 compliance:
    - **A01 Broken Access Control**: ทดสอบว่า OrderPolicy ทำงาน, user ไม่สามารถเข้าถึง orders ของคนอื่น, admin middleware ทำงาน
    - **A02 Cryptographic Failures**: verify passwords ถูก hash ด้วย `Hash::make()`, ไม่มี credentials hardcoded, .env ใน .gitignore
    - **A03 Injection**: verify ไม่มี raw SQL concatenation เลย (grep search "SELECT.*\$" ต้องไม่เจอ), ทุก query ใช้ Eloquent
    - **A04 Insecure Design**: verify มี DB::transaction() ใน OrderService methods, มี rate limiting บน auth routes
    - **A05 Security Misconfiguration**: verify APP_DEBUG=false ใน .env.production, security headers middleware active
    - **A07 Auth Failures**: test rate limiting ทำงาน, password validation ทำงาน (reject weak passwords)
    - **A08 Data Integrity**: verify audit logs ทำงาน (check storage/logs/security.log)
    - **A09 Logging Failures**: verify login/logout/order events ถูก log

70. Run security scan tools:
    - `composer audit` เพื่อ check vulnerabilities ใน dependencies
    - Install `enlightn/enlightn` หรือ `vimeo/psalm` สำหรับ additional security checks

---

### **Phase 18: Documentation & Deployment**

71. สร้าง [README.md](README.md) สำหรับ phpdemo-laravel project:
    - Prerequisites: PHP 8.3, Composer, Docker
    - Installation steps
    - Environment setup
    - Migration commands
    - Testing commands
    - Deployment instructions

72. Document API endpoints (ถ้ามี):
    - ใช้ Laravel Scribe หรือ manual documentation

73. Create deployment checklist:
    - Set production environment variables (.env)
    - Run migrations: `php artisan migrate`
    - Run seeders: `php artisan db:seed`
    - Clear caches: `php artisan config:cache`, `php artisan route:cache`
    - Set proper file permissions (storage/, bootstrap/cache/)
    - Enable HTTPS
    - Configure backup strategy
    - Setup monitoring (Laravel Telescope, Sentry, etc.)

74. Setup CI/CD pipeline (optional):
    - GitHub Actions หรือ GitLab CI
    - Steps: install dependencies, run tests, run Pint, run PHPStan, deploy

---

**Verification**

1. **Functional Testing**: ทดสอบทุก feature manually ใน browser
   - Register → Login → Create Order → Edit Order → Confirm Order → View Order History
   - Admin Login → Search Orders → Bulk Confirm → Logout

2. **Security Testing**:
   - พยายาม SQL injection ใน shipping address field (ต้องไม่ทำงาน)
   - พยายามเข้าถึง order ของคนอื่นด้วย URL manipulation (ต้อง 403)
   - ตรวจสอบ database: passwords ต้องเป็น hashed strings (bcrypt format: $2y$...)
   - พยายาม brute force login (attempt ที่ 6 ต้องถูก rate limit)

3. **Automated Testing**: `php artisan test --coverage` ต้อง pass ทุก test และ coverage ≥80%

4. **Static Analysis**: `./vendor/bin/phpstan analyse --level=5` ต้องไม่มี errors

5. **Code Style**: `./vendor/bin/pint --test` ต้อง pass (ไม่มี style violations)

6. **Database Integrity**:
   - สร้าง order → check orders table และ order_details table
   - Delete order → order_details ต้องถูก cascade delete
   - Confirm order → status_id ต้องเปลี่ยนเป็น 2

7. **Logging Verification**:
   - Login/logout → check storage/logs/security.log มี entries
   - Create/confirm order → check logs
   - Failed login attempt → check logs

8. **Performance**: ทดสอบ admin orders page ด้วย 100+ orders ต้องไม่มี N+1 query problem (ใช้ Laravel Debugbar หรือ Telescope)

---

**Decisions**

- **Stock Quantity**: ไม่ implement inventory tracking ใน phase นี้ เพราะโค้ดเดิมมี column แต่ไม่ใช้งาน (ถ้าต้องการให้เพิ่ม `Product::decrement('stock_quantity')` ใน OrderService::createOrder)
- **Order Number Generation**: ใช้ model event แทน การ generate ใน service layer เพื่อ single responsibility
- **Password Migration**: ใช้ Hash::make() สำหรับ test users ใหม่, production ควรใช้ force password reset approach
- **API Endpoints**: ไม่สร้างใน phase นี้ เพราะโค้ดเดิมไม่มี (ถ้าต้องการให้เพิ่ม routes/api.php และ API controllers ภายหลัง)
- **Soft Deletes**: ไม่เพิ่มใน phase นี้ เพราะโค้ดเดิมไม่มี (ถ้าต้องการให้เพิ่ม SoftDeletes trait และ deleted_at column)
- **Pagination**: ไม่เพิ่มใน admin orders index ตอนนี้ เพราะโค้ดเดิมไม่มี (ควร implement ถ้ามี orders > 100 records)
- **confirmOrderWithAddress Bug Fix**: แก้ bug ให้การ confirm เปลี่ยน status เป็น CONFIRMED ด้วย (โค้ดเดิมเก็บแค่ address)
