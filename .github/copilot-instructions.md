# Copilot Instructions (phpdemo_badcode → Laravel Migration)

**โปรเจกต์นี้เป็นคู่มือสำหรับการแปลงเว็บแอป PHP แบบเก่า (legacy PHP 7.4) ที่มีช่องโหว่ ไปเป็นแอปพลิเคชัน Laravel 11 ที่ทันสมัย ปลอดภัย และพร้อมใช้งาน production**

## ข้อมูลภาพรวม

โปรเจกต์เดิม (phpdemo_badcode) ถูกสร้างขึ้นเป็นตัวอย่างโค้ดที่มีปัญหาด้านความปลอดภัยและโครงสร้าง คู่มือนี้จะช่วยแปลงโปรเจกต์ให้เป็น Laravel application ที่มีมาตรฐานสูง ปลอดจากช่องโหว่ตาม OWASP Top 10 2025 และพร้อมสำหรับการใช้งานจริง

**Historical Context**: โค้ดเดิมมีช่องโหว่ตั้งใจ (SQL injection, plaintext passwords, no authorization) และโครงสร้างแบบ spaghetti - การ migration นี้จะแก้ไขทุกปัญหาเหล่านั้น

---

## Tech Stack (Modern)

### Core Requirements
- **PHP**: 8.3 (LTS - supported until November 2027)
- **Framework**: Laravel 11.x (latest LTS)
- **Database**: MySQL 8.0+ or MariaDB 10.11+
- **Package Manager**: Composer 2.x
- **Build/Deploy**: Docker & Docker Compose

### Required PHP Extensions
```
- pdo_mysql      # Database connectivity
- mbstring       # String handling
- xml            # XML processing
- bcmath         # Precision math
- opcache        # Performance optimization
- redis          # Caching/queue (optional but recommended)
- zip            # Archive handling
- gd or imagick  # Image processing (if needed)
```

### Development Tools
- **Code Style**: Laravel Pint (PSR-12 compliance)
- **Testing**: PHPUnit 10+ or Pest 2+
- **Static Analysis**: Larastan (PHPStan for Laravel) - Level 5+
- **Pre-commit Hooks**: Husky or native Git hooks

---

## Product Goals (Migration Target)

เว็บแอปพลิเคชัน Order Management System ที่:

1. **Secure by Default**: ปลอดจากช่องโหว่ทั้งหมดใน OWASP Top 10 2025
2. **Production-Ready**: พร้อมใช้งานจริง มีการทำ logging, monitoring, error handling
3. **Maintainable**: โครงสร้างชัดเจน ตาม Laravel conventions และ SOLID principles
4. **Well-Tested**: Test coverage อย่างน้อย 80% สำหรับ critical paths
5. **Modern UI/UX**: ใช้ Blade templates พร้อม CSRF protection, responsive design

### Core Features (ต้องครอบคลุมทั้งหมดจากโปรเจกต์เดิม)

**User Management**
- ✅ User registration with validation
- ✅ Secure authentication (hashed passwords)
- ✅ Login/logout with session management
- ✅ Role-based access (admin/user)

**Product Management**
- ✅ Product listing
- ✅ Product details view
- ✅ Admin product CRUD (if applicable)

**Order Management**
- ✅ Create order (multiple products with quantities)
- ✅ Update order (modify items before confirmation)
- ✅ Confirm order with shipping address
- ✅ View order history
- ✅ Order status tracking (PENDING → CONFIRMED)

**Admin Features**
- ✅ Search/filter orders
- ✅ Bulk confirm orders
- ✅ View all users' orders
- ✅ Order details with line items

**Additional (Modern Improvements)**
- ✅ RESTful API endpoints (alongside web UI)
- ✅ Rate limiting on authentication
- ✅ Comprehensive audit logging
- ✅ Email notifications (optional)

---

## Modern Laravel Best Practices

### 1. MVC Architecture with Service Layer

```
Controllers → Handle HTTP requests/responses
Services    → Business logic and complex operations
Models      → Eloquent ORM, relationships, scopes
Views       → Blade templates with components
```

