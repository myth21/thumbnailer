<?php

declare(strict_types=1);

namespace App\Console\Commands\Transformation;

use App\Console\Commands\CommandInterface;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Throwable;

/**
 * Отвечает за обновление трансформации.
 */
class Update extends Command implements CommandInterface
{
    /**
     * Обновляемые данные.
     *
     * @var string[]
     */
    protected array $updateAbleFields = [
        'group_code',
        'title',
        'description',
    ];

    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:update {--code=} {--group_code=} {--title=} {--description=}';

    /**
     * Обновление модели трансформации.
     *
     * @var string
     */
    protected $description = 'Update transformation: --code= --group_code= --title= --description=';

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
                $updateAttributes[$updateAbleField] = $this->option($updateAbleField);
            }
        }
        $code = $this->option('code');

        try {
            $transformationRepository->update($code, $updateAttributes);
        } catch (Throwable $e) {
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show', []);

        return self::OK;
    }
}
