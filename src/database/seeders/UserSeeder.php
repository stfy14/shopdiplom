<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->delete();

        DB::table('users')->insert([
            [
                'name'       => 'Администратор',
                'email'      => 'admin@shop.ru',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Алексей Громов',
                'email'      => 'gromov@mail.ru',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'created_at' => now()->subDays(60),
                'updated_at' => now()->subDays(60),
            ],
            [
                'name'       => 'Марина Соколова',
                'email'      => 'sokolova@gmail.com',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'created_at' => now()->subDays(45),
                'updated_at' => now()->subDays(45),
            ],
            [
                'name'       => 'Дмитрий Захаров',
                'email'      => 'zakharov@yandex.ru',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'name'       => 'Ольга Петрова',
                'email'      => 'petrova@inbox.ru',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(20),
            ],
            [
                'name'       => 'Сергей Никитин',
                'email'      => 'nikitin@mail.ru',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
        ]);
    }
}