**ห้ามทำ**:
- ❌ Business logic ในไฟล์ view
- ❌ Database queries ใน controller (ใช้ Eloquent/Service)
- ❌ Mixed concerns (HTML + PHP logic + SQL ในไฟล์เดียว)

**ต้องทำ**:
- ✅ ใช้ Form Requests สำหรับ validation
- ✅ ใช้ Policies สำหรับ authorization
- ✅ ใช้ Database transactions สำหรับ multi-step operations
- ✅ ใช้ Eloquent relationships แทน manual joins

### 2. Eloquent ORM - ALWAYS Use Query Builder

```php
// ✅ CORRECT - Safe from SQL injection
$user = User::where('email', $email)->first();
$orders = Order::where('user_id', $userId)
    ->with('orderDetails.product')
    ->get();

// ❌ WRONG - Vulnerable to SQL injection
$user = DB::select("SELECT * FROM users WHERE email = '$email'");
```

### 3. Authentication & Authorization

```php
// Use Laravel Breeze or Fortify
composer require laravel/breeze --dev
php artisan breeze:install blade

// Authorization with Policies
if (auth()->user()->can('update', $order)) {
    // Allow update
}

// Middleware protection
Route::middleware(['auth', 'verified'])->group(function () {
    // Protected routes
});
```

### 4. Validation via Form Requests

```php
// app/Http/Requests/CreateOrderRequest.php
public function rules()
{
    return [
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,product_id',
        'products.*.quantity' => 'required|integer|min:1',
    ];
}
```

### 5. Database Transactions

```php
// For complex multi-step operations (e.g., processOrder)
DB::transaction(function () use ($orderData) {
    $order = Order::create([...]);
    
    foreach ($orderData['items'] as $item) {
        OrderDetail::create([...]);
        
        // Update stock
        Product::find($item['product_id'])
            ->decrement('stock_quantity', $item['quantity']);
    }
});
```

### 6. Security-First Mindset

- ✅ ใช้ `@csrf` directive ในทุก form
- ✅ ใช้ `Hash::make()` สำหรับ passwords - **ไม่มีทางเก็บ plaintext**
- ✅ ใช้ `encrypt()` helper สำหรับข้อมูลละเอียดอ่อน
- ✅ Set `APP_DEBUG=false` ใน production
- ✅ กำหนด `$fillable` หรือ `$guarded` ใน models
- ✅ ใช้ rate limiting (`throttle` middleware)

---

## OWASP Top 10 2025 - Security Requirements

**ทุกโค้ดที่เขียนต้องปลอดจากช่องโหว่ทุกข้อต่อไปนี้**

### A01:2025 - Broken Access Control ⚠️ CRITICAL

**ปัญหาในโค้ดเดิม**: ไม่มีการตรวจสอบว่า order เป็นของ user ที่ login อยู่หรือไม่

**Laravel Solution**:
```php
// 1. Use Policies for authorization
// app/Policies/OrderPolicy.php
public function update(User $user, Order $order)
{
    return $user->user_id === $order->user_id;
}

// 2. Apply in Controller
public function update(Request $request, Order $order)
{
    $this->authorize('update', $order);
    // Process update...
}

// 3. Middleware for admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
});

// 4. Blade directives
@can('update', $order)
    <button>Edit Order</button>
@endcan
```

**Checklist**:
- ✅ ทุก order operation ต้องตรวจสอบ ownership
- ✅ Admin routes ต้องมี middleware ป้องกัน
- ✅ ใช้ Policy classes แทนการเช็ค `if ($user->id === $order->user_id)`
- ✅ Default deny - ปฏิเสธถ้าไม่ได้รับอนุญาตชัดเจน

---

### A02:2025 - Cryptographic Failures 🔐

**ปัญหาในโค้ดเดิม**: รหัสผ่านเก็บเป็น plaintext, credentials hardcoded

