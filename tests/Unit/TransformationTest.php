<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repository\TransformationRepositoryInterface;
use App\Service\FileFinderInterface;
use App\Service\FileKeeperInterface;
use App\Service\ImageIdFileKeeper;
use App\Service\LocalFileFinder;
use App\Service\TransformationMethod\TransformationMethodInterface;
use Illuminate\Support\Facades\Log;
use Imagick;
use ImagickException;
use PHPUnit\Framework\Assert;
use Tests\AbstractTestCase;

use function app;
use function array_flip;
use function array_rand;
use function file_get_contents;

/**
 * Отвечает за тестирование трансформации изображений.
 */
class TransformationTest extends AbstractTestCase
{
    /**
     * Имя файла.
     *
     * @var string
     */
    protected string $fileName = 'test-jpg';

    /**
     * Расширение (тип) файла.
     *
     * @var string
     */
    protected string $fileNameExt = 'jpg';

    /**
     * Имя изображения.
     *
     * @var string
     */
    protected string $imageName = 'test-jpg.jpg';

    /**
     * Объект поиска файлов.
     *
     * @var FileFinderInterface
     */
    protected FileFinderInterface $localFileFinder;

    /**
     * Объект хранения файлов.
     *
     * @var FileKeeperInterface
     */
    protected FileKeeperInterface $fileKeeper;

    /**
     * Объект хранилища данных.
     *
     * @var TransformationRepositoryInterface
     */
    protected TransformationRepositoryInterface $transformationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformationRepository = app(TransformationRepositoryInterface::class);
        $this->fileKeeper = app(FileKeeperInterface::class);
        $this->localFileFinder = new LocalFileFinder($this->transformationRepository);
    }

    /**
     * Тестирует применение трансформации.
     *
     * @throws ImagickException
     */
    public function testApplyTransformation(): void
    {
        $testImagePath = __DIR__ . '/../Support/Data/' . $this->imageName;
        $this->fileKeeper->saveOriginal($this->imageName, file_get_contents($testImagePath));

        $imageBlob = $this->localFileFinder->getSourceImageBlob($this->imageName);

        $imagick = new Imagick();
        $imagick->readImageBlob($imageBlob);
        $deltaSourceWidth = $imagick->getImageWidth() - 10;
        $deltaSourceHeight = $imagick->getImageHeight() - 10;

        /* @var TransformationMethodInterface $method */
        $method = app('Resize');
        $params = [
            'width'  => $deltaSourceWidth,
            'height' => $deltaSourceHeight,
        ];
        $method->apply($imagick, $params);

        self::assertEquals($deltaSourceWidth, $imagick->getImageWidth());
        self::assertEquals($deltaSourceHeight, $imagick->getImageHeight());
    }

    /**
     * Тестирует получение соответствующего mime-type.
     *
     * @throws ImagickException
     */
    public function testResponseMimeType(): void
    {
        if ($this->fileKeeper instanceof ImageIdFileKeeper) {
            $message = 'Test ' . __METHOD__ . ' is skipped, because one is not executed for "ImageIdFileKeeper" param.';
            Assert::markTestSkipped($message);
        }

        $imagick = new Imagick();
        $types = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'webp' => 'image/x-webp',
        ];
        $codes = $this->transformationRepository->getCodes();
        $randCode = array_rand(array_flip($codes), 1);

        foreach ($types as $ext => $type) {
            $creatingUrl = 'http://10.222.33.2/?img=/images/' . $randCode . '/' . $this->fileName . '.' . $ext;
            $response = $this->get($creatingUrl);
            $response->assertStatus(200);
            $imagick->readImageBlob($response->getContent());
            self::assertEquals($type, $imagick->getImageMimeType());
        }
    }
}
