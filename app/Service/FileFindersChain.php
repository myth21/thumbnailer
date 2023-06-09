<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ThumbnailRequestInterface;
use App\Exception\AppRuntimeException;
use Imagick;
use ImagickException;

use function is_string;

/**
 * Отвечает за цепочку возможных способов поиска файла с помощью зарегистрированных объектов.
 *
 * @property FileFinderInterface[] $fileFinders
 */
class FileFindersChain implements FileFindersChainInterface
{
    /**
     * Конструктор класса.
     *
     * @param iterable            $fileFinders Массив объектов отвечающих за поиск файлов.
     * @param FileKeeperInterface $fileKeeper  Адаптер управления хранения файлов, путей к ним.
     */
    public function __construct(protected iterable $fileFinders, protected FileKeeperInterface $fileKeeper)
    {
    }

    /**
     * Возвращает инициализированный ОРИГИНАЛЬНЫМ изображением Imagick.
     * "Запускает" все классы ответственные за поиск файла и его чтение.
     *
     * @param ThumbnailRequestInterface $thumbnailRequestDto Содержит необходимые параметры.
     *
     * @return Imagick Обработчик изображения.
     * @throws ImagickException Бросает объект обработки изображения.
     * @throws AppRuntimeException Бросается если файл не найден.
     */
    public function getOriginImageInstance(ThumbnailRequestInterface $thumbnailRequestDto): Imagick
    {
        $imagick = new Imagick();

        foreach ($this->fileFinders as $fileFinder) {
            /** @var FileFinderInterface $fileFinder */
            // Get original image path to search.
            // Image path can be just imageName.ext (for local filesystem).
            // Or /path/to/imageName.ext (for external filesystem).
            $imageName = $this->fileKeeper->getAdaptedOriginalImageName($thumbnailRequestDto->getImageName());

            // Search original image in local filesystem, remote (mounted) filesystem.
            if ($fileFinder->isImageAvailable($imageName)) {
                $imagick->readImageBlob($fileFinder->getSourceImageBlob($imageName));
                // Image path can be just imageName.ext or /path/to/imageName.ext.
                $imagick->setFilename($imageName);
                return $imagick;
            }

            // Search first suitable original image with other extension.
            $imageName = $fileFinder->getAvailableImageNameByPattern($imageName);
            if (is_string($imageName)) {
                $imagick->readImageBlob($fileFinder->getSourceImageBlob($imageName));
                $imagick->setFilename($imageName);
                return $imagick;
            }
        }

        throw new AppRuntimeException('Original image ' . $thumbnailRequestDto->getImageName() . ' not found');
    }
}