**Laravel Solution**:
```php
// 1. Password Hashing - ALWAYS use Hash facade
use Illuminate\Support\Facades\Hash;

// Store password
$user->password = Hash::make($request->password);

// Verify password
if (Hash::check($request->password, $user->password)) {
    // Authenticated
}

// 2. Sensitive data encryption
$encrypted = encrypt($sensitiveData);
$decrypted = decrypt($encrypted);

// 3. Environment variables for secrets
// .env (NEVER commit this file)
DB_PASSWORD=secret
APP_KEY=base64:generated_key

// 4. HTTPS enforcement in production
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'strict',
```

**Checklist**:
- ✅ **ห้ามเก็บ plaintext passwords โดยเด็ดขาด**
- ✅ ใช้ `Hash::make()` เท่านั้น (Bcrypt/Argon2id)
- ✅ ไม่มี credentials ใน source code - ใช้ `.env`
- ✅ เพิ่ม `.env` ใน `.gitignore`
- ✅ ใช้ HTTPS ใน production (force via middleware)
- ✅ Database connections ใช้ SSL/TLS

---

### A03:2025 - Injection 💉 CRITICAL

**ปัญหาในโค้ดเดิม**: SQL injection ทุกที่ - ใช้ string concatenation

**Laravel Solution**:
```php
// ✅ ALWAYS USE - Eloquent (automatic parameter binding)
$user = User::where('email', $request->email)->first();

// ✅ ALWAYS USE - Query Builder with bindings
$orders = DB::table('orders')
    ->where('user_id', '=', $userId)
    ->where('status_id', '=', $statusId)
    ->get();

// ⚠️ If raw SQL is absolutely necessary (rare)
$orders = DB::select('SELECT * FROM orders WHERE user_id = ? AND status_id = ?', 
    [$userId, $statusId]);

// ❌ NEVER DO THIS - Vulnerable to SQL injection
$orders = DB::select("SELECT * FROM orders WHERE user_id = $userId");
$user = DB::select("SELECT * FROM users WHERE email = '$email'");
```

**Additional Injection Prevention**:
```php
// 1. Input validation (Form Requests)
public function rules()
{
    return [
        'email' => 'required|email|max:255',
        'order_number' => 'required|alpha_dash|max:50',
    ];
}

// 2. Output escaping in Blade (automatic)
{{ $user->name }}  // Auto-escaped
{!! $html !!}      // Unescaped (use only for trusted content)

// 3. Command injection prevention
// Never use shell_exec, exec, system with user input
// If needed, use Process facade with array arguments
use Illuminate\Support\Facades\Process;
Process::run(['ls', '-la', $directory]); // Safe - no shell
```

**Checklist**:
- ✅ **ไม่มี raw SQL concatenation เลย**
- ✅ ใช้ Eloquent หรือ Query Builder exclusively
- ✅ Validate ทุก input ก่อนใช้งาน
- ✅ Blade templates escape output อัตโนมัติ
- ✅ ไม่ใช้ `DB::raw()` กับ user input

---

### A04:2025 - Insecure Design 🏗️

**ปัญหาในโค้ดเดิม**: ไม่มี rate limiting, ไม่มี transaction, business logic flaws

**Laravel Solution**:
```php
// 1. Rate Limiting on auth routes
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Custom rate limiting
RateLimiter::for('orders', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->user_id);
});

// 2. Design with transactions
DB::transaction(function () use ($orderData) {
    // All-or-nothing operations
    $order = Order::create($orderData);
    
    foreach ($orderData['items'] as $item) {
        // Stock check before creating
        $product = Product::lockForUpdate()->find($item['product_id']);
        
        if ($product->stock_quantity < $item['quantity']) {
            throw new \Exception('Insufficient stock');
        }
        
        OrderDetail::create([...]);
        $product->decrement('stock_quantity', $item['quantity']);
    }
}); // Auto-rollback on exception

// 3. Prevent race conditions
$product = Product::lockForUpdate()->find($productId);

// 4. Validation at business logic level
if ($order->status_id === ProductStatus::CONFIRMED) {
    throw new \Exception('Cannot modify confirmed order');
}
```

