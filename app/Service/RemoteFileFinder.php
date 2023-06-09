<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppRuntimeException;
use App\Repository\TransformationRepositoryInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use function in_array;
use function pathinfo;

/**
 * Отвечает за логику поиска файла на удаленном (возможно примонтированном) сервере.
 */
class RemoteFileFinder implements FileFinderInterface
{
    /**
     * Имя диска где искать файлы.
     */
    private string $diskName = 'external';

    /**
     * Конструктор класса.
     *
     * @param TransformationRepositoryInterface $transformationRepository Объект хранилища данных.
     */
    public function __construct(protected TransformationRepositoryInterface $transformationRepository) {
    }

    /**
     * Проверяет существует ли файл в соответствующей файловой системе.
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
     * Возвращает имя первого найденного изображению согласно маске.
     *
     * @param string $fileName Имя файла.
     *
     * @return ?string
     */
    public function getAvailableImageNameByPattern(string $fileName): ?string
    {
        $globSearchPattern = Storage::disk($this->diskName)->path($this->getGlobFileNamePattern($fileName));
        // Функция glob неприменима для работы с удалёнными файлами.
        // Поскольку файл должен быть доступен через файловую систему сервера.
        // Flag GLOB_BRACE cannot use because not all OS has it.
        $files = File::glob($globSearchPattern);
        if ($files) {
            $types = $this->transformationRepository->getTypes();
            foreach ($files as $file) {
                $pathParts = pathinfo($file);
                if (in_array($pathParts['extension'], $types, true)) {
                    return $pathParts['basename'];
                }
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
