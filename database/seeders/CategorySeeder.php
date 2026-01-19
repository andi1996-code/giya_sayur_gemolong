<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $now = Carbon::now();

        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Buah-Buahan',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'name' => 'Sayur-Sayuran',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
