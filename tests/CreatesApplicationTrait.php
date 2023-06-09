<?php
declare(strict_types = 1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * Трейт для работы с методами объекта приложения.
 */
trait CreatesApplicationTrait
{
    /**
     * Создать экземпляр приложения.
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
