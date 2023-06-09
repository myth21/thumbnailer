<?php

declare(strict_types=1);

namespace App\Job;

use App\Job\V1\Metadata;
use App\Job\V1\Payload;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

use Ramsey\Uuid\Uuid;

use function base64_encode;

/**
 * Модель сообщения (без учета версий).
 */
class JobMessage implements JobMessageInterface
{
    /**
     * Метаданные сообщения.
     *
     * @var JobMessageMetaDataInterface
     */
    protected JobMessageMetaDataInterface $metaData;

    /**
     * Полезная нагрузка сообщения.
     *
     * @var PayloadInterface
     */
    protected PayloadInterface $payload;

    /**
     * Конструктор класса.
     *
     * @param array $jobMessage Декодированные параметры из JSON сообщения.
     *
     * @throws Exception При некорректной дате.
     */
    public function __construct(protected array $jobMessage)
    {
        $this->metaData = new Metadata(
            $jobMessage['subject'],
            $jobMessage['event'],
            $jobMessage['version'],
            new DateTimeImmutable($jobMessage['timestamp'])
        );
        $this->payload = new Payload(
            $jobMessage['payload']['base64Image'],
            $jobMessage['payload']['imageName'],
            $jobMessage['payload']['groupCode']
        );
    }

    /**
     * Возвращает объект полезной нагрузки.
     *
     * @return JobMessageMetaDataInterface
     */
    public function getMetaData(): JobMessageMetaDataInterface
    {
        return $this->metaData;
    }

    /**
     * Возвращает объект полезной нагрузки.
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }

    /**
     * Возвращает массив данных сообщения.
     *
     * @param string $blobImage Двоичные данные изображения.
     * @param string $fileName  Имя файла.
     * @param string $groupCode Код группы.
     * @param string $event     Идентификатор события.
     *
     * @return array
     * @throws Exception Если что-то не так.
     */
    public static function generateMessage(string $blobImage, string $fileName, string $groupCode, string $event): array
    {
        return [
            'payload'   => [
                // Image content.
                'base64Image' => base64_encode($blobImage),
                // Name for saving.
                'imageName'   => $fileName,
                // Group code is related with handle codes for processing.
                'groupCode'   => $groupCode,
            ],
            'uuid'      => Uuid::uuid4()->toString(),
            'subject'   => self::class,
            // If you want to delete thumbnails use `delete` instead upload.
            'event'     => 'thumbnailer.image.' . $event . '.complete',
            'version'   => '1.0.0',
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }
}
