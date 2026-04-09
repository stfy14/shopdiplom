<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCharacteristic;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $winches = Category::where('code', 'winches')->first();
        $slings  = Category::where('code', 'slings')->first();
        $grips   = Category::where('code', 'grips')->first();
        $ropes   = Category::where('code', 'ropes')->first();

        $products = [
            [
                'category'  => $winches,
                'title'     => 'Лебёдка электрическая ЛЭ-1000',
                'price'     => 85000,
                'quantity'  => 5,
                'discount'  => 10,
                'description' => 'Электрическая лебёдка грузоподъёмностью 1 тонна для промышленного использования.',
                'chars'     => ['Грузоподъёмность (т)' => '1', 'Длина каната (м)' => '20', 'Мощность (кВт)' => '2.2'],
            ],
            [
                'category'  => $winches,
                'title'     => 'Лебёдка ручная ЛР-500',
                'price'     => 12000,
                'quantity'  => 15,
                'discount'  => 0,
                'description' => 'Ручная рычажная лебёдка для такелажных работ.',
                'chars'     => ['Грузоподъёмность (т)' => '0.5', 'Длина каната (м)' => '10', 'Мощность (кВт)' => '—'],
            ],
            [
                'category'  => $slings,
                'title'     => 'Строп текстильный СТП 2т',
                'price'     => 1800,
                'quantity'  => 50,
                'discount'  => 0,
                'description' => 'Петлевой строп из высокопрочного полиэстера.',
                'chars'     => ['Грузоподъёмность (т)' => '2', 'Длина (м)' => '3', 'Материал' => 'Полиэстер'],
            ],
            [
                'category'  => $slings,
                'title'     => 'Строп цепной СЦ 3т',
                'price'     => 8500,
                'quantity'  => 20,
                'discount'  => 5,
                'description' => 'Цепной строп для работы с острыми кромками.',
                'chars'     => ['Грузоподъёмность (т)' => '3', 'Длина (м)' => '2', 'Материал' => 'Сталь'],
            ],
            [
                'category'  => $grips,
                'title'     => 'Захват листовой ЗЛ-1',
                'price'     => 15000,
                'quantity'  => 8,
                'discount'  => 0,
                'description' => 'Захват для подъёма листового металла.',
                'chars'     => ['Грузоподъёмность (т)' => '1', 'Тип' => 'Листовой', 'Ширина захвата (мм)' => '0-40'],
            ],
            [
                'category'  => $ropes,
                'title'     => 'Канат стальной ГОСТ 2688',
                'price'     => 4500,
                'quantity'  => 0,
                'discount'  => 0,
                'description' => 'Канат двойной свивки типа ЛК-Р конструкции 6х19.',
                'chars'     => ['Диаметр (мм)' => '12', 'Длина (м)' => '100', 'Разрывное усилие (кН)' => '85'],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'title'       => $data['title'],
                'price'       => $data['price'],
                'quantity'    => $data['quantity'],
                'discount'    => $data['discount'],
                'description' => $data['description'],
                'category_id' => $data['category']->id,
            ]);

            $chars = $data['category']->characteristics;
            foreach ($data['chars'] as $charName => $value) {
                $char = $chars->firstWhere('name', $charName);
                if ($char) {
                    ProductCharacteristic::create([
                        'product_id'        => $product->id,
                        'characteristic_id' => $char->id,
                        'value'             => $value,
                    ]);
                }
            }
        }
    }
}