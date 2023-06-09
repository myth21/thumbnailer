<?php

declare(strict_types=1);

namespace App\Console\Commands;

/**
 * Описывает интерфейс консольных кодов выхода.
 */
interface CommandInterface
{
    /**
     * Команда выполнена успешно.
     */
    final public const OK = 0;

    /**
     * Команда завершилась с не указанной ошибкой.
     */
    final public const UNSPECIFIED_ERROR = 1;
}
