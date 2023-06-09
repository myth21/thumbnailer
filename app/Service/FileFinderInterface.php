<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

/**
 * Описывает логику для работы с поиском и получением файла.
 */
interface FileFinderInterface
{
    /**
     * Проверяет существует ли изображение в соответствующей файловой системе.
     *
     * @param string $imageName Имя изображения.
     *
     * @return bool
     */
    public function isImageAvailable(string $imageName): bool;

    /**
     * Возвращает имя первого найденного изображению согласно маске.
     *
     * @param string $fileName Имя файла.
     *
     * @return ?string
     */
    public function getAvailableImageNameByPattern(string $fileName): ?string;

    /**
     * Читает исходное изображение в память и возвращает прочитанный файл (бинарную строку), либо кидает Exception.
     *
     * @param string $imageName Путь к имени изображения.
     *
     * @return string
     * @throws RuntimeException Бросается если файл не найден.
     */
    public function getSourceImageBlob(string $imageName): string;
}
