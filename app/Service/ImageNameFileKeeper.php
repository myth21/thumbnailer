<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppRuntimeException;

use function count;
use function explode;
use function implode;
use function md5;
use function pathinfo;
use function substr;

use const PATHINFO_DIRNAME;
use const PATHINFO_FILENAME;

/**
 * Адаптер, отвечает за управление хранением файлов, путей к ним.
 */
class ImageNameFileKeeper extends AbstractFileKeeper
{
    /**
     * Минимальное количество элементов массива для определения оригинального имени изображения.
     */
    private const MIN_COUNT_TO_DEFINE_ORIGINAL_IMAGE_NAME = 2;

    /**
     * Возвращает путь к поддиректории, где хранятся файлы или куда их сохранять.
     * @deprecated
     *
     * @param string      $imageName   Имя изображения или файла без расширения.
     * @param string|null $codeDirName Код группы соответствующий имени директории.
     *
     * @return string
     */
    public function getPublicSubDirName(string $imageName, string $codeDirName = null): string
    {
        $fileName = pathinfo($imageName, PATHINFO_FILENAME);
        $md5FileName = md5($fileName);
        $firstPartSubDirName = substr($md5FileName, 0, 2);
        $secondPartSubDrName = substr($md5FileName, 2, 3);

        $pathToSubDirName = $firstPartSubDirName . '/' . $secondPartSubDrName;

        return (null === $codeDirName) ? $pathToSubDirName : $codeDirName . '/' . $pathToSubDirName;
    }

    /**
     * Возвращает путь к изображению (это может быть просто имя изображения) в зависимости от класса адаптера.
     *
     * @param string $requestImageName Запрашиваемое имя изображения.
     *
     * @return string
     * @throws AppRuntimeException Если невозможно получить тип изображения из его имени.
     */
    public function getAdaptedOriginalImageName(string $requestImageName): string
    {
        $dirName = pathinfo($requestImageName, PATHINFO_DIRNAME);
        $fileName = pathinfo($requestImageName, PATHINFO_FILENAME);

        $explodedFileName = explode('-', $fileName);
        if (count($explodedFileName) < self::MIN_COUNT_TO_DEFINE_ORIGINAL_IMAGE_NAME) {
            throw new AppRuntimeException('File type not defined for original image ' . $requestImageName);
        }
        $type = array_pop($explodedFileName);
        $implodedFileName = implode('-', $explodedFileName);
        $originalImageName = $implodedFileName . '.' . $type;

        return $dirName . '/' . $originalImageName;
    }
}
