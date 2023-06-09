<?php

declare(strict_types=1);

namespace Tests\Functional;

use PhpAmqpLib\Message\AMQPMessage;
use Tests\MessageTrait;
use Tests\Support\FunctionalTester;
use JsonException;

use function json_encode;
use function sleep;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Отвечает за тестирование связанное с отправкой сообщений в очередь.
 */
class QueueCest
{
    use MessageTrait;

    protected string $exchangeName;
    protected string $queueName;

    /**
     * Инициализация перед запуском тестов.
     *
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $this->exchangeName = $I->getModuleConfig('AMQP', 'exchangeName');
        $this->queueName = $I->getModuleConfig('AMQP', 'queueName');
    }

    /**
     * Отправляет сообщение на обработку изображения с событием "загрузка изображения".
     * Для данного кейса требуется отключить обработчиков очереди которые забирают сообщения из очереди.
     *
     * @param FunctionalTester $I
     * @throws JsonException
     */
    public function sendImgUploadMessageTest(FunctionalTester $I): void
    {
        $uploadMessageData = $this->getMessageData('upload');
        $jobJson = json_encode($uploadMessageData, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $I->seeQueueIsEmpty($this->queueName);
        $I->pushToExchange($this->exchangeName, new AMQPMessage($jobJson));
        sleep(1);
        $I->seeNumberOfMessagesInQueue($this->queueName, 1);
    }
}
