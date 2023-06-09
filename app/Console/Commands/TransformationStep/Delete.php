<?php

declare(strict_types=1);

namespace App\Console\Commands\TransformationStep;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Throwable;

/**
 * Отвечает за удаление шагов трансформации.
 */
class Delete extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:delete-step {--id=}';

    /**
     * Удаление модели трансформации.
     *
     * @var string
     */
    protected $description = 'Delete transformation: --id=';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        $id = $this->option('id');
        try {
            $transformationRepository->deleteStepByPrimary((int) $id);
        } catch (Throwable $e) {
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show-steps', []);

        return self::OK;
    }
}
