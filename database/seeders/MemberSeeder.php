<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use Carbon\Carbon;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'budi@example.com',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'birth_date' => '1990-05-15',
                'gender' => 'male',
                'tier' => 'gold',
                'total_points' => 150,
                'lifetime_points' => 250,
                'total_spent' => 5500000,
                'total_transactions' => 25,
                'registered_date' => Carbon::now()->subMonths(6),
                'last_transaction_date' => Carbon::now()->subDays(3),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'phone' => '081234567891',
                'email' => 'siti@example.com',
                'address' => 'Jl. Sudirman No. 456, Bandung',
                'birth_date' => '1985-08-20',
                'gender' => 'female',
                'tier' => 'platinum',
                'total_points' => 500,
                'lifetime_points' => 1200,
                'total_spent' => 15000000,
                'total_transactions' => 80,
                'registered_date' => Carbon::now()->subYear(),
                'last_transaction_date' => Carbon::now()->subDay(),
            ],
            [
                'name' => 'Ahmad Hidayat',
                'phone' => '081234567892',
                'email' => 'ahmad@example.com',
                'address' => 'Jl. Gatot Subroto No. 789, Surabaya',
                'birth_date' => '1995-03-10',
                'gender' => 'male',
                'tier' => 'silver',
                'total_points' => 50,
                'lifetime_points' => 80,
                'total_spent' => 2500000,
                'total_transactions' => 15,
                'registered_date' => Carbon::now()->subMonths(3),
                'last_transaction_date' => Carbon::now()->subWeek(),
            ],
            [
                'name' => 'Dewi Lestari',
                'phone' => '081234567893',
                'email' => 'dewi@example.com',
                'address' => 'Jl. Ahmad Yani No. 321, Yogyakarta',
                'birth_date' => '1992-11-25',
                'gender' => 'female',
                'tier' => 'bronze',
                'total_points' => 20,
                'lifetime_points' => 20,
                'total_spent' => 500000,
                'total_transactions' => 5,
                'registered_date' => Carbon::now()->subMonth(),
                'last_transaction_date' => Carbon::now()->subDays(5),
            ],
            [
                'name' => 'Rudi Hartono',
                'phone' => '081234567894',
                'email' => 'rudi@example.com',
                'address' => 'Jl. Diponegoro No. 111, Semarang',
                'birth_date' => '1988-07-18',
                'gender' => 'male',
                'tier' => 'silver',
                'total_points' => 75,
                'lifetime_points' => 120,
                'total_spent' => 3000000,
                'total_transactions' => 18,
                'registered_date' => Carbon::now()->subMonths(4),
                'last_transaction_date' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($members as $member) {
            Member::create($member);
        }

        $this->command->info('Sample members created successfully!');
    }
}
