<?php

declare(strict_types=1);

namespace App\Console\Commands\TransformationStep;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Throwable;

use function explode;

/**
 * Отвечает за создание шагов трансформации.
 */
class Create extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     * Пример множественного параметра --params=key1:val1 --params=key2:val2 ...
     *
     * @var string
     */
    protected $signature = 'command:create-step {--code=} {--sorting=} {--method=} {--params=*}';

    /**
     * Создание модели шага трансформации.
     *
     * @var string
     */
    protected $description = 'Create transformation step:
                              --code= --sorting= --method= --params=key1:val1 --params=key2:val2';

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
        $sorting = $this->option('sorting');
        $method = $this->option('method');
        $params = [];
        foreach ($this->option('params') as $param) {
            [$key, $value] = explode(':', $param);
            $params[$key] = $value;
        }

        try {
            $transformationRepository->saveStep($code, (int) $sorting, $method, $params);
        } catch (Throwable $e) {
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show-steps', []);

        return self::OK;
    }
}
