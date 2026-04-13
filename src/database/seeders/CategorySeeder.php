<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_characteristics')->delete();
        DB::table('characteristics')->delete();
        DB::table('categories')->delete();

        $categories = [
            [
                'name' => 'Тали электрические',
                'code' => 'electric-hoists',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Высота подъёма',
                    'Мощность двигателя',
                    'Скорость подъёма',
                    'Напряжение питания',
                    'Тип управления',
                    'Степень защиты IP',
                    'Вес',
                ],
            ],
            [
                'name' => 'Тали ручные',
                'code' => 'manual-hoists',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Высота подъёма',
                    'Тип привода',
                    'Длина тяговой цепи',
                    'КПД',
                    'Вес',
                ],
            ],
            [
                'name' => 'Лебёдки',
                'code' => 'winches',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Длина троса',
                    'Диаметр троса',
                    'Мощность двигателя',
                    'Тип привода',
                    'Напряжение питания',
                    'Скорость подъёма',
                    'Вес',
                ],
            ],
            [
                'name' => 'Краны',
                'code' => 'cranes',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Пролёт',
                    'Высота подъёма',
                    'Скорость передвижения',
                    'Тип крана',
                    'Режим работы',
                    'Вес',
                ],
            ],
            [
                'name' => 'Стропы',
                'code' => 'slings',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Длина',
                    'Материал',
                    'Количество ветвей',
                    'Тип исполнения',
                    'Диаметр/ширина',
                    'Вес',
                ],
            ],
            [
                'name' => 'Домкраты',
                'code' => 'jacks',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Высота подъёма',
                    'Начальная высота',
                    'Тип',
                    'Рабочее давление',
                    'Вес',
                ],
            ],
            [
                'name' => 'Тележки',
                'code' => 'trolleys',
                'characteristics' => [
                    'Грузоподъёмность',
                    'Тип привода',
                    'Ширина полки двутавра',
                    'Скорость передвижения',
                    'Напряжение питания',
                    'Вес',
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $categoryId = DB::table('categories')->insertGetId([
                'name'       => $cat['name'],
                'code'       => $cat['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cat['characteristics'] as $charName) {
                DB::table('characteristics')->insert([
                    'category_id' => $categoryId,
                    'name'        => $charName,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}