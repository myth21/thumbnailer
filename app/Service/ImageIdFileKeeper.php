<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ExternalImageRepository;

use function pathinfo;
use function str_pad;
use function substr;

use const PATHINFO_FILENAME;

/**
 * Адаптер, отвечает за управление хранением файлов, путей к ним.
 */
class ImageIdFileKeeper extends AbstractFileKeeper
{
    /**
     * Конструктор класса.
     *
     * @param ExternalImageRepository $externalImageRepository Отвечает за хранение данных "внешней" таблицы.
     */
    public function __construct(
        protected ExternalImageRepository $externalImageRepository,
    )
    {
    }

    /**
     * Возвращает путь к поддиректории, где хранятся файлы или куда их сохранять.
     *
     * @param string      $imageName   Имя изображения или файла без расширения.
     * @param string|null $codeDirName Код группы соответствующий имени директории.
     *
     * @return string
     */
    public function getPublicSubDirName(string $imageName, string $codeDirName = null): string
    {
        $fileName = pathinfo($imageName, PATHINFO_FILENAME);
        $fileName = str_pad((string) $fileName, 5, '0', STR_PAD_LEFT);
        $firstPartSubDirName = substr($fileName, 0, 2);
        $secondPartSubDrName = substr($fileName, 2, 3);

        $pathToSubDirName = $firstPartSubDirName . '/' . $secondPartSubDrName;

        return (null === $codeDirName) ? $pathToSubDirName : $codeDirName . '/' . $pathToSubDirName;
    }

    /**
     * Возвращает путь к изображению (это может быть просто имя изображения) в зависимости от класса адаптера.
     *
     * @param string $requestImageName Запрашиваемое имя изображения.
     *
     * @return string
     */
    public function getAdaptedOriginalImageName(string $requestImageName): string
    {
        $requestFileName = pathinfo($requestImageName, PATHINFO_FILENAME);
        return $this->externalImageRepository->getImagePath($requestFileName);
    }
}
