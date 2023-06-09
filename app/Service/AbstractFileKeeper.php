<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppRuntimeException;
use Illuminate\Support\Facades\Storage;

/**
 * Адаптер, отвечает за управление хранением файлов, путей к ним.
 */
abstract class AbstractFileKeeper implements FileKeeperInterface
{
    /**
     * Принимаемые mime types по которым адаптер пытается установить тип изображения.
     *
     * @var array|string[]
     */
    private array $acceptedMimeTypes = [
        'image/png'    => 'png',
        'image/jpeg'   => 'jpg',
        'image/x-webp' => 'webp',
    ];

    /**
     * Возвращает путь к поддиректории, где хранятся файлы или куда их сохранять.
     *
     * @param string      $imageName   Имя изображения или файла без расширения.
     * @param string|null $codeDirName Код группы соответствующий имени директории.
     *
     * @return string
     */
    abstract public function getPublicSubDirName(string $imageName, string $codeDirName = null): string;

    /**
     * Возвращает имя изображения в зависимости от конкретного обработчика.
     *
     * @param string $requestImageName Имя изображения или файла без расширения.
     *
     * @return string
     */
    abstract public function getAdaptedOriginalImageName(string $requestImageName): string;

    /**
     * Сохраняет оригинальный файл в соответствующую директорию.
     * Возвращает url к изображению.
     *
     * @param string $imageName Имя не менее 2-х символов.
     * @param string $imageBlob Строка прочитанного изображения.
     *
     * @return string
     * @throws AppRuntimeException При невозможности сохранить файл.
     */
    public function saveOriginal(string $imageName, string $imageBlob): string
    {
        $disk = Storage::disk('original');
        $put = $disk->put($imageName, $imageBlob, 'public');
        if (! $put) {
            throw new AppRuntimeException('Failed saving original file ' . $imageName);
        }

        return $disk->url($imageName);
    }

    /**
     * Сохраняет файл в соответствующую директорию.
     * Создает соответствующие директории если таковых нет.
     *
     * @param string $codeDirName Код из запроса соответствующий имения директории.
     * @param string $imageName   Имя не менее 2-х символов.
     * @param string $imageBlob   Строка прочитанного изображения.
     *
     * @return string
     * @throws AppRuntimeException Бросается при ошибке сохранения файла.
     */
    public function save(string $codeDirName, string $imageName, string $imageBlob): string
    {
        $disk = Storage::disk('destination');

        // Создать кодовую директорию, если таковой не существует.
        if (! $disk->exists($codeDirName)) {
            $disk->makeDirectory($codeDirName);
        }

        // Создать поддиректорию на основе имени файла, куда следует сохранять файлы, если таковой не существует.
        // В связи с изменением логики, генерация собственных поддиректорий на сервисе отменяется.
        // @deprecated
        /*
        $pathToSubDirName = $this->getPublicSubDirName($imageName, $codeDirName);
        if (! $disk->exists($pathToSubDirName)) {
            $disk->makeDirectory($pathToSubDirName);
        }
        */
        // В связи с изменением логики, генерация собственных поддиректорий на сервисе отменяется.
        // @deprecated
        // $pathToImage = $pathToSubDirName . '/' . $imageName;

        // Получить путь к имени изображения для сохранения.
        $pathToImage = $this->getImagePath($codeDirName, $imageName);
        $put = $disk->put($pathToImage, $imageBlob, 'public');
        if (! $put) {
            throw new AppRuntimeException('Failed saving file ' . $pathToImage);
        }

        return $disk->url($pathToImage);
    }

    /**
     * Возвращает наиболее подходящий тип изображения по mime type.
     *
     * @param string $mimeType    Mime-type изображения.
     * @param string $defaultType Тип возвращаемый если не нашлось подходящего.
     *
     * @return string
     */
    public function getSuitableExtensionByMimeType(string $mimeType, string $defaultType): string
    {
        return $this->acceptedMimeTypes[$mimeType] ?? $defaultType;
    }

    /**
     * Удаляет изображение, и возвращает путь к удаленному файлу.
     *
     * @param string $codeDirName Код группы соответствующий имени директории.
     * @param string $imageName   Имя изображения.
     *
     * @return string|null
     */
    public function delete(string $codeDirName, string $imageName): string | null
    {
        $pathToImage = $this->getImagePath($codeDirName, $imageName);
        $disk = Storage::disk('destination');
        if ($disk->exists($pathToImage)) {
            $disk->delete($pathToImage);
            return $disk->url($pathToImage);
        }

        return null;
    }

    /**
     * Удаляет оригинал изображение, и возвращает путь к удаленному файлу.
     *
     * @param string $imageName Имя изображения.
     *
     * @return string|null
     */
    public function deleteOriginal(string $imageName): string | null
    {
        $disk = Storage::disk('original');

        if ($disk->exists($imageName)) {
            $disk->delete($imageName);
            return $disk->url($imageName);
        }

        return null;
    }

    /**
     * Проверяет, существует ли файл.
     *
     * @param string $codeDirName Код группы соответствующий имени директории.
     * @param string $imageName   Имя изображения.
     *
     * @return bool
     */
    public function isFileSaved(string $codeDirName, string $imageName): bool
    {
        $pathToImage = $this->getImagePath($codeDirName, $imageName);

        return Storage::disk('destination')->exists($pathToImage);
    }

    /**
     * Возвращает путь к изображению от директории кода.
     *
     * @param $codeDirName
     * @param $imageName
     *
     * @return string
     */
    public function getImagePath($codeDirName, $imageName): string
    {
        return $codeDirName . '/' . $imageName;
    }

    /**
     * Проверяет, существует ли файл.
     *
     * @param string $imageName Имя изображения.
     *
     * @return bool
     */
    public function isOriginalFileSaved(string $imageName): bool
    {
        return Storage::disk('original')->exists($imageName);
    }
}
