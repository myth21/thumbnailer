<?php
declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Управляет записями в соответствующей модели таблице.
 */
class TransformationSeeder extends Seeder
{
    /**
     * Создает записи в таблице модели трансформации.
     *
     * @return void
     */
    public function run(): void
    {
        $transformations = [
            'breakpoint' => [
                3200,
                506,
                305,
                164,
                90,
            ],
        ];
        foreach ($transformations as $groupCode => $codes) {
            foreach ($codes as $code) {
                DB::table('transformation')->insert([
                    'code'        => 'w' . $code,
                    'group_code'  => $groupCode,
                    'title'       => 'Миниатюра шириной ' . $code,
                    'description' => 'Создать миниатюру по ширине ' . $code . ' пикселей согласно пропорциям',
                ]);
            }
        }
    }
}
