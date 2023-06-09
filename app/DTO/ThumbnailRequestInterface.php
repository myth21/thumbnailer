<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Методы, для работы с параметрам, предназначенные для создания миниатюр.
 */
interface ThumbnailRequestInterface
{
    /**
     * Возвращает запрошенный код.
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Возвращает запрошенное имя файла.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает запрошенный тип файла.
     *
     * @return string
     */
    public function getType(): string;
}
