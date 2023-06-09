<?php

declare(strict_types=1);

namespace App\Job;

use DateTimeImmutable;

/**
 * Описывает интерфейс получения мета данных из сообщения шины.
 */
interface JobMessageMetaDataInterface
{
    /**
     * Возвращает имя субъекта, инициировавшего обработку сообщение.
     *
     * @return string
     */
    public function getSubject(): string;

    /**
     * Возвращает идентификатор сообщения.
     *
     * @return string
     */
    public function getEventId(): string;

    /**
     * Возвращает версию сообщения формата major.minor.patch.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Возвращает время создания сообщения в формате ISO-8601 (DATE_ATOM).
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable;
}
