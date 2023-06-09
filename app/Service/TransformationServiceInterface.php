<?php

declare(strict_types=1);

namespace App\Service;

use Imagick;

/**
 * Структура для применения работы с параметрам, предназначенные для создания миниатюр.
 */
interface TransformationServiceInterface
{
    /**
     * Получить модель трансформации и шаги трансформации с соответствующими методами обработки используя Imagick.
     *
     * @param string  $code    Код модели трансформации.
     * @param Imagick $imagick Трансформируемое изображение.
     *
     * @return void
     */
    public function transform(string $code, Imagick $imagick): void;
}
