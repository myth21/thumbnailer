<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Transformation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Описывает интерфейс работы с данными по трансформации.
 */
interface TransformationRepositoryInterface
{
    /**
     * Возвращает объект модели.
     *
     * @param string $code Идентификатор модели.
     *
     * @return Transformation
     *
     * @throws ModelNotFoundException Модель не найдена.
     */
    public function getByCode(string $code): Transformation;

    /**
     * Возвращает коды из $table.
     *
     * @return array
     */
    public function getCodes(): array;

    /**
     * Возвращает допустимые типы (расширения) изображений.
     *
     * @return string[]
     */
    public function getTypes(): array;

    /**
     * Возвращает массив моделей по коду группы.
     *
     * @param string $groupCode Код группы.
     *
     * @return Transformation[]
     */
    public function getByGroupCode(string $groupCode): array;
}
