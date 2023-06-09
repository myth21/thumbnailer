<?php

declare(strict_types=1);

namespace App\Console\Commands\Transformation;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Throwable;

/**
 * Отвечает за удаление трансформации.
 */
class Delete extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:delete {--code=}';

    /**
     * Удаление модели трансформации.
     *
     * @var string
     */
    protected $description = 'Delete transformation: --code=';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        $code = $this->option('code');
        try {
            $transformationRepository->delete($code);
        } catch (Throwable $e) {
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show', []);

        return self::OK;
    }
}