**Design Principles**:
- ✅ Fail secure - default deny access
- ✅ Complete mediation - check every request
- ✅ Separation of duties - admin vs user roles
- ✅ Defense in depth - multiple layers of security
- ✅ Least privilege - minimal permissions

**Checklist**:
- ✅ Rate limiting บน login/register (max 5/minute)
- ✅ Database transactions สำหรับ critical operations
- ✅ Pessimistic locking สำหรับ stock updates
- ✅ Business rule validation ก่อน database operations
- ✅ Idempotent operations (prevent double-submission)

---

### A05:2025 - Security Misconfiguration ⚙️

**ปัญหาในโค้ดเดิม**: Debug mode on, database errors exposed, no secure headers

**Laravel Solution**:
```php
// 1. Production environment (.env)
APP_ENV=production
APP_DEBUG=false
APP_LOG_LEVEL=error

// 2. Disable unnecessary routes
// routes/web.php - remove default welcome route
// routes/api.php - only enable if needed

// 3. Secure session configuration
// config/session.php
'lifetime' => 120,
'expire_on_close' => false,
'encrypt' => true,
'http_only' => true,
'same_site' => 'strict',
'secure' => true, // HTTPS only

// 4. CORS configuration (if API is used)
// config/cors.php
'allowed_origins' => [env('FRONTEND_URL')],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],

// 5. Security headers middleware
// app/Http/Middleware/SecurityHeaders.php
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

// 6. Hide server information
// public/.htaccess (for Apache)
ServerSignature Off
```

**Dependency Management**:
```bash
# Regular security audits
composer audit

# Update dependencies
composer update

# Lock file for reproducibility
# commit composer.lock to git
```

**Checklist**:
- ✅ `APP_DEBUG=false` in production
- ✅ Error pages ไม่แสดง stack traces
- ✅ ลบ default routes ที่ไม่ใช้
- ✅ Session cookies: httpOnly, secure, sameSite
- ✅ Security headers configured
- ✅ Regular `composer audit` and updates
- ✅ `.env.example` template (no real secrets)

---

### A06:2025 - Vulnerable and Outdated Components 📦

**Requirements**:
```json
// composer.json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0"
    }
}
```

**Maintenance Process**:
```bash
# 1. Check for vulnerabilities
composer audit

# 2. Review outdated packages
composer outdated

# 3. Update packages (test thoroughly)
composer update

# 4. Review security advisories
# https://github.com/advisories
# https://laravel.com/docs/releases
```

**Checklist**:
- ✅ PHP 8.3 or higher (LTS)
- ✅ Laravel 11.x (latest stable)
- ✅ Run `composer audit` ใน CI/CD pipeline
- ✅ Subscribe to Laravel security advisories
- ✅ Update dependencies monthly (with testing)
- ✅ Pin major versions in composer.json
- ✅ Keep `composer.lock` in version control

---

### A07:2025 - Identification and Authentication Failures 🔑

**ปัญหาในโค้ดเดิม**: ไม่มี rate limiting, weak password rules, password ถูก compare ตรง ๆ

**Laravel Solution**:
```php
// 1. Use Laravel Breeze for authentication scaffolding
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate

// 2. Strong password validation
// app/Http/Requests/RegisterRequest.php
public function rules()
{
    return [
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'confirmed',
            'min:8',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
        ],
    ];
}

// 3. Rate limiting on login
// routes/web.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute

// Custom throttling
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email);
});

// 4. Session security
// LoginController
Auth::login($user);
$request->session()->regenerate(); // Prevent session fixation

// 5. Logout properly
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

// 6. Prevent username enumeration
// Same error message for both invalid email and password
if (!Auth::attempt($credentials)) {
    return back()->withErrors([
        'email' => 'The provided credentials are incorrect.',
    ]);
}
```

**Multi-Factor Authentication (Optional but Recommended)**:
```bash
composer require laravel/fortify
# Configure 2FA in config/fortify.php
```

