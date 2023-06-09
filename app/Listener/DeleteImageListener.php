<?php

declare(strict_types=1);

namespace App\Listener;

use App\Queue\Job\RabbitMQJob;
use App\Service\ImageThumbnailerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Слушатель соответствующего события.
 * ShouldQueue указывает на что слушатель слушает событие (привязанное в EventServiceProvider) из очереди.
 */
class DeleteImageListener implements ShouldQueue
{
    /**
     * Создать слушателя событий.
     *
     * @param ImageThumbnailerInterface $imageThumbnailer Логика обработки, сохранения, получения изображения.
     */
    public function __construct(
        protected ImageThumbnailerInterface $imageThumbnailer,
    )
    {
    }

    /**
     * Обработать событие (из очереди) и соответствующие из него данные.
     *
     * @param RabbitMQJob $job Объект сообщения из очереди.
     *
     * @throws RuntimeException При какой либо ошибке в процессе обработки.
     */
    public function handle(RabbitMQJob $job): void
    {
        Log::info('Call method ' . __METHOD__);

        try {
            $payload = $job->getMessage()->getPayload();
            $pathToImages = $this->imageThumbnailer->deleteThumbnails($payload);

            Log::info('Deleted images', [
                'pathToImages' => $pathToImages,
            ]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'source' => __METHOD__ . ':' . __LINE__,
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
            ]);

            $job->fail($exception);
        }
    }
}
