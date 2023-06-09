<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Symfony\Component\Yaml\Parser;
use Illuminate\Support\Facades\DB;

use function config;
use function is_file;
use function json_encode;
use function storage_path;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

/**
 * Управляет записями в соответствующей модели таблице.
 */
class TransformationDumpSeeder extends Seeder
{
    /**
     * Создает записи в таблице модели трансформации.
     *
     * @return void
     */
    public function run(): void
    {
        $transformationsSeedPath = config('filesystems.transformationsSeedPath');
        if (! is_file($transformationsSeedPath)) {
            exit('File ' . $transformationsSeedPath . ' is not available.' . PHP_EOL);
        }

        DB::table('transformation_step')->truncate();
        DB::table('transformation')->truncate();

        $transformations = (new Parser())->parseFile($transformationsSeedPath);
        foreach ($transformations as $groupCode => $codes) {
            foreach ($codes as $code => $codeData) {
                DB::table('transformation')->insert([
                    'code'        => $code,
                    'group_code'  => $groupCode,
                    'title'       => $codeData['title'],
                    'description' => $codeData['description'],
                ]);
                foreach ($codeData['steps'] as $step) {
                    DB::table('transformation_step')->insert([
                        'code'    => $code,
                        'sorting' => $step['sorting'],
                        'method'  => $step['method'],
                        'params'  => json_encode($step['params'], JSON_THROW_ON_ERROR),
                    ]);
                }
            }
        }
    }
}
