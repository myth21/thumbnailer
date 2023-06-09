<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppRuntimeException;
use App\Model\Transformation;
use App\Repository\TransformationRepositoryInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use function in_array;
use function pathinfo;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

/**
 * Отвечает за логику поиска файла на локальном сервере.
 */
class LocalFileFinder implements FileFinderInterface
{
    /**
     * Место, где искать файлы.
     */
    private string $diskName = 'original';

    /**
     * Конструктор класса.
     *
     * @param TransformationRepositoryInterface $transformationRepository Объект хранилища данных.
     */
    public function __construct(protected TransformationRepositoryInterface $transformationRepository) {
    }

    /**
     * Проверяет, существует ли файл в соответствующей файловой системе.
     *
     * @param string $imageName Имя изображения.
     *
     * @return bool
     */
    public function isImageAvailable(string $imageName): bool
    {
        return Storage::disk($this->diskName)->exists($imageName);
    }

    /**
     *  Возвращает имя первого найденного изображению согласно маске.
     *
     * @param string $fileName Имя файла.
     *
     * @return ?string
     */
    public function getAvailableImageNameByPattern(string $fileName): ?string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        $globFileNamePattern = $this->getGlobFileNamePattern($fileName);
        $globSearchPattern = Storage::disk($this->diskName)->path($globFileNamePattern);
        // Flag GLOB_BRACE cannot use because not all OS has it.
        $files = File::glob($globSearchPattern);

        // Try find a file with acceptable type.
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, Transformation::getTypes(), true)) {
                return pathinfo($file, PATHINFO_BASENAME);
            }
        }

        return null;
    }

    /**
     * Читает исходное изображение в память и возвращает прочитанный файл (бинарную строку), либо кидает Exception.
     *
     * @param string $imageName Имя изображения.
     *
     * @return string
     * @throws AppRuntimeException Бросается при невозможности прочитать файл.
     */
    public function getSourceImageBlob(string $imageName): string
    {
        $blobFile = Storage::disk($this->diskName)->get($imageName);
        if (null === $blobFile) {
            throw new AppRuntimeException('Failed to read file ' . $imageName);
        }

        return $blobFile;
    }

    /**
     * Возвращает паттерн для поиска подходящего файла.
     * Паттерн: fileName . {png,jpg} не работает, потому что на alpine нет GLOB_BRACE.
     *
     * @param string $fileName Имя файла.
     *
     * @return string
     */
    protected function getGlobFileNamePattern(string $fileName): string
    {
        return $fileName . '.*';
    }
}
