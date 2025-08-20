<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CashAccount;

class CashAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Tiền mặt',
                'type' => 'cash',
                'balance' => 10000000,
                'description' => 'Tiền mặt tại quỹ',
                'is_active' => true,
            ],
            [
                'name' => 'Tài khoản Vietcombank',
                'type' => 'bank',
                'account_number' => '1234567890',
                'bank_name' => 'Vietcombank',
                'balance' => 50000000,
                'description' => 'Tài khoản ngân hàng chính',
                'is_active' => true,
            ],
            [
                'name' => 'Tài khoản BIDV',
                'type' => 'bank',
                'account_number' => '0987654321',
                'bank_name' => 'BIDV',
                'balance' => 25000000,
                'description' => 'Tài khoản ngân hàng phụ',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            CashAccount::create($account);
        }
    }
}