**Checklist**:
- ✅ Rate limiting: max 5 login attempts per minute
- ✅ Strong password policy (min 8 chars, complexity)
- ✅ Session regeneration after login
- ✅ Secure logout (invalidate session + regenerate token)
- ✅ No username enumeration (same error message)
- ✅ "Remember me" uses secure token
- ✅ Account lockout after repeated failures (optional)
- ✅ Email verification for new accounts (recommended)

---

### A08:2025 - Software and Data Integrity Failures 🛡️

**Laravel Solution**:
```php
// 1. Use composer.lock for reproducible builds
# Commit composer.lock to git
git add composer.lock
git commit -m "Lock dependencies"

// 2. Verify package integrity
composer validate --strict

// 3. Database migrations with version control
php artisan make:migration create_orders_table
# All migrations in git

// 4. Asset integrity (Vite)
// vite.config.js
export default defineConfig({
    build: {
        manifest: true,
    },
});

// 5. Audit logging for critical operations
use Illuminate\Support\Facades\Log;

Log::info('Order confirmed', [
    'order_id' => $order->order_id,
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
]);

// 6. Model events for data integrity
// app/Models/Order.php
protected static function booted()
{
    static::creating(function ($order) {
        $order->order_number = 'ORD-' . time() . '-' . rand(1000, 9999);
    });
    
    static::updating(function ($order) {
        if ($order->isDirty('total_amount')) {
            Log::warning('Order total changed', [
                'order_id' => $order->order_id,
                'old' => $order->getOriginal('total_amount'),
                'new' => $order->total_amount,
            ]);
        }
    });
}
```

**Checklist**:
- ✅ `composer.lock` in version control
- ✅ Use official Packagist sources only
- ✅ Database migrations for all schema changes
- ✅ Audit logging for: order creation, order confirmation, user registration
- ✅ Model events for tracking critical changes
- ✅ Asset integrity verification (SRI for CDN resources)

---

### A09:2025 - Security Logging and Monitoring Failures 📊

**Laravel Solution**:
```php
// 1. Configure logging channels
// config/logging.php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 90,
    ],
],

// 2. Log authentication events
// app/Providers/EventServiceProvider.php
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

protected $listen = [
    Login::class => [
        'App\Listeners\LogSuccessfulLogin',
    ],
    Failed::class => [
        'App\Listeners\LogFailedLogin',
    ],
];

// app/Listeners/LogSuccessfulLogin.php
public function handle(Login $event)
{
    Log::channel('security')->info('User logged in', [
        'user_id' => $event->user->user_id,
        'email' => $event->user->email,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}

// 3. Log authorization failures
// app/Exceptions/Handler.php
use Illuminate\Auth\Access\AuthorizationException;

public function register()
{
    $this->reportable(function (AuthorizationException $e) {
        Log::channel('security')->warning('Authorization failed', [
            'user_id' => auth()->id(),
            'policy' => $e->getMessage(),
            'ip' => request()->ip(),
        ]);
    });
}

// 4. Log critical business operations
// OrderService.php
public function confirmOrder(Order $order, string $address)
{
    DB::transaction(function () use ($order, $address) {
        $order->update([
            'shipping_address' => $address,
            'status_id' => ProductStatus::CONFIRMED,
        ]);
        
        Log::channel('security')->info('Order confirmed', [
            'order_id' => $order->order_id,
            'order_number' => $order->order_number,
            'user_id' => auth()->id(),
            'total_amount' => $order->total_amount,
        ]);
    });
}

// 5. Never log sensitive data
// ❌ WRONG
Log::info('User login attempt', ['password' => $password]);

// ✅ CORRECT
Log::info('User login attempt', ['email' => $email]);
```

**What to Log**:
- ✅ Authentication: login success/failure, logout
- ✅ Authorization: access denied, policy violations
- ✅ Order operations: create, update, confirm, bulk operations
- ✅ User operations: registration, profile updates
- ✅ Admin actions: bulk confirms, searches
- ✅ System errors: exceptions, database errors
- ✅ Security events: rate limit exceeded, suspicious activity

