@extends('layouts.app')

@section('title', '- เข้าสู่ระบบ')

@section('content')
<div class="max-w-md mx-auto">
    <x-ui.card class="fade-in">
        <h2 class="text-2xl font-bold mb-6 text-center text-secondary-900">เข้าสู่ระบบ</h2>
        
        <form method="POST" action="{{ route('login') }}" 
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

            <x-form.input 
                name="password" 
                type="password"
                label="รหัสผ่าน"
                placeholder="••••••••"
                required
                class="mb-6"
            />

            <div class="mb-6">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-secondary-700">จดจำฉันไว้ในระบบ</span>
                </label>
            </div>

            <x-ui.button 
                type="submit" 
                variant="primary"
                class="w-full"
                x-bind:loading="loading"
            >
                เข้าสู่ระบบ
            </x-ui.button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('register') }}" class="text-primary-600 hover:text-primary-700 font-medium transition-smooth">
                ยังไม่มีบัญชี? สมัครสมาชิก
            </a>
        </div>
    </x-ui.card>
</div>
@endsection
