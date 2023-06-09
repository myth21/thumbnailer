<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TransformationRepositoryInterface;
use App\Service\TransformationMethod\TransformationMethodInterface;
use Imagick;

use function app;

/**
 * Отвечает за применения логику трансформации согласно модели трансформации.
 */
class TransformationService implements TransformationServiceInterface
{
    /**
     * Конструктор класса.
     *
     * @param TransformationRepositoryInterface $transformationRepository Объект хранилища данных.
     */
    public function __construct(protected TransformationRepositoryInterface $transformationRepository) {
    }

    /**
     * Получить модель трансформации и шаги трансформации с соответствующими методами обработки используя Imagick.
     *
     * @param string  $code    Код модели трансформации.
     * @param Imagick $imagick Трансформируемое изображение.
     *
     * @return void
     */
    public function transform(string $code, Imagick $imagick): void
    {
        $transformation = $this->transformationRepository->getByCode($code);

        foreach ($transformation->steps as $step) {
            // Создать объект метода трансформации согласно именованию метода из модели шага трансформации.
            /* @uses Resize */
            /* @var TransformationMethodInterface $method */
            $methodName = $step->getMethodName();
            $method = app($methodName);
            $method->apply($imagick, $step->getParams());
        }
    }
}
