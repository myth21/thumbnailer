<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель, отражает данные из таблицы-"справочник" $table.
 *
 * @method findOrFail(mixed $primaryKey) Возвращает модель или кидает исключение.
 *
 * @property string $code Первичный ключ модели.
 * @property string $group_code Код группы.
 * @property TransformationStep[] $steps Связанные модели
 */
class Transformation extends Model
{
    /**
     * Имя таблицы модели.
     *
     * @var string
     */
    protected $table = 'transformation';

    /**
     * Первичный ключ модели.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Тип первичный ключа модели.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'group_code',
        'title',
        'description',
    ];

    /**
     * Допустимые типы (расширения) изображений.
     *
     * @const string[]
     */
    protected const TYPES = [
        'jpg',
        'png',
        'webp',
    ];

    /**
     * Возвращает допустимые типы (расширения) изображений.
     *
     * @return string[]
     */
    public static function getTypes(): array
    {
        return self::TYPES;
    }

    /**
     * Связанные модели шагов, отсортированные по sorting.
     * При обращении как к свойству возвращает Illuminate\Database\Eloquent\Collection (перебираемый как модели).
     *
     * @return HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(TransformationStep::class, 'code', 'code')->orderBy('sorting');
    }

    /**
     * Возвращает код группы.
     *
     * @return string
     */
    public function getGroupCode(): string
    {
        return $this->attributes['group_code'];
    }
}
