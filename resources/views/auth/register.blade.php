@extends('layouts.app')

@section('title', '- สมัครสมาชิก')

@section('content')
<div class="max-w-md mx-auto">
    <x-ui.card class="fade-in">
        <h2 class="text-2xl font-bold mb-6 text-center text-secondary-900">สมัครสมาชิก</h2>
        
        <form method="POST" action="{{ route('register') }}"
              x-data="{ loading: false }" 
              x-on:submit="loading = true">
            @csrf
            
            <x-form.input 
                name="email" 
                type="email"
                label="อีเมล"
                placeholder="your@email.com"
                required
                autofocus
                class="mb-4"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-form.input 
                    name="first_name" 
                    label="ชื่อ"
                    placeholder="ระบุชื่อ"
                    required
                />

                <x-form.input 
                    name="last_name" 
                    label="นามสกุล"
                    placeholder="ระบุนามสกุล"
                    required
                />
            </div>

            <x-form.input 
                name="phone" 
                type="tel"
                label="เบอร์โทรศัพท์"
                placeholder="0812345678"
                required
                class="mb-4"
            />

            <x-form.input 
                name="password" 
                type="password"
                label="รหัสผ่าน"
                placeholder="••••••••"
                required
                class="mb-2"
            />
            <p class="text-xs text-secondary-500 mb-4">
                💡 รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์เล็ก พิมพ์ใหญ่ ตัวเลข และสัญลักษณ์
            </p>

            <x-form.input 
                name="password_confirmation" 
                type="password"
                label="ยืนยันรหัสผ่าน"
                placeholder="••••••••"
                required
                class="mb-6"
            />

            <x-ui.button 
                type="submit" 
                variant="primary"
                class="w-full"
                x-bind:loading="loading"
            >
                สมัครสมาชิก
            </x-ui.button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium transition-smooth">
                มีบัญชีแล้ว? เข้าสู่ระบบ
            </a>
        </div>
    </x-ui.card>
</div>
@endsection
