<?php

declare(strict_types = 1);

namespace App\Event;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Отвечает за событие отправки сообщения в очередь.
 */
class InputImageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * JSON сообщение из шины.
     *
     * @var string $jobMessage
     */
    protected string $jobMessage;

    /**
     * Конструктор класса.
     *
     * @param string $jobMessage Сообщение из шины.
     */
    public function __construct(string $jobMessage)
    {
        $this->jobMessage = $jobMessage;
    }

    /**
     * Возвращает JSON сообщения из шины.
     *
     * @return string Сообщение из шины.
     */
    public function getRawJobMessage(): string
    {
        return $this->jobMessage;
    }
}
