<?php

declare(strict_types=1);

namespace App\Console\Commands\Transformation;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;

/**
 * Отвечает за отображение трансформации.
 */
class Show extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:show';

    /**
     * Показать модели трансформаций.
     *
     * @var string
     */
    protected $description = 'Show transformations';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        $this->table(
            ['code', 'group_code', 'title', 'description'],
            $transformationRepository->getArray(['code', 'group_code', 'title', 'description'])
        );

        return self::OK;
    }
}
