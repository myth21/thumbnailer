<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ThumbnailRequestInterface;
use App\Job\PayloadInterface;
use Imagick;
use ImagickException;

/**
 * Интерфейс для классов, реализующих скрытие алгоритмов обработки изображений для получения миниатюр.
 */
interface ImageThumbnailerInterface
{
    /**
     * Содержит алгоритм поиска и получения миниатюры изображения.
     * Возвращает объект Imagick, в частности инициализированный параметрами из DTO.
     *
     * @param ThumbnailRequestInterface $thumbnailRequestDto Данные запроса для обработки изображения.
     *
     * @return Imagick
     * @throws ImagickException Бросает объект обработки изображения.
     */
    public function doThumbnail(ThumbnailRequestInterface $thumbnailRequestDto): Imagick;

    /**
     * Обрабатывает изображение согласно кодам полученным через коду группы.
     *
     * @param PayloadInterface $payload Полезная нагрузка.
     * @param Imagick          $imagick Обработчик изображения.
     *
     * @return array
     */
    public function doThumbnails(PayloadInterface $payload, Imagick $imagick): array;
}
