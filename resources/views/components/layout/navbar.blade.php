<nav class="bg-primary-600 text-white shadow-lg" x-data="{ mobileMenuOpen: false }">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo/Brand -->
            <div class="flex items-center">
                <a href="{{ route('products.index') }}" class="text-xl font-bold hover:text-primary-100 transition-smooth">
                    {{ config('app.name') }}
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center space-x-6">
                @auth
                    <a href="{{ route('products.index') }}" 
                       class="hover:text-primary-200 transition-smooth {{ request()->routeIs('products.*') ? 'font-semibold' : '' }}">
                        รายการสินค้า
                    </a>
                    <a href="{{ route('orders.index') }}" 
                       class="hover:text-primary-200 transition-smooth {{ request()->routeIs('orders.*') ? 'font-semibold' : '' }}">
                        ออเดอร์ของฉัน
                    </a>
                    <a href="{{ route('orders.create') }}" 
                       class="hover:text-primary-200 transition-smooth">
                        สร้างออเดอร์
                    </a>
                    
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-warning-500 hover:bg-warning-600 px-3 py-1.5 rounded-lg transition-smooth {{ request()->routeIs('admin.dashboard') ? 'ring-2 ring-white' : '' }}">
                            📊 Dashboard
                        </a>
                        <a href="{{ route('admin.orders.index') }}" 
                           class="bg-warning-500 hover:bg-warning-600 px-3 py-1.5 rounded-lg transition-smooth {{ request()->routeIs('admin.orders.*') ? 'ring-2 ring-white' : '' }}">
                            จัดการออเดอร์
                        </a>
                    @endif
                    
                    <span class="text-sm text-primary-100">
                        👤 {{ auth()->user()->full_name }}
                    </span>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-primary-200 transition-smooth">
                            ออกจากระบบ
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-primary-200 transition-smooth">
                        เข้าสู่ระบบ
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-white text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition-smooth font-semibold">
                        สมัครสมาชิก
                    </a>
                @endauth
            </div>
            
            <!-- Mobile Menu Button -->
            <div class="lg:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2 rounded-lg hover:bg-primary-700 transition-smooth"
                        aria-label="Toggle mobile menu"
                        :aria-expanded="mobileMenuOpen.toString()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="lg:hidden pb-4"
             style="display: none;">
            @auth
                <a href="{{ route('products.index') }}" 
                   class="block py-2 px-4 hover:bg-primary-700 rounded transition-smooth {{ request()->routeIs('products.*') ? 'bg-primary-700' : '' }}">
                    รายการสินค้า
                </a>
                <a href="{{ route('orders.index') }}" 
                   class="block py-2 px-4 hover:bg-primary-700 rounded transition-smooth {{ request()->routeIs('orders.*') ? 'bg-primary-700' : '' }}">
                    ออเดอร์ของฉัน
                </a>
                <a href="{{ route('orders.create') }}" 
                   class="block py-2 px-4 hover:bg-primary-700 rounded transition-smooth">
                    สร้างออเดอร์
                </a>
                
                @if(auth()->user()->is_admin)
                    <div class="my-2 border-t border-primary-500"></div>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="block py-2 px-4 bg-warning-500 hover:bg-warning-600 rounded transition-smooth mb-2">
                        📊 Dashboard
                    </a>
                    <a href="{{ route('admin.orders.index') }}" 
                       class="block py-2 px-4 bg-warning-500 hover:bg-warning-600 rounded transition-smooth">
                        จัดการออเดอร์ (Admin)
                    </a>
                @endif
                
                <div class="my-2 border-t border-primary-500"></div>
                <div class="py-2 px-4 text-sm text-primary-100">
                    👤 {{ auth()->user()->full_name }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left py-2 px-4 hover:bg-primary-700 rounded transition-smooth">
                        ออกจากระบบ
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block py-2 px-4 hover:bg-primary-700 rounded transition-smooth">
                    เข้าสู่ระบบ
                </a>
                <a href="{{ route('register') }}" 
                   class="block py-2 px-4 mt-2 bg-white text-primary-600 rounded-lg hover:bg-primary-50 transition-smooth font-semibold text-center">
                    สมัครสมาชิก
                </a>
            @endauth
        </div>
    </div>
</nav>
