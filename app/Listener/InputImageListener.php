<?php

declare(strict_types=1);

namespace App\Listener;

use App\Queue\Job\RabbitMQJob;
use App\Service\ImageThumbnailerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Imagick;
use RuntimeException;
use Throwable;

use function base64_decode;

/**
 * Слушатель соответствующего события.
 * ShouldQueue указывает на что слушатель слушает событие (привязанное в EventServiceProvider) из очереди.
 */
class InputImageListener implements ShouldQueue
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

            $imagick = new Imagick();
            $imagick->readImageBlob(base64_decode($payload->getBase64Image()));

            $pathToImages = $this->imageThumbnailer->doThumbnails($payload, $imagick);

            Log::info('Created images', ['pathToImages' => $pathToImages]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'source' => __METHOD__ . ':' . __LINE__,
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
            ]);

            $job->fail($exception);
        }
    }
}
