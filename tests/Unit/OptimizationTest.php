<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;
use PHPImageOptim\PHPImageOptim;
use PHPImageOptim\Tools\Png\PngOut;
use Tests\AbstractTestCase;

use function copy;
use function filesize;
use function round;
use function unlink;

class OptimizationTest extends AbstractTestCase
{
    /**
     * Объект утилиты pngout.
     */
    private PngOut $pngOut;

    /**
     * Путь к утилиты pngout.
     */
    private string $binaryPath = '/usr/local/bin/pngout';

    /**
     * Имя изображения.
     */
    private string $imageName = 'test-png.png';

    /**
     * Путь к изображению.
     */
    private string $imagePath;

    /**
     * Путь к изображению предназначенному для обработки.
     */
    private string $targetImagePath;

    /**
     * Объект библиотеки PHPImageOptim.
     */
    private PHPImageOptim $phpImageOptim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pngOut = new PngOut();
        $this->pngOut->setBinaryPath($this->binaryPath);
        $this->phpImageOptim = new PHPImageOptim();
        $this->imagePath = __DIR__ . '/../Support/Data/' . $this->imageName;
        $this->targetImagePath = __DIR__ . '/../Support/Data/target-' . $this->imageName;
        copy($this->imagePath, $this->targetImagePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->targetImagePath);
    }

    /**
     * Тестирует оптимизацию изображения.
     *
     * @throws Exception Например если файл не существует.
     */
    public function testPngOut(): void
    {
        $roundOriginalFileSizeInMb = $this->getRoundFileSizeInMb($this->imagePath);
        Log::info('Размер файла ' . $this->imageName . ' в мб ДО обработки: ' . $roundOriginalFileSizeInMb);

        $this->phpImageOptim->setImage($this->targetImagePath);
        $this->phpImageOptim->chainCommand($this->pngOut);
        $this->phpImageOptim->optimise();
        clearstatcache();

        $roundProcessedFileSizeInMb = $this->getRoundFileSizeInMb($this->targetImagePath);
        Log::info('Размер файла ' . $this->imageName . ' в мб ПОСЛЕ обработки: ' . $roundProcessedFileSizeInMb);

        self::assertLessThanOrEqual($roundOriginalFileSizeInMb, $roundProcessedFileSizeInMb);
    }

    /**
     * Получить округленный размер изображения в мб.
     *
     * @param string $imagePath Путь к изображению.
     *
     * @return float
     */
    #[Pure] private function getRoundFileSizeInMb(string $imagePath): float
    {
        return round(filesize($imagePath) / 1000000, 2);
    }
}
