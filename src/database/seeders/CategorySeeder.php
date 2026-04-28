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
        DB::table('products')->delete();
        DB::table('categories')->delete();

        // ── Родительские категории ──────────────────────────────────────────
        $parents = [
            [
                'name'        => 'Такелажное оборудование',
                'code'        => 'rigging',
                'description' => 'Профессиональное такелажное оборудование для строповки, крепления и натяжения грузов. Зажимы для каната, талрепы, стропы всех типов.',
                'image'       => null,
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Грузоподъёмное оборудование',
                'code'        => 'lifting-equipment',
                'description' => 'Тали, лебёдки, краны и кран-балки для подъёма и перемещения грузов на производстве, в цехах и на строительных объектах.',
                'image'       => null,
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Гидравлическое оборудование',
                'code'        => 'hydraulic',
                'description' => 'Гидравлические домкраты для подъёма автомобилей, оборудования и строительных конструкций. Бутылочные, подкатные, гаражные.',
                'image'       => null,
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Складское оборудование',
                'code'        => 'warehouse',
                'description' => 'Грузовые тележки, ходовые тележки для талей и кран-балок. Ручные и электрические модели для склада и производства.',
                'image'       => null,
                'sort_order'  => 4,
            ],
        ];

        $parentIds = [];
        foreach ($parents as $parent) {
            $id = DB::table('categories')->insertGetId(array_merge($parent, [
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $parentIds[$parent['code']] = $id;
        }

        // ── Дочерние категории с характеристиками ──────────────────────────
        $children = [

            // ── ТАКЕЛАЖ ──
            [
                'name'        => 'Зажимы для каната',
                'code'        => 'clamps',
                'parent'      => 'rigging',
                'description' => 'Зажимы для стальных канатов и тросов. DIN 741, нержавеющие, пластинчатые. Диаметры 6–32 мм.',
                'sort_order'  => 1,
                'chars'       => [
                    'Диаметр каната (мм)',
                    'Тип зажима',
                    'Материал',
                    'Разрывная нагрузка (кН)',
                    'Кол-во в упаковке (шт)',
                    'Вес единицы (кг)',
                ],
            ],
            [
                'name'        => 'Талрепы',
                'code'        => 'turnbuckles',
                'parent'      => 'rigging',
                'description' => 'Талрепы для натяжения тросов, цепей и канатов. Типы: крюк-крюк, крюк-кольцо, кольцо-кольцо, вилка-вилка. Резьба M6–M30.',
                'sort_order'  => 2,
                'chars'       => [
                    'Тип исполнения',
                    'Резьба',
                    'Рабочая нагрузка (кН)',
                    'Материал',
                    'Длина в открытом виде (мм)',
                    'Вес (кг)',
                ],
            ],
            [
                'name'        => 'Стропы',
                'code'        => 'slings',
                'parent'      => 'rigging',
                'description' => 'Текстильные петлевые стропы, канатные и цепные стропы для строповки грузов.',
                'sort_order'  => 3,
                'chars'       => [
                    'Грузоподъёмность',
                    'Длина',
                    'Материал',
                    'Количество ветвей',
                    'Тип исполнения',
                    'Диаметр/ширина',
                    'Вес',
                ],
            ],

            // ── ГРУЗОПОДЪЁМ ──
            [
                'name'        => 'Тали электрические',
                'code'        => 'electric-hoists',
                'parent'      => 'lifting-equipment',
                'description' => 'Электрические цепные и канатные тали серий CD1 и MD.',
                'sort_order'  => 1,
                'chars'       => [
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
                'name'        => 'Тали ручные',
                'code'        => 'manual-hoists',
                'parent'      => 'lifting-equipment',
                'description' => 'Ручные червячные, рычажные и шестерёнчатые тали.',
                'sort_order'  => 2,
                'chars'       => [
                    'Грузоподъёмность',
                    'Высота подъёма',
                    'Тип привода',
                    'Длина тяговой цепи',
                    'КПД',
                    'Вес',
                ],
            ],
            [
                'name'        => 'Лебёдки',
                'code'        => 'winches',
                'parent'      => 'lifting-equipment',
                'description' => 'Электрические и ручные лебёдки для промышленного применения.',
                'sort_order'  => 3,
                'chars'       => [
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
                'name'        => 'Краны и кран-балки',
                'code'        => 'cranes',
                'parent'      => 'lifting-equipment',
                'description' => 'Мостовые краны, подвесные кран-балки, консольные краны.',
                'sort_order'  => 4,
                'chars'       => [
                    'Грузоподъёмность',
                    'Пролёт',
                    'Высота подъёма',
                    'Скорость передвижения',
                    'Тип крана',
                    'Режим работы',
                    'Вес',
                ],
            ],

            // ── ГИДРАВЛИКА ──
            [
                'name'        => 'Домкраты',
                'code'        => 'jacks',
                'parent'      => 'hydraulic',
                'description' => 'Гидравлические домкраты бутылочные и подкатные.',
                'sort_order'  => 1,
                'chars'       => [
                    'Грузоподъёмность',
                    'Высота подъёма',
                    'Начальная высота',
                    'Тип',
                    'Рабочее давление',
                    'Вес',
                ],
            ],

            // ── СКЛАД ──
            [
                'name'        => 'Тележки',
                'code'        => 'trolleys',
                'parent'      => 'warehouse',
                'description' => 'Ходовые тележки для талей, складские платформенные тележки.',
                'sort_order'  => 1,
                'chars'       => [
                    'Грузоподъёмность',
                    'Тип привода',
                    'Ширина полки двутавра',
                    'Скорость передвижения',
                    'Напряжение питания',
                    'Вес',
                ],
            ],
        ];

        $childIds = [];
        foreach ($children as $child) {
            $catId = DB::table('categories')->insertGetId([
                'parent_id'   => $parentIds[$child['parent']],
                'name'        => $child['name'],
                'code'        => $child['code'],
                'description' => $child['description'],
                'image'       => null,
                'sort_order'  => $child['sort_order'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $childIds[$child['code']] = $catId;

            foreach ($child['chars'] as $charName) {
                DB::table('characteristics')->insert([
                    'category_id' => $catId,
                    'name'        => $charName,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Экспортируем для ProductSeeder
        cache(['category_child_ids' => $childIds]);
    }
}