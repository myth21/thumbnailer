<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppRuntimeException;
use Illuminate\Support\Facades\Storage;
use Throwable;
use function env;
use function file_get_contents;
use function get_headers;
use function stream_context_create;
use function substr;

/**
 * Отвечает за логику поиска файла по HTTP запросам.
 */
class HttpFileFinder implements FileFinderInterface
{
    /**
     * Место, где искать файлы.
     */
    private string $diskName = 'original';

    /**
     * Успешный статус кода заголовка ответа.
     */
    private const HEADER_STATUS_CODE = '200';

    /**
     * Возвращает адрес для запроса.
     *
     * @throws AppRuntimeException
     */
    private function getRequestUrl(string $imageName): string
    {
        $baseProjectImgBaseUrl = env('BASE_PROJECT_IMG_BASE_URL');
        if (null === $baseProjectImgBaseUrl) {
            throw new AppRuntimeException('Base project img base url not found in env');
        }

        return env('BASE_PROJECT_IMG_BASE_URL') . $imageName;
    }

    /**
     * Проверяет, существует ли файл по URL.
     *
     * @param string $imageName Имя изображения.
     *
     * @return bool
     */
    public function isImageAvailable(string $imageName): bool
    {
        if (Storage::disk($this->diskName)->exists($imageName)) {
            return true;
        }

        $context = stream_context_create(['http' => ['method' => 'HEAD', 'ignore_errors' => true]]);

        try {
            $headers = get_headers($this->getRequestUrl($imageName), true, $context);
            if (false === $headers) {
                return false;
            }

            if (!isset($headers[0])) {
                return false;
            }

            // Разбираем ответ строки `HTTP/1.1 200 OK`.
            $headerCode = substr($headers[0], 9, 3);
            return $headerCode === self::HEADER_STATUS_CODE;

        } catch (Throwable $e) {
            return false;
        }
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
        $disk = Storage::disk($this->diskName);
        if ($disk->exists($imageName)) {
            return $disk->get($imageName);
        }

        $requestUrl = $this->getRequestUrl($imageName);

        $content= file_get_contents($requestUrl);
        if ($content) {
            return $content;
        }

        throw new AppRuntimeException('Failed to read file ' . $imageName);
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
        return null;
    }
}
