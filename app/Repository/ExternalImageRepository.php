<?php

declare(strict_types=1);

namespace App\Repository;

use Illuminate\Support\Facades\DB;

/**
 * Отвечает за работу с внешним хранилищем сущности изображения.
 */
class ExternalImageRepository
{
    /**
     * Конструктор класса.
     *
     * @param array $params Параметры схемы базы.
     */
    public function __construct(
        protected array $params
    ) {
    }

    /**
     * Возвращает путь к изображению.
     *
     * @param string $requestImageName Имя или идентификатор запрашиваемого изображения.
     *
     * @return string
     */
    public function getImagePath(string $requestImageName): string
    {
        $std = DB::connection($this->params['connection'])
            ->table($this->params['target_table'])
            ->select()
            ->where($this->params['id_column'], '=', $requestImageName)
            ->get()
            ->firstOrFail();
        return $std->{$this->params['sub_dir_column']} . '/' . $std->{$this->params['image_column']};
    }
}
