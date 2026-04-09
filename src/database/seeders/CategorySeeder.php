<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Characteristic;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Лебёдки', 'code' => 'winches', 'chars' => ['Грузоподъёмность (т)', 'Длина каната (м)', 'Мощность (кВт)']],
            ['name' => 'Стропы', 'code' => 'slings', 'chars' => ['Грузоподъёмность (т)', 'Длина (м)', 'Материал']],
            ['name' => 'Захваты', 'code' => 'grips', 'chars' => ['Грузоподъёмность (т)', 'Тип', 'Ширина захвата (мм)']],
            ['name' => 'Канаты', 'code' => 'ropes', 'chars' => ['Диаметр (мм)', 'Длина (м)', 'Разрывное усилие (кН)']],
        ];

        foreach ($categories as $data) {
            $category = Category::create([
                'name' => $data['name'],
                'code' => $data['code'],
            ]);

            foreach ($data['chars'] as $char) {
                Characteristic::create([
                    'category_id' => $category->id,
                    'name'        => $char,
                ]);
            }
        }
    }
}