**What NOT to Log**:
- ❌ Passwords (plaintext or hashed)
- ❌ Session tokens
- ❌ API keys
- ❌ Credit card numbers (if applicable)
- ❌ Personal sensitive information (unless encrypted)

**Checklist**:
- ✅ Separate security log channel
- ✅ Log retention: minimum 90 days
- ✅ Log all authentication attempts
- ✅ Log authorization failures
- ✅ Log critical business operations
- ✅ Include context: user_id, IP, timestamp
- ✅ Use appropriate log levels
- ✅ Never log credentials or tokens

---

### A10:2025 - Server-Side Request Forgery (SSRF) 🌐

**Context**: อาจไม่ relate โดยตรงกับ order management system แต่ถ้าต้อง fetch external resources

**Laravel Solution**:
```php
// If fetching product images from URLs
use Illuminate\Support\Facades\Http;

// 1. Whitelist allowed domains
$allowedDomains = [
    'cdn.example.com',
    'images.example.com',
];

$parsedUrl = parse_url($request->image_url);
if (!in_array($parsedUrl['host'], $allowedDomains)) {
    throw new \Exception('Invalid image source');
}

// 2. Use HTTP client with timeouts
$response = Http::timeout(5)
    ->withoutRedirecting()
    ->get($validatedUrl);

// 3. Validate response content type
if (!str_starts_with($response->header('Content-Type'), 'image/')) {
    throw new \Exception('Invalid content type');
}

// 4. Never allow user-controlled redirects
// Disable automatic redirects
Http::withoutRedirecting()->get($url);
```

**Checklist** (if applicable):
- ✅ Whitelist allowed domains
- ✅ Disable automatic redirects
- ✅ Validate response content type
- ✅ Set connection timeouts (5-10 seconds)
- ✅ Block internal IP ranges (127.0.0.1, 10.x.x.x, etc.)

---

## Database Design (Laravel Migration)

### Schema Overview

**Tables to Migrate** (from existing schema):
1. `product_status` - Reference data (PENDING, CONFIRMED)
2. `users` - User accounts with roles
3. `products` - Product catalog
4. `orders` - Order headers
5. `order_details` - Order line items

### Eloquent Models & Relationships

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    
    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'password',
        'is_admin',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'is_admin' => 'boolean',
        'email_verified_at' => 'datetime',
    ];
    
    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

// app/Models/Product.php
class Product extends Model
{
    protected $primaryKey = 'product_id';
    
    protected $fillable = [
        'product_number',
        'product_name',
        'product_description',
        'price',
        'stock_quantity',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];
    
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id', 'product_id');
    }
}

// app/Models/Order.php
class Order extends Model
{
    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'order_number',
        'user_id',
        'status_id',
        'shipping_address',
        'total_amount',
    ];
    
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'status_id', 'status_id');
    }
    
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_id', ProductStatus::PENDING);
    }
    
    public function scopeConfirmed($query)
    {
        return $query->where('status_id', ProductStatus::CONFIRMED);
    }
}

// app/Models/OrderDetail.php
class OrderDetail extends Model
{
    protected $primaryKey = 'order_detail_id';
    
    protected $fillable = [
        'order_id',
        'product_id',
        'product_number',
        'quantity',
        'unit_price',
        'subtotal',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];
    
    public $timestamps = false; // No created_at/updated_at
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}

// app/Models/ProductStatus.php
class ProductStatus extends Model
{
    protected $table = 'product_status';
    protected $primaryKey = 'status_id';
    
    public $timestamps = false;
    
    const PENDING = 1;
    const CONFIRMED = 2;
    
