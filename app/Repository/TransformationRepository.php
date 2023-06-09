<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Transformation;
use App\Model\TransformationStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

/**
 * Отвечает за скрытие информации о хранении данных.
 */
class TransformationRepository implements TransformationRepositoryInterface
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
    public function getByCode(string $code): Transformation
    {
        return Cache::rememberForever('transformationByCode' . $code, static function () use ($code) {
            return Transformation::query()->findOrFail($code);
        });
    }

    /**
     * Возвращает коды (список) из $table.
     *
     * @return array
     */
    public function getCodes(): array
    {
        return Cache::rememberForever('codes', static function () {
            return Transformation::query()->select('code')->get()->pluck('code')->all();
        });
    }

    /**
     * Возвращает допустимые типы (расширения) изображений.
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return Transformation::getTypes();
    }

    /**
     * Возвращает массив моделей по коду группы.
     *
     * @param string $groupCode Код группы.
     *
     * @return Transformation[]
     */
    public function getByGroupCode(string $groupCode): array
    {
        return Cache::rememberForever('transformationByGroupCode' . $groupCode, static function () use ($groupCode) {
            return Transformation::query()->where('group_code', '=', $groupCode)->get()->all();
        });
    }

    /**
     * Получить список трансформаций.
     *
     * @param array|string[] $columns Список столбцов.
     *
     * @return array
     */
    public function getArray(array $columns = ['*']): array
    {
        return Transformation::all($columns)->toArray();
    }

    /**
     * Сохранить трансформацию.
     *
     * @param string $code        Код (первичный ключ).
     * @param string $groupCode   Код группы.
     * @param string $title       Именование трансформации.
     * @param string $description Описание трансформации.
     *
     * @return Transformation
     */
    public function save(string $code, string $groupCode, string $title, string $description): Transformation
    {
        return Transformation::create([
            'code'        => $code,
            'group_code'  => $groupCode,
            'title'       => $title,
            'description' => $description,
        ]);
    }

    /**
     * Обновить трансформацию.
     *
     * @param string $code             Код (первичный ключ).
     * @param array  $updateAttributes Обновляемые данные.
     *
     * @return int
     */
    public function update(string $code, array $updateAttributes): int
    {
        return Transformation::where('code', $code)->update($updateAttributes);
    }

    /**
     * Удалить трансформацию.
     *
     * @param string $code Код (первичный ключ).
     *
     * @return int
     */
    public function delete(string $code): int
    {
        return Transformation::destroy($code);
    }

    /**
     * Получить список шагов трансформации.
     *
     * @param string[] $columns Список столбцов.
     *
     * @return array
     */
    public function getStepArray(array $columns = ['*']): array
    {
        return TransformationStep::all($columns)->toArray();
    }

    /**
     * Сохранить шаг трансформации.
     *
     * @param string $code    Код (первичный ключ).
     * @param int    $sorting Сортировка.
     * @param string $method  Имя метода.
     * @param array  $params  Параметры для метода.
     *
     * @return Model|$this
     */
    public function saveStep(string $code, int $sorting, string $method, array $params)
    {
        return TransformationStep::create([
            'code'    => $code,
            'sorting' => $sorting,
            'method'  => $method,
            'params'  => $params,
        ]);
    }

    /**
     * Обновить шаг трансформации по первичному ключу.
     *
     * @param string $primary          Первичный ключ.
     * @param array  $updateAttributes Атрибуты для обновления.
     *
     * @return int
     */
    public function updateStep(string $primary, array $updateAttributes): int
    {
        return TransformationStep::where('id', $primary)->update($updateAttributes);
    }

    /**
     * Удалить шаг трансформации по первичному ключу.
     *
     * @param int $primary Первичный ключ.
     *
     * @return int
     */
    public function deleteStepByPrimary(int $primary): int
    {
        return TransformationStep::destroy($primary);
    }

    /**
     * Удалить шаги трансформации по коду.
     *
     * @param string $code Код трансформации.
     *
     * @return bool|null
     */
    public function deleteStepsByCode(string $code)
    {
        return TransformationStep::where('code', $code)->delete();
    }

    /**
     * Удалить шаги трансформации по методу.
     *
     * @param string $method Метод трансформации.
     *
     * @return bool|null
     */
    public function deleteStepsByMethod(string $method)
    {
        return TransformationStep::where('method', $method)->delete();
    }
}
