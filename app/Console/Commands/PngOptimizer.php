<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PHPImageOptim\PHPImageOptim;
use PHPImageOptim\Tools\Png\PngOut;

use function clearstatcache;
use function config;
use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * Отвечает за обработку всех png изображений на диске destination.
 */
class PngOptimizer extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:PngOptimizer';

    /**
     * Для оптимизации изображений png.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Обрабатывает изображения.
     *
     * @return int
     * @throws Exception Что-то не так.
     */
    public function handle(): int
    {
        $pngOutBinaryPath = config('app.pngOutBinaryPath');
        $pngOut = (new PngOut())->setBinaryPath($pngOutBinaryPath);
        $phpImageOptim = new PHPImageOptim();

        $destinationDisk = Storage::disk('destination');
        $path = $destinationDisk->path('/');
        foreach ($destinationDisk->allFiles() as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                $file = $path . $file;
                $phpImageOptim->setImage($file);
                $phpImageOptim->chainCommand($pngOut);
                $phpImageOptim->optimise();
                clearstatcache();
            }
        }

        return 0;
    }
}
