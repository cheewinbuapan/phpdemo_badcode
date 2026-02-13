<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_status')->insert([
            [
                'status_id' => 1,
                'status_code' => 'PENDING',
                'status_name' => 'รอจัดส่ง',
            ],
            [
                'status_id' => 2,
                'status_code' => 'CONFIRMED',
                'status_name' => 'ยืนยันแล้ว',
            ],
        ]);
    }
}
