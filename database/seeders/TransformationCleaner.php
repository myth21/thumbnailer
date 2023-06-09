<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Управляет записями в соответствующей модели таблице.
 */
class TransformationCleaner extends Seeder
{
    /**
     * Создает записи в таблице модели трансформации.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('transformation_step')->truncate();
        DB::table('transformation')->truncate();
    }
}
