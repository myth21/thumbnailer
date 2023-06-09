<?php

declare(strict_types=1);

namespace App\Service\TransformationMethod;

use Imagick;

/**
 * Задает интерфейс для работы с методами трансформации.
 */
interface TransformationMethodInterface
{
    /**
     * Получить текущее состояние объекта изображения и применить шаг трансформации к нему, изменив его состояние.
     *
     * @param Imagick $imagick Трансформируемое изображение.
     * @param array   $params  Параметры для трансформации изображения.
     */
    public function apply(Imagick $imagick, array $params): void;
}
