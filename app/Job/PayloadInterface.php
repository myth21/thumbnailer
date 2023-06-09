<?php

declare(strict_types=1);

namespace App\Job;

/**
 * Описывает методы для работы с полезной нагрузки (из сообщения очереди).
 */
interface PayloadInterface
{
    /**
     * Возвращает закодированное (двоичное) изображение в base64.
     *
     * @return string
     */
    public function getBase64Image(): string;

    /**
     * Возвращает имя изображения.
     *
     * @return string
     */
    public function getImageName(): string;

    /**
     * Возвращает имя файла без типа.
     *
     * @return string
     */
    public function getFileName(): string;

    /**
     * Возвращает тип изображения.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Возвращает код группы для модели трансформации.
     *
     * @return string
     */
    public function getGroupCode(): string;
}
