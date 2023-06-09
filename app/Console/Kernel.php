<?php

declare(strict_types = 1);

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Отвечает за работу с консольными командами.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Регистрация команд и подключение конфигурации.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
