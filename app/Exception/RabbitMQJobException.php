<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

/**
 * Отвечает за перехват исключений брошенный при валидности сообщений шины.
 */
class RabbitMQJobException extends Exception
{

}
