<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Exception\RabbitMQJobException;
use App\Job\JobMessageInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

use function array_key_exists;
use function config;
use function json_decode;
use function version_compare;

use const JSON_THROW_ON_ERROR;

/**
 * Для переопределения поведения обработки задач.
 *
 * @link https://github.com/vyuldashev/laravel-queue-rabbitmq#use-your-own-rabbitmqjob-class
 */
class RabbitMQJob extends BaseJob implements AppJobInterface
{
    /**
     * Поддерживаемые события.
     */
    protected array $supportEvents = [];

    /**
     * Поддерживаемые версии.
     */
    protected array $supportVersions = [];

    /**
     * Декодированное сообщение.
     */
    protected array $decodedBodyMessage;

    /**
     * Объект сообщения.
     */
    protected JobMessageInterface $jobMessage;

    /**
     * Массив полезной нагрузки.
     */
    private array $payload;

    /**
     * Возвращает полезную нагрузку сообщения.
     *
     * @return array{job: string, data: array}
     * @throws RuntimeException|JsonException|RabbitMQJobException При не валидном JSON.
     */
    public function payload(): array
    {
        // Mark after fire().
        if ($this->isDeleted()) {
            return $this->payload;
        }

        if (isset($this->payload)) {
            return $this->payload;
        }

        $jobMessage = $this->getMessage();
        $this->validate($jobMessage);

        $this->payload = [
            'job'  => $this->getEventHandlerName($jobMessage->getMetaData()->getEventId()),
            'data' => $this->getDecodedBodyMessage(),
        ];

        return $this->payload;
    }

    /**
     * Передать обработчику задачу и удалить её.
     *
     * @return void
     * @throws JsonException|BindingResolutionException При не валидном JSON.
     */
    public function fire(): void
    {
        Log::info('Call method ' . __METHOD__);

        $jobMessage = $this->getMessage();
        $className = $this->getEventHandlerName($jobMessage->getMetaData()->getEventId());
        $this->instance = $this->resolve($className);
        if ($this->instance instanceof ShouldQueue) {
            $this->instance->handle($this);
            $this->delete();
        }
    }

    /**
     * Возвращает имя класса обработчика события.
     *
     * @param string $eventId Идентификатор события.
     *
     * @return string
     * @throws RuntimeException При не валидности.
     */
    private function getEventHandlerName(string $eventId): string
    {
        $supportEvents = $this->getSupportEvents();
        return $supportEvents[$eventId];
    }

    /**
     * Возвращает декодированное сообщение JSON.
     *
     * @return array
     * @throws JsonException При не валидном JSON.
     */
    protected function getDecodedBodyMessage(): array
    {
        if (isset($this->decodedBodyMessage)) {
            return $this->decodedBodyMessage;
        }

        $this->decodedBodyMessage = json_decode($this->getRawBody(), true, 512, JSON_THROW_ON_ERROR);
        return $this->decodedBodyMessage;
    }

    /**
     * Валидирует сообщение.
     *
     * @param JobMessageInterface $jobMessage Объект сообщения.
     *
     * @throws RabbitMQJobException При не валидности.
     */
    private function validate(JobMessageInterface $jobMessage): void
    {
        $version = $jobMessage->getMetaData()->getVersion();
        $supportVersions = $this->getSupportVersions();

        // Игнорировать сообщения, версии которых младше поддерживаемых.
        if (version_compare($version, $supportVersions['min'], '<')) {
            throw new RabbitMQJobException('Version ' . $version . ' not longer supported.');
        }

        // Игнорировать сообщения, версии которых старше поддерживаемых.
        if (version_compare($version, $supportVersions['max'], '>')) {
            throw new RabbitMQJobException('Version ' . $version . ' not supported yet.');
        }

        $eventId = $jobMessage->getMetaData()->getEventId();
        if (! array_key_exists($eventId, $this->getSupportEvents())) {
            throw new RabbitMQJobException('Event id ' . $eventId . ' not found.');
        }
    }

    /**
     * Возвращает поддерживаемые события.
     *
     * @return array
     */
    protected function getSupportEvents(): array
    {
        if (empty($this->supportEvents)) {
            $this->supportEvents = config('app.supportEvents');
        }

        return $this->supportEvents;
    }

    /**
     * Возвращает поддерживаемые версии.
     *
     * @return array
     */
    protected function getSupportVersions(): array
    {
        if (empty($this->supportVersions)) {
            $this->supportVersions = config('app.supportVersions');
        }

        return $this->supportVersions;
    }

    /**
     * Возвращает сообщение задачи.
     *
     * @return JobMessageInterface
     * @throws JsonException При не успешном парсинге JSON.
     */
    public function getMessage(): JobMessageInterface
    {
        if (isset($this->jobMessage)) {
            return $this->jobMessage;
        }

        $this->jobMessage = app(JobMessageInterface::class, ['decodedBodyMessage' => $this->getDecodedBodyMessage()]);

        return $this->jobMessage;
    }
}
