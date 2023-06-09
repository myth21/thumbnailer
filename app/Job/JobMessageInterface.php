<?php

declare(strict_types=1);

namespace App\Job;

/**
 * Описывает методы для работы с сообщением из очереди.
 */
interface JobMessageInterface
{
    /**
     * Возвращает объект полевной нагрузки.
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface;

    /**
     * Возвращает объект метаданных.
     *
     * @return JobMessageMetaDataInterface
     */
    public function getMetaData(): JobMessageMetaDataInterface;
}
