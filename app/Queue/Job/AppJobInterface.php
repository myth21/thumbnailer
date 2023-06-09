<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Job\JobMessageInterface;

/**
 * Описание задач приложения.
 */
interface AppJobInterface
{
    /**
     * Возвращает объект сообщения.
     *
     * @return JobMessageInterface
     */
    public function getMessage(): JobMessageInterface;
}
