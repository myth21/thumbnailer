<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ThumbnailRequestInterface;
use Imagick;
use ImagickException;

/**
 * Описывает интерфейс для получения экземпляра изображения(ий)
 * с помощью цепочки объектов использующих разные способы поиска файла.
 */
interface FileFindersChainInterface
{
    /**
     * Инициализирует путь к директории хранения изображений из файла конфигурации приложения.
     * Возвращает Imagick с прочитанным исходным изображением.
     *
     * @param ThumbnailRequestInterface $thumbnailRequestDto Содержит необходимые параметры.
     *
     * @return Imagick
     * @throws ImagickException Бросает объект обработки изображения.
     */
    public function getOriginImageInstance(ThumbnailRequestInterface $thumbnailRequestDto): Imagick;
}
