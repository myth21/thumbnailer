<?php

declare(strict_types=1);

namespace App\Job\V1;

use App\Job\PayloadInterface;

use function pathinfo;

use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

/**
 * Модель полезной нагрузки сообщения (без учета версий).
 */
class Payload implements PayloadInterface
{
    /**
     * Конструктор класса.
     *
     * @param string $base64Image Закодированное изображение.
     * @param string $imageName   Имя изображения.
     * @param string $groupCode   Код группы.
     */
    public function __construct(
        protected string $base64Image,
        protected string $imageName,
        protected string $groupCode
    )
    {
    }

    /**
     * Возвращает закодированное (двоичное) изображение в base64.
     *
     * @return string
     */
    public function getBase64Image(): string
    {
        return $this->base64Image;
    }

    /**
     * Возвращает имя изображения.
     *
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * Возвращает имя файла.
     *
     * @return string
     */
    public function getFileName(): string
    {
        $dirName = pathinfo($this->imageName, PATHINFO_DIRNAME);
        $fileName = pathinfo($this->imageName, PATHINFO_FILENAME);
        return $dirName ? $dirName . '/' . $fileName : $fileName;
    }

    /**
     * Возвращает имя изображения.
     *
     * @return string
     */
    public function getType(): string
    {
        return pathinfo($this->imageName, PATHINFO_EXTENSION);
    }

    /**
     * Возвращает код группы для модели трансформации.
     *
     * @return string
     */
    public function getGroupCode(): string
    {
        return $this->groupCode;
    }
}
