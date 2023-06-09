<?php

declare(strict_types = 1);

namespace App\Provider;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

use function base_path;

/**
 * Отвечает за настройки маршрутов, управление.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Определяет привязки модели маршрута, фильтры шаблонов и т. д.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->routes(function() {
            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }
}
