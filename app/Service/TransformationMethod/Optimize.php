<?php

declare(strict_types=1);

namespace App\Service\TransformationMethod;

use Imagick;

/**
 * Отвечает за оптимизацию изображения.
 */
class Optimize implements TransformationMethodInterface
{
    /**
     * Получить текущее состояние объекта изображения и применить шаг трансформации к нему, изменив его состояние.
     *
     * @param Imagick               $imagick Трансформируемое изображение.
     * @param array{interlace: int} $params  Параметры трансформации шага.
     */
    public function apply(Imagick $imagick, array $params): void
    {
        $interlace = $params['interlace'] ?? Imagick::INTERLACE_PLANE;

        // Сделать оптимизацию изображения, jpg сделать прогрессивным.
        $imagick->setInterlaceScheme((int)$interlace);
        // Установить качество сжатия, 100 - не сжимать.
        $imagick->setImageCompressionQuality((int)$params['quality']);
    }
}
