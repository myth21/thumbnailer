<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controller\TransformationController;
use App\Job\V1\Payload;
use App\Repository\TransformationRepositoryInterface;
use App\Service\FileKeeperInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;
use Throwable;

use function app;
use function array_map;
use function base64_decode;
use function config;
use function env;
use function exif_imagetype;
use function file_get_contents;
use function image_type_to_mime_type;
use function json_encode;
use function round;
use function time;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Тестирует отправку сообщений в очередь.
 */
class QueueMessageProducerTest extends AbstractTestCase
{
    /**
     * Объект соединения.
     *
     * @var AMQPStreamConnection
     */
    protected AMQPStreamConnection $connection;

    /**
     * Объект канала в котором обрабатываются очереди.
     *
     * @var AMQPChannel
     */
    protected AMQPChannel $channel;

    /**
     * Имя очереди куда будет отправлено сообщение.
     *
     * @var string
     */
    protected string $queueName;

    /**
     * Имя точки обмена.
     *
     * @var string
     */
    protected string $exchangeName;

    /**
     * Объект хранения файлов.
     *
     * @var FileKeeperInterface
     */
    protected FileKeeperInterface $fileKeeper;

    /**
     * Код группы для обработки изображения.
     */
    protected static string $groupCode;

    /**
     * Объект хранилища данных.
     *
     * @var TransformationRepositoryInterface
     */
    protected TransformationRepositoryInterface $transformationRepository;

    public static function setUpBeforeClass(): void
    {
        self::$groupCode = env('GROUP_CODE');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $params = config('queue.connections.rabbitmq.hosts')[0];
        $this->connection = new AMQPStreamConnection(
            $params['host'],
            $params['port'],
            $params['user'],
            $params['password'],
            $params['vhost']
        );

        $channelId = 1;
        $this->channel = $this->connection->channel($channelId);

        $this->queueName = config('queue.connections.rabbitmq.queue');
        $this->exchangeName = config('queue.connections.rabbitmq.exchange');

        $this->fileKeeper = app(FileKeeperInterface::class);
        $this->transformationRepository = app(TransformationRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Тестирует отправку сообщения в очередь на обработку изображений.
     *
     * @throws Exception При закрытии соединения или не валидном JSON.
     */
    public function testPublishingMessageAndSavingImages(): void
    {
        try {
            $uploadMessageData = $this->getUploadMessageData();
            $jobJson = json_encode($uploadMessageData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $this->publicMessageInQueue($jobJson);

            $this->checkSavingImages([$uploadMessageData]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'source' => __METHOD__ . ':' . __LINE__,
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
            Assert::fail($exception->getMessage());
        }
    }

    /**
     * Тестирует отправку сообщения в очередь на удаление изображений.
     *
     * @throws Exception При закрытии соединения или не валидном JSON.
     */
    public function testTryingDeleteImagesViaMessage(): void
    {
        try {
            $uploadMessageData = $this->getDeleteMessageData();
            $jobJson = json_encode($uploadMessageData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $this->publicMessageInQueue($jobJson);

            // Если исключений не выброшено, то эта часть теста считается успешной.
            $this->assertTrue(true);

        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'source' => __METHOD__ . ':' . __LINE__,
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
            Assert::fail($exception->getMessage());
        }
    }

    /**
     * Опубликовать сообщение в очередь.
     *
     * @param string $jobJson
     */
    private function publicMessageInQueue(string $jobJson): void
    {
        $amqpMessage = new AMQPMessage($jobJson, [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
        $amqpMessage->set('application_headers', new AMQPTable([]));
        $this->channel->basic_publish($amqpMessage, $this->exchangeName);
    }

    /**
     * Проверить создание изображения за выделенное время.
     *
     * @param array $messages
     * @throws ImagickException
     */
    private function checkSavingImages(array $messages): void
    {
        // Code sniffer does not allow to use the function sleep.
        $waitTime = 20;
        $breakTime = time() + $waitTime;
        while (true) {
            if (time() < $breakTime) {
                continue;
            }
            break;
        }

        $imagick = new Imagick();
        array_map(function ($msg) use ($waitTime, $imagick) {

            $payload = new Payload($msg['payload']['base64Image'], $msg['payload']['imageName'], $msg['payload']['groupCode']);
            $imagick->readImageBlob(base64_decode($payload->getBase64Image()));

            $originalImageName = $this->fileKeeper->getAdaptedOriginalImageName($payload->getImageName());

            if ($this->fileKeeper->isOriginalFileSaved($originalImageName)) {
                $message = $this->getSuccessMessage('original', $originalImageName);
                self::assertTrue(true, $message);
                Log::info($message);
            } else {
                $message = 'WARNING, original file ' . $originalImageName . ' NOT created for ' . $waitTime . ' seconds';
                self::assertTrue(false, $message);
            }

            $transforms = $this->transformationRepository->getByGroupCode($msg['payload']['groupCode']);
            foreach ($transforms as $transform) {
                $this->checkSavingOtherImages($payload->getFileName(), $transform->code);
            }

        }, $messages);
    }

    /**
     * Проверить создание других изображений за выделенное время.
     *
     * @param string $fileName Имя файла.
     * @param string $code Код обработки.
     */
    private function checkSavingOtherImages(string $fileName, string $code): void
    {
        $types = $this->transformationRepository->getTypes();
        foreach ($types as $type) {

            $imageName = $fileName . '.' . $type;
            $pathToImage = $this->fileKeeper->getImagePath($code, $imageName);

            if ($this->fileKeeper->isFileSaved($code, $imageName)) {
                $message = $this->getSuccessMessage('destination', $pathToImage);
                self::assertTrue(true, $message);
            } else {
                $message = 'WARNING, other file ' . $pathToImage . ' NOT created';
                self::assertTrue(false, $message);
            }
            Log::info($message);
        }
    }

    /**
     * Возвращает успешное сообщение.
     *
     * @param string $destination Имя диска.
     * @param string $pathToImage Путь к изображению.
     *
     * @return string
     */
    private function getSuccessMessage(string $destination, string $pathToImage): string
    {
        $disk = Storage::disk($destination);
        $mimeType = image_type_to_mime_type(exif_imagetype($disk->path($pathToImage)));
        $roundSize = round($disk->size($pathToImage) / 1000000, 2);

        return 'OK, file ' . $disk->url($pathToImage) . ' (' . $mimeType . ', ' . $roundSize . 'mb) created';
    }

    /**
     * Возвращает сообщение с событием выгрузить.
     *
     * @return array
     * @throws Exception
     */
    private function getUploadMessageData(): array
    {
        return $this->getMessageData('upload');
    }

    /**
     * Возвращает сообщение с событием удалить.
     *
     * @return array
     * @throws Exception
     */
    private function getDeleteMessageData(): array
    {
        return $this->getMessageData('delete');
    }

    /**
     * Возвращает сообщение.
     *
     * @param string $event
     *
     * @return array
     * @throws Exception
     */
    private function getMessageData(string $event): array
    {
        return [
            'payload'   => [
                // Image content.
                'base64Image' => file_get_contents(__DIR__ . '/../Support/Data/base64jpg'),
                // Name for saving.
                'imageName'   => 'asd/as/test-jpg.jpg',
                // Group code is related with handle codes for processing.
                'groupCode'   => self::$groupCode,
            ],
            'uuid'      => Uuid::uuid4()->toString(),
            'subject'   => TransformationController::class,
            // If you want to delete thumbnails use `delete` instead upload.
            'event'     => 'thumbnailer.image.' . $event . '.complete',
            'version'   => '1.0.0',
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }
}
