<?php
declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Отвечает за хранение классов которые будут запущены для заполнения данными базы.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Запустите начальные данные базы данных.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            TransformationCleaner::class,
            TransformationSeeder::class,
            TransformationStepSeeder::class,
        ]);
    }
}
