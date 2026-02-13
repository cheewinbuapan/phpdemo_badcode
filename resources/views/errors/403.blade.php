@extends('layouts.app')

@section('title', '- ไม่มีสิทธิ์เข้าถึง')

@section('content')
<div class="max-w-2xl mx-auto text-center py-12">
    <div class="bg-red-50 border border-red-200 rounded-lg p-8">
        <svg class="mx-auto h-24 w-24 text-red-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        
        <h1 class="text-4xl font-bold text-red-700 mb-4">403</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">ไม่มีสิทธิ์เข้าถึง</h2>
        
        <p class="text-gray-600 mb-6">
            {{ $exception->getMessage() ?: 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ กรุณาติดต่อผู้ดูแลระบบหากคุณคิดว่านี่เป็นความผิดพลาด' }}
        </p>
        
        <div class="space-x-4">
            <a 
                href="{{ route('products.index') }}" 
                class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-bold"
            >
                กลับหน้าหลัก
            </a>
            
            @auth
                @if(!auth()->user()->is_admin)
                    <a 
                        href="{{ route('orders.index') }}" 
                        class="inline-block bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold"
                    >
                        ออเดอร์ของฉัน
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection
