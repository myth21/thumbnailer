<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ThumbnailRequestInterface;
use App\Exception\AppRuntimeException;
use App\Job\JobMessage;
use App\Job\PayloadInterface;
use App\Repository\TransformationRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use JsonException;

use function config;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Отвечает за алгоритмы получения миниатюр.
 */
class ImageThumbnailer implements ImageThumbnailerInterface
{
    /**
     * Конструктор класса.
     *
     * @param FileKeeperInterface               $fileKeeper               Объект знающий куда и как сохранять файл(ы).
     * @param TransformationServiceInterface    $transformationService    Трансформатор изображения.
     * @param FileFindersChainInterface         $fileFinderChain          Объекты поиска файлов.
     * @param TransformationRepositoryInterface $transformationRepository Объекты хранения данных.
     */
    public function __construct(
        protected FileKeeperInterface $fileKeeper,
        protected TransformationServiceInterface $transformationService,
        protected FileFindersChainInterface $fileFinderChain,
        protected TransformationRepositoryInterface $transformationRepository
    )
    {
    }

    /**
     * Содержит алгоритм поиска изображения и его обработки.
     * Возвращает объект Imagick, в частности инициализированный параметрами из DTO.
     *
     * @param ThumbnailRequestInterface $thumbnailRequestDto Данные запроса для обработки изображения.
     *
     * @return Imagick
     * @throws ImagickException|JsonException Бросает обработчик изображения.
     */
    public function doThumbnail(ThumbnailRequestInterface $thumbnailRequestDto): Imagick
    {
        $code = $thumbnailRequestDto->getCode();
        $imagick = $this->fileFinderChain->getOriginImageInstance($thumbnailRequestDto);
        if (! Storage::disk('original')->exists($imagick->getFilename())) {
            $this->fileKeeper->saveOriginal($imagick->getFilename(), $imagick->getImageBlob());

            // Предполагается что если был сделан запрос на трансформацию изображения.
            // То значит CDN или nginx не отдал статику и изображения еще не были сгенерированы.
            // Поэтому первый запрос на генерацию одного из кодов, требует наличие оригинального изображения.
            // И все его трансформации согласно коду группы, которому принадлежит запрашиваемое изображение.
            $this->sendMessageToDoThumbnails($imagick, $code, $thumbnailRequestDto->getName(), 'upload');
        }

        $this->transformationService->transform($code, $imagick);
        $imagick->setFormat($thumbnailRequestDto->getType());

        $this->fileKeeper->save($code, $thumbnailRequestDto->getImageName(), $imagick->getImageBlob());

        return $imagick;
    }

    /**
     * Отправляет сообщение в очередь для обработки изображения по всем кодам группы.
     *
     * @param Imagick $imagick Обработчик изображения.
     * @param string  $code    Код обработки.
     * @param string  $event   Событие идентификатор.
     *
     * @throws Exception|JsonException Если что-то не так.
     */
    protected function sendMessageToDoThumbnails(Imagick $imagick, string $code, string $fileName, string $event): void
    {
        $imageBlob = $imagick->getImageBlob();
        $groupCode = $this->transformationRepository->getByCode($code)->getGroupCode();
        $uploadMessageData = JobMessage::generateMessage($imageBlob, $fileName, $groupCode, $event);
        $jobJson = json_encode($uploadMessageData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $this->sendMessageInQueue($jobJson);
    }

    /**
     * Обрабатывает изображение, пришедшее из шины, согласно кодам полученным через коду группы.
     *
     * @param PayloadInterface $payload Полезная нагрузка.
     * @param Imagick          $imagick Обработчик изображения.
     *
     * @return array
     * @throws AppRuntimeException|ImagickException При не нахождении кода группы.
     */
    public function doThumbnails(PayloadInterface $payload, Imagick $imagick): array
    {
        $pathToImages = [];
        $transforms = $this->transformationRepository->getByGroupCode($payload->getGroupCode());
        if (empty($transforms)) {
            throw new AppRuntimeException('Code group ' . $payload->getGroupCode() . ' not found.');
        }

        // Save original image first.
        $originalImageName = $this->fileKeeper->getAdaptedOriginalImageName($payload->getImageName());
        $pathToImages[] = $this->fileKeeper->saveOriginal($originalImageName, $imagick->getImageBlob());
        $fileName = $payload->getFileName();

        foreach ($transforms as $transformation) {
            $this->transformationService->transform($transformation->code, $imagick);
            // Generate other image types.
            foreach ($this->transformationRepository->getTypes() as $otherType) {
                $imagick->setImageFormat($otherType);
                $imageName = $fileName . '.' . $otherType;
                $pathToImages[] = $this->fileKeeper->save($transformation->code, $imageName, $imagick->getImageBlob());
            }
        }

        return $pathToImages;
    }

    /**
     * Удаляет изображение согласно кодам полученным через коду группы.
     *
     * @param PayloadInterface $payload Полезная нагрузка.
     *
     * @return array
     */
    public function deleteThumbnails(PayloadInterface $payload): array
    {
        $pathToImages = [];
        $originalImageName = $this->fileKeeper->getAdaptedOriginalImageName($payload->getImageName());
        $pathToImages[] = $this->fileKeeper->deleteOriginal($originalImageName);

        $transformations = $this->transformationRepository->getByGroupCode($payload->getGroupCode());
        foreach ($transformations as $transformation) {
            foreach ($this->transformationRepository->getTypes() as $otherType) {
                $imageName = $payload->getFileName() . '.' . $otherType;
                $pathToImages[] = $this->fileKeeper->delete($transformation->code, $imageName);
            }
        }

        return $pathToImages;
    }

    /**
     * Отправляет сообщение в очередь.
     *
     * @param string $jobJson Сообщение в очередь.
     */
    private function sendMessageInQueue(string $jobJson): void
    {
        $params = config('queue.connections.rabbitmq.hosts')[0];
        $connection = new AMQPStreamConnection(
            $params['host'],
            $params['port'],
            $params['user'],
            $params['password'],
            $params['vhost']
        );

        $channelId = 1;

        $amqpMessage = new AMQPMessage($jobJson, [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
        $amqpMessage->set('application_headers', new AMQPTable([]));

        $exchangeName = config('queue.connections.rabbitmq.exchange');
        $connection->channel($channelId)->basic_publish($amqpMessage, $exchangeName);
    }
}
