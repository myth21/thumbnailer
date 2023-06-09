<?php

declare(strict_types=1);

namespace App\Console\Commands\Transformation;

use App\Console\Commands\CommandInterface;
use App\Exception\AppRuntimeException;
use App\Repository\TransformationRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

use function explode;
use function preg_replace;
use function readline;

use function trim;

/**
 * Отвечает за создание трансформации.
 */
class Create extends Command implements CommandInterface
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'command:create {--code=} {--group_code=} {--title=} {--desc=}';

    /**
     * Создание модели трансформации.
     *
     * @var string
     */
    protected $description = 'Create transformation: --code= --group_code= --title= --desc=';

    /**
     * Запустить команду на выполнение.
     *
     * @param TransformationRepository $transformationRepository Объект хранилища.
     *
     * @return int
     */
    public function handle(TransformationRepository $transformationRepository): int
    {
        try {
            $inputs['code'] = $this->option('code');
            $inputs['group_code'] = $this->option('group_code');
            $inputs['title'] = $this->option('title');
            $inputs['desc'] = $this->option('desc');
            foreach ($inputs as $key => $input) {
                if (null === $input) {
                    throw new AppRuntimeException('Param "' . $key . '" is required');
                }
            }

            $this->line('Трансформация подготовлена:');
            $this->table(['code', 'group_code', 'title', 'desc'], [
                [$inputs['code'], $inputs['group_code'], $inputs['title'], $inputs['desc']],
            ]);

            $this->line('Сейчас вам будет предложено ввести все шаги трансформации.');
            $this->line('Согласно следующим параметрам:');
            $this->table(['method', 'params', 'sorting'], []);
            $this->line('Операция транзакционная. Нажмите ctrl+C чтобы прервать операцию.');

            $transformationSteps = [];
            do {
                $transformationSteps[] = $this->getTransformationStep();
                $line = readline('Завершить ввод шагов трансформации и сохранить ("y")?:');
            } while ('y' !== $line);

            DB::beginTransaction();
            $transformationRepository->save($inputs['code'], $inputs['group_code'], $inputs['title'], $inputs['desc']);
            foreach ($transformationSteps as $transformationStep) {
                $sorting = (int) $transformationStep['sorting'];
                $method = $transformationStep['method'];
                $params = $transformationStep['params'];
                $transformationRepository->saveStep($inputs['code'], $sorting, $method, $params);
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->warn($e->getMessage());
            $this->line($this->description);
            return self::UNSPECIFIED_ERROR;
        }

        $this->call('command:show', []);
        $this->call('command:show-steps', []);

        return self::OK;
    }

    /**
     * Возвращает введенные пользователем параметры шага трансформации.
     *
     * @return array
     */
    protected function getTransformationStep(): array
    {
        $transformationStep = [];

        $transformationStep['method'] = readline('Введите имя метода шага трансформации (Resize, Optimize), method:');
        $inputParams = readline('Введите параметры согласно синтаксису (key1=val1 key2=val2), params: ');
        $transformationStep['sorting'] = readline('Введите приоритет сортировки (int), sorting:');

        $strParams = trim(preg_replace('/\s+/', ' ', $inputParams));
        $explodeBySpaceParams = explode(' ', $strParams);
        $params = [];
        foreach ($explodeBySpaceParams as $param) {
            [$key, $value] = explode('=', $param);
            $params[$key] = $value;
        }

        $transformationStep['params'] = $params;

        return $transformationStep;
    }
}
