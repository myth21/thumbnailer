<?php

declare(strict_types = 1);

namespace App\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exception\AMQPIOException;

/**
 * Отвечает за перехват исключений в ядре и приложении.
 */
class Handler extends ExceptionHandler
{
    /**
     * Регистрирует обработчиков исключений.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(static function (AMQPIOException $e) {
            Log::error('AMQPIOException: ' . $e->getMessage());
        })->stop();

        $this->reportable(static function (RabbitMQJobException $e) {
            Log::error('RabbitMQJobException: ' . $e->getMessage());
        })->stop();
    }
}
