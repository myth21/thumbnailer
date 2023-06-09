<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use App\Repository\TransformationRepository;
use App\Service\ImageNameFileKeeper;
use Imagick;
use ImagickException;
use JsonException;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\MessageTrait;
use Tests\Support\AcceptanceTester;

use function json_encode;
use function sleep;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Отвечает за тестирование доступности изображений по HTTP.
 */
class BrowserCest
{
    use MessageTrait;

    /**
     * Проверить доступность миниатюр изображений.
     *
     * @param AcceptanceTester $I
     * @throws ImagickException
     */
    public function getAllImagesByGroupCodeTest(AcceptanceTester $I): void
    {
        $payload = $this->getPayload();
        $imagick = new Imagick();
        $types = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'webp' => 'image/x-webp',
        ];

        $codes = $I->grabColumnFromDatabase('transformation', 'code', []);
        foreach ($codes as $code) {

            $isSleep = true;
            $imageTypes = (new TransformationRepository())->getTypes();
            foreach ($imageTypes as $imageType) {

                $imagesBaseUrl = $I->getModuleConfig('PhpBrowser', 'handledImagesBaseUrl');
                $fileNamePath = (new ImageNameFileKeeper())->getImagePath($code, $payload->getFileName());
                $creatingUrl = $imagesBaseUrl . $fileNamePath . '.' . $imageType;
                $response = $I->sendGet($creatingUrl);
                // При первом запросе на обработку изображения требуется время на обработку других изображений,
                // если они еще не были обработаны. Изображения обрабатываются на основе оригинального.
                // Поэтому также потребуется найти и сохранить оригинальное изображение если его еще не было.
                // Для этих целей взято 15 секунд ожидания.
                if ($isSleep) {
                    sleep(15);
                    $isSleep = false;
                }

                $I->seeResponseCodeIs(200);

                $imagick->readImageBlob($response);

                $I->assertArrayHasKey($imageType, $types);
                $I->assertEquals($types[$imageType], $imagick->getImageMimeType());
            }
        }
    }

    /**
     * Отправить сообщение на удаление обработанных изображений.
     *
     * @throws JsonException Если что то не так.
     */
    public function deleteAllImagesByGroupCodeTest(AcceptanceTester $I): void
    {
        $deleteMessageData = $this->getMessageData('delete');
        $jobJson = json_encode($deleteMessageData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $exchangeName = $I->getModuleConfig('AMQP', 'exchangeName');
        $I->pushToExchange($exchangeName, new AMQPMessage($jobJson));
    }
}
