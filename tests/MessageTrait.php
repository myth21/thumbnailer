<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Controller\TransformationController;
use App\Job\V1\Payload;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

use Ramsey\Uuid\Uuid;

use function file_get_contents;

trait MessageTrait
{
    /**
     * Возвращает данные сообщения.
     *
     * @param string $event
     *
     * @return array
     * @throws Exception
     */
    protected function getMessageData(string $event): array
    {
        return [
            'payload'   => $this->getPayloadData(),
            'uuid'      => Uuid::uuid4()->toString(),
            'subject'   => TransformationController::class,
            'event'     => 'thumbnailer.image.' . $event . '.complete',
            'version'   => '1.0.0',
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * Возвращает полезные данные сообщения.
     *
     * @param string|null $key
     * @return string|array
     */
    protected function getPayloadData(string $key = null): string|array
    {
        $data = [
            // Image content.
            'base64Image' => file_get_contents(__DIR__ . '/Support/Data/base64jpg'),
            // Name for saving.
            'imageName'   => '4de/9ghn6w2zjk3tvm18s0h6ubcuno43an5j-jpg.jpg',
            // Group code is related with handle codes for processing.
            'groupCode'   => 'breakpoint',
        ];

        return $key ? $data[$key] : $data;
    }

    /**
     * Возвращает объект полезной нагрузки.
     */
    protected function getPayload(): Payload
    {
        return new Payload(
            $this->getPayloadData('base64Image'),
            $this->getPayloadData('imageName'),
            $this->getPayloadData('groupCode')
        );
    }
}