    protected $fillable = [
        'status_code',
        'status_name',
    ];
    
    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id', 'status_id');
    }
}
```

### Important Migration Notes

**Critical Changes from Old Schema**:
- ✅ `users.password` column: **Must use `Hash::make()`** - no plaintext storage
- ✅ Add indexes for performance (`email`, `order_number`, `product_number`)
- ✅ Foreign key constraints with proper cascading (DELETE CASCADE for order_details)
- ✅ Use Laravel's `timestamps()` instead of manual CURRENT_TIMESTAMP
- ✅ Proper decimal precision for monetary values (10,2)

---

## Laravel Project Structure

**Required folder structure**:

```
phpdemo-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php
│   │   │   └── Admin/
│   │   │       └── OrderController.php
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── CreateOrderRequest.php
│   │   │   ├── UpdateOrderRequest.php
│   │   │   └── ConfirmOrderRequest.php
│   │   └── Middleware/
│   │       ├── EnsureUserIsAdmin.php
│   │       └── SecurityHeaders.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── OrderDetail.php
│   │   └── ProductStatus.php
│   ├── Policies/
│   │   └── OrderPolicy.php
│   ├── Services/
│   │   └── OrderService.php
│   └── Providers/
│       └── AuthServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_product_status_table.php
│   │   ├── 2024_01_01_000002_create_users_table.php
│   │   ├── 2024_01_01_000003_create_products_table.php
│   │   ├── 2024_01_01_000004_create_orders_table.php
│   │   └── 2024_01_01_000005_create_order_details_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── ProductStatusSeeder.php
│   │   ├── ProductSeeder.php
│   │   └── UserSeeder.php
│   └── factories/
│       ├── UserFactory.php
│       ├── ProductFactory.php
│       └── OrderFactory.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── orders/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       └── admin/
│           └── orders/
│               └── index.blade.php
├── routes/
│   ├── web.php
│   └── api.php (optional)
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   └── RegisterTest.php
│   │   ├── OrderTest.php
│   │   └── Admin/
│   │       └── OrderManagementTest.php
│   └── Unit/
│       └── Services/
│           └── OrderServiceTest.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── session.php
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Migration Path (Step-by-Step Guide)

### Phase 1: Laravel Setup

```bash
# 1. Create new Laravel project
composer create-project laravel/laravel phpdemo-laravel "11.*"
cd phpdemo-laravel

# 2. Install authentication
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# Edit .env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=order_db
DB_USERNAME=root
DB_PASSWORD=secret
```

### Phase 2: Database Migration

```bash
# 1. Create migrations based on sql/schema.sql
php artisan make:migration create_product_status_table
php artisan make:migration create_users_table
php artisan make:migration create_products_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_details_table

# 2. Create seeders based on sql/seed.sql
php artisan make:seeder ProductStatusSeeder
php artisan make:seeder ProductSeeder
php artisan make:seeder UserSeeder

# 3. Run migrations
php artisan migrate
php artisan db:seed
```

### Phase 3: Model Creation

```bash
# Create models (already scaffolded by migrations if using --model flag)
# Define relationships, fillable, casts as shown in Database Design section
```

### Phase 4: Service Layer Migration

**Transform business logic**:
- `includes/functions.php::processOrder()` → `OrderService::createOrder()`
- `includes/functions.php::updateOrderItems()` → `OrderService::updateOrder()`
- `includes/functions.php::confirmOrderWithAddress()` → `OrderService::confirmOrder()`
- `includes/functions.php::searchOrders()` → Eloquent scopes and queries
- `includes/functions.php::bulkConfirmOrders()` → `OrderService::bulkConfirm()`

### Phase 5: Controller & Route Migration

```bash
php artisan make:controller OrderController --resource
php artisan make:controller Admin/OrderController
```

**Mapping**:
- `public/order.php` → `routes/web.php` + `OrderController@create`
- `public/update_order.php` → `OrderController@edit`
- `public/confirm_order.php` → `OrderController@confirm`
- `public/admin/orders.php` → `Admin/OrderController@index`
- `public/admin/confirm_orders.php` → `Admin/OrderController@bulkConfirm`

### Phase 6: Authentication Migration

**Replace**:
- `public/login.php` → Laravel Breeze login
- `public/register.php` → Laravel Breeze register
- `public/logout.php` → Laravel Breeze logout

**Fix security issues**:
- ❌ Remove plaintext password comparison
- ✅ Use `Hash::check()`
- ✅ Add rate limiting
- ✅ Session regeneration

### Phase 7: Authorization with Policies

