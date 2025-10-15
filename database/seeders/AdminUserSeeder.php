<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tài khoản admin nếu chưa tồn tại
        $adminExists = User::where('name', 'namhai')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'namhai',
                'email' => 'namhai@perfumeshop.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Tài khoản admin đã được tạo:');
            $this->command->info('Tên đăng nhập: namhai');
            $this->command->info('Mật khẩu: 123456');
        } else {
            $this->command->info('Tài khoản admin đã tồn tại.');
        }
    }
}
