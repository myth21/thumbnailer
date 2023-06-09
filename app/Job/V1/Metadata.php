<?php

declare(strict_types=1);

namespace App\Job\V1;

use App\Job\JobMessageMetaDataInterface;
use DateTimeImmutable;
use Exception;

use function bin2hex;
use function chr;
use function ord;
use function random_bytes;
use function str_split;
use function vsprintf;

/**
 * Модель метаданных сообщения (без учета версий).
 */
class Metadata implements JobMessageMetaDataInterface
{
    /**
     * Конструктор класса.
     *
     * @param string            $subject   Имя субъекта.
     * @param string            $event     Идентификатор сообщения.
     * @param string            $version   Версию сообщения формата major.minor.patch.
     * @param DateTimeImmutable $timestamp Объект времени.
     */
    public function __construct(
        protected string $subject,
        protected string $event,
        protected string $version,
        protected DateTimeImmutable $timestamp
    )
    {
    }

    /**
     * Возвращает имя субъекта, инициировавшего обработку сообщение.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Возвращает идентификатор сообщения.
     * Формат module.feature.action.event.
     *
     * @return string
     */
    public function getEventId(): string
    {
        return $this->event;
    }

    /**
     * Возвращает версию сообщения формата major.minor.patch.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Возвращает время создания сообщения в формате ISO-8601 (DATE_ATOM).
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
