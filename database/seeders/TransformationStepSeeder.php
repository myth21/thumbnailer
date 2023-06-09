<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * Управляет записями в соответствующей модели таблице.
 */
class TransformationStepSeeder extends Seeder
{
    /**
     * Создает записи в таблице модели шагов трансформации.
     *
     * @return void
     * @throws JsonException Бросает исключение.
     */
    public function run(): void
    {
        $codes = [
            3200,
            506,
            305,
            164,
            90,
        ];
        $i = 1;
        foreach ($codes as $code) {
            DB::table('transformation_step')->insert([
                'code'    => 'w' . $code,
                'sorting' => $i,
                'method'  => 'Resize',
                'params'  => json_encode([
                    'width'  => $code,
                    'height' => 0,
                ], JSON_THROW_ON_ERROR),
            ]);
            $i++;
        }
        $i = 1;
        foreach ($codes as $code) {
            DB::table('transformation_step')->insert([
                'code'    => 'w' . $code,
                'sorting' => $i,
                'method'  => 'Optimize',
                'params'  => json_encode([
                    'quality' => 90,
                ], JSON_THROW_ON_ERROR),
            ]);
            $i++;
        }
    }
}
