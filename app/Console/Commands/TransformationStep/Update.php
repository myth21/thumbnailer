<?php

declare(strict_types=1);

namespace App\Console\Commands\TransformationStep;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Throwable;

use function explode;

/**
 * Отвечает за обновление шагов трансформации.
 */
class Update extends Command implements CommandInterface
{
    /**
     * Обновляемые данные.
     *
     * @var string[]
     */
    protected array $updateAbleFields = [
        'code',
        'sorting',
        'method',
        'params',
    ];

    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:update-step {--id=} {--code=} {--sorting=} {--method=} {--params=*}';

    /**
     * Обновление модели трансформации.
     *
     * @var string
     */
    protected $description = 'Update transformation:
                              --id= --code= --sorting= --method= --params=key1:val1 --params=key2:val2';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        $updateAttributes = [];
        foreach ($this->updateAbleFields as $updateAbleField) {
            if ($this->option($updateAbleField)) {
                if ('params' === $updateAbleField) {
                    $params = [];
                    foreach ($this->option('params') as $param) {
                        [$key, $value] = explode(':', $param);
                        $params[$key] = $value;
                    }
                    $updateAttributes[$updateAbleField] = $params;
                } else {
                    $updateAttributes[$updateAbleField] = $this->option($updateAbleField);
                }
            }
        }

        $id = $this->option('id');

        try {
            $transformationRepository->updateStep($id, $updateAttributes);
        } catch (Throwable $e) {
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show-steps', []);

        return self::OK;
    }
}
