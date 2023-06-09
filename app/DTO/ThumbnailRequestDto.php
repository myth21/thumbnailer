<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Хранит параметры запроса, необходимые для обработки изображения.
 */
class ThumbnailRequestDto implements ThumbnailRequestInterface
{
    /**
     * Инициализирует и устанавливает свойства объекта.
     *
     * @param string $code Код по которому будут определяться шаги обработки и находится файл изображения.
     * @param string $name Имя файла изображения.
     * @param string $type Тип (расширение) изображения.
     */
    public function __construct(
    protected string $code,
    protected string $name,
    protected string $type
    )
    {
    }

    /**
     * Возвращает запрошенный код.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Возвращает запрошенное имя файла.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает запрошенный тип файла.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Возвращает имя изображения, составленное из имени файлаи типа.
     *
     * @return string
     */
    public function getImageName(): string
    {
        return $this->name . '.' . $this->type;
    }
}
