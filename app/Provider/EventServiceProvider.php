<?php

declare(strict_types=1);

namespace App\Provider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Отвечает за управление событиями и слушателями.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Карта слушателей событий приложения.
     *
     * @var array<class-string, array<int, class-string>> Связь события и слушателя этого события.
     */
    protected $listen = [
        // Связь событие InputImageEvent::class => [InputImageListener::class].
    ];

    /**
     * Прослушивает любое входящее событие для приложения.
     *
     * @return void
     */
    public function boot(): void
    {
    }
}
