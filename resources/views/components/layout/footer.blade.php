<footer class="bg-secondary-800 text-white mt-12 py-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Company Info -->
            <div>
                <h3 class="text-lg font-bold mb-3">{{ config('app.name') }}</h3>
                <p class="text-secondary-300 text-sm leading-relaxed">
                    ระบบจัดการออเดอร์ที่ทันสมัย ปลอดภัย และใช้งานง่าย
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-3">ลิงก์ด่วน</h3>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li>
                            <a href="{{ route('products.index') }}" class="text-secondary-300 hover:text-white transition-smooth">
                                รายการสินค้า
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('orders.index') }}" class="text-secondary-300 hover:text-white transition-smooth">
                                ออเดอร์ของฉัน
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="text-secondary-300 hover:text-white transition-smooth">
                                เข้าสู่ระบบ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="text-secondary-300 hover:text-white transition-smooth">
                                สมัครสมาชิก
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
            
            <!-- Tech Info -->
            <div>
                <h3 class="text-lg font-bold mb-3">เทคโนโลยี</h3>
                <ul class="text-secondary-300 text-sm space-y-1">
                    <li>✅ Laravel 11</li>
                    <li>✅ PHP 8.3</li>
                    <li>✅ Tailwind CSS</li>
                    <li>✅ Alpine.js</li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-secondary-700 mt-8 pt-6 text-center text-sm text-secondary-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. ปลอดภัยด้วย Laravel 11 | พัฒนาตาม OWASP Top 10 2025</p>
        </div>
    </div>
</footer>