```bash
php artisan make:policy OrderPolicy --model=Order
php artisan make:middleware EnsureUserIsAdmin
```

**Implement ownership checks** to prevent users from accessing others' orders.

### Phase 8: Testing

```bash
php artisan make:test Auth/LoginTest
php artisan make:test Auth/RegisterTest
php artisan make:test OrderTest
php artisan make:test Admin/OrderManagementTest

# Run tests
php artisan test --coverage
```

### Phase 9: Docker Configuration Update

**Update Dockerfile** for PHP 8.3 + Composer:
```dockerfile
FROM php:8.3-fpm-alpine
# Install extensions, Composer, etc.
```

**Update docker-compose.yml**:
```yaml
services:
  app:
    build: .
    # PHP 8.3 configuration
  db:
    image: mysql:8.0
  redis:
    image: redis:alpine
```

---

## Code Quality & Testing Standards

### PSR-12 Compliance

```bash
composer require laravel/pint --dev
./vendor/bin/pint --test  # Check
./vendor/bin/pint         # Fix
```

### Static Analysis

```bash
composer require nunomaduro/larastan:^2.0 --dev
./vendor/bin/phpstan analyse --memory-limit=2G --level=5
```

### Testing Requirements

- ✅ Feature tests for all critical flows
- ✅ Unit tests for service classes
- ✅ Minimum 80% coverage for critical paths
- ✅ Authorization tests (403 forbidden responses)

---

## What to AVOID (Security Anti-Patterns)

### 🚫 Never Do These

```php
// ❌ Plaintext passwords
$user->password = $request->password;

// ❌ SQL injection
$user = DB::select("SELECT * FROM users WHERE email = '$email'");

// ❌ No authorization
public function update(Order $order) {
    $order->update(...); // Anyone can update!
}

// ❌ No CSRF protection
protected $except = ['*']; // in VerifyCsrfToken

// ❌ Debug in production
APP_DEBUG=true

// ❌ Hardcoded credentials
$conn = new PDO('mysql:host=localhost', 'root', 'password');
```

### ✅ Always Do These

```php
// ✅ Hash passwords
$user->password = Hash::make($request->password);

// ✅ Use Eloquent
$user = User::where('email', $email)->first();

// ✅ Check authorization
$this->authorize('update', $order);

// ✅ CSRF in forms
<form>@csrf</form>

// ✅ Disable debug
APP_DEBUG=false

// ✅ Use .env
DB_PASSWORD=env('DB_PASSWORD')
```

---

## Summary: Transformation Checklist

### ✅ Infrastructure
- [ ] PHP 8.3 configured
- [ ] Laravel 11 installed
- [ ] Docker updated (PHP 8.3, MySQL 8.0, Redis)
- [ ] `.env` configured (no hardcoded secrets)

### ✅ Security (OWASP Top 10)
- [ ] A01: Authorization policies implemented
- [ ] A02: Password hashing with `Hash::make()`
- [ ] A03: No SQL injection (Eloquent only)
- [ ] A04: Rate limiting + transactions
- [ ] A05: `APP_DEBUG=false`, security headers
- [ ] A06: PHP 8.3 + Laravel 11, regular audits
- [ ] A07: Strong password rules, rate limiting
- [ ] A08: `composer.lock` committed, audit logging
- [ ] A09: Security logging configured
- [ ] A10: URL validation (if applicable)

### ✅ Features
- [ ] User registration/login (Laravel Breeze)
- [ ] Order CRUD with authorization
- [ ] Admin features (search, bulk confirm)
- [ ] All features tested

### ✅ Code Quality
- [ ] PSR-12 compliant (Laravel Pint)
- [ ] PHPStan level 5+
- [ ] Test coverage ≥80%
- [ ] MVC separation maintained

---

**คู่มือนี้จะช่วยให้ Copilot สร้างโค้ด Laravel ที่ทันสมัย ปลอดภัย และพร้อมใช้งาน production โดยแปลงจากโครงสร้างเดิมที่มีช่องโหว่ไปสู่ best practices ตามมาตรฐาน OWASP Top 10 2025**




