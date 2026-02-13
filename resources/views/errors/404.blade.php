@extends('layouts.app')

@section('title', '- ไม่พบหน้าที่ค้นหา')

@section('content')
<div class="max-w-2xl mx-auto text-center py-12">
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8">
        <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        
        <h1 class="text-6xl font-bold text-gray-700 mb-4">404</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">ไม่พบหน้าที่ค้นหา</h2>
        
        <p class="text-gray-600 mb-6">
            ขออภัย หน้าที่คุณกำลังมองหาไม่มีอยู่ในระบบ อาจถูกย้าย ลบ หรือไม่เคยมีอยู่เลย
        </p>
        
        <div class="space-x-4">
            <a 
                href="{{ route('products.index') }}" 
                class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-bold"
            >
                กลับหน้าหลัก
            </a>
            
            @auth
                <a 
                    href="{{ route('orders.index') }}" 
                    class="inline-block bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold"
                >
                    ออเดอร์ของฉัน
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
