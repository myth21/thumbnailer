<?php

declare(strict_types=1);

namespace App\Service\TransformationMethod;

use Imagick;

/**
 * Отвечает за изменение размера изображения.
 */
class Resize implements TransformationMethodInterface
{
    /**
     * Получить текущее состояние объекта изображения и применить шаг трансформации к нему, изменив его состояние.
     *
     * @param Imagick                        $imagick Трансформируемое изображение.
     * @param array{width: int, height: int} $params  Параметры трансформации шага.
     */
    public function apply(Imagick $imagick, array $params): void
    {
        $imagick->thumbnailImage((int)$params['width'], (int)$params['height']);
    }
}
