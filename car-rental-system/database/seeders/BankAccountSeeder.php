<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'bank_name' => 'Afghan United Bank',
                'account_name' => 'Car Rental Company',
                'account_number' => '1234567890',
                'branch' => 'Kabul Main Branch',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'bank_name' => 'Azizi Bank',
                'account_name' => 'Car Rental Company',
                'account_number' => '0987654321',
                'branch' => 'Shar-e-Naw Branch',
                'is_active' => true,
                'display_order' => 2,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::firstOrCreate(
                ['account_number' => $account['account_number']],
                $account
            );
        }

        $this->command->info('✅ Bank accounts seeded successfully.');
    }
}