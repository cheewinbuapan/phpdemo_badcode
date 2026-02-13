<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'email' => 'admin@test.com',
                'first_name' => 'Admin',
                'last_name' => 'System',
                'phone' => '0801111111',
                'password' => Hash::make('Admin@123'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'user1@test.com',
                'first_name' => 'สมชาย',
                'last_name' => 'ใจดี',
                'phone' => '0812222222',
                'password' => Hash::make('User@123'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'user2@test.com',
                'first_name' => 'สมหญิง',
                'last_name' => 'รักสวย',
                'phone' => '0823333333',
                'password' => Hash::make('User@123'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
