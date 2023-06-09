<?php

declare(strict_types=1);

namespace App\Console\Commands\TransformationStep;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use function json_encode;

/**
 * Отвечает за создание шагов трансформации.
 */
class Show extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:show-steps';

    /**
     * Показать модели трансформаций.
     *
     * @var string
     */
    protected $description = 'Show transformation steps';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        $rows = [];
        $stepItems = $transformationRepository->getStepArray();
        foreach ($stepItems as $stepItem) {
            $stepItem['params'] = json_encode($stepItem['params']);
            $rows[] = $stepItem;
        }

        $this->table(
            ['id', 'code', 'sorting', 'method', 'params'],
            $rows
        );

        return self::OK;
    }
}
