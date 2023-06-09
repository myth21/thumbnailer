<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель, отражает данные из таблицы-"справочник" $table.
 *
 * @property string $code Внешний первичный ключ.
 * @property int $sorting Сортировка шагов.
 * @property string $method Имя метода (класса).
 * @property array $params Параметры для применения шага трансформации.
 */
class TransformationStep extends Model
{
    /**
     * Имя таблицы модели.
     *
     * @var string
     */
    protected $table = 'transformation_step';

    /**
     * Указывает, должна ли модель иметь временную метку.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Заполняемые атрибуты модели.
     *
     * @var string[]
     */
    protected $fillable = [
        'code',
        'sorting',
        'method',
        'params',
    ];

    /**
     * Преобразование параметров json из базы в массив при запросе этого свойства у объекта.
     *
     * @var string[]
     */
    protected $casts = [
        'params' => 'array',
    ];

    /**
     * Возвращает имя метода, которое соответствует имени класса в приложении.
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->method;
    }

    /**
     * Возвращает массив параметров которые применяются при шаге трансформации.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
