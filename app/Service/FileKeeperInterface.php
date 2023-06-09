<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Описывает интерфейс для управления хранением файлов, путей к ним.
 */
interface FileKeeperInterface
{
    /**
     * Сохраняет файл в соответствующую директорию.
     * Создает соответствующие директории если таковых нет.
     *
     * @param string $codeDirName Код из запроса соответствующей имени директории.
     * @param string $imageName   Имя не менее 2-х символов.
     * @param string $imageBlob   Строка прочитанного изображения.
     *
     * @return string
     */
    public function save(string $codeDirName, string $imageName, string $imageBlob): string;

    /**
     * Сохраняет оригинальный файл в соответствующую директорию.
     *
     * @param string $imageName Имя изображения.
     * @param string $imageBlob Строка прочитанного изображения.
     *
     * @return string
     */
    public function saveOriginal(string $imageName, string $imageBlob): string;

    /**
     * Возвращает путь к поддиректории, где хранятся файлы или куда их сохранять.
     *
     * @param string      $imageName   Имя изображения или файла без расширения.
     * @param string|null $codeDirName Код группы соответствующий имени директории.
     *
     * @return string
     */
    public function getPublicSubDirName(string $imageName, string $codeDirName = null): string;

    /**
     * Возвращает имя изображения в зависимости от конкретного обработчика.
     *
     * @param string $requestImageName Имя изображения или файла без расширения.
     *
     * @return string
     */
    public function getAdaptedOriginalImageName(string $requestImageName): string;
}
