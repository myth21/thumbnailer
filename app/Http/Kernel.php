<?php

declare(strict_types = 1);

namespace App\Http;

use App\Http\Middleware\Web\RequestParser;
use App\Http\Middleware\Web\RequestValidator;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Middleware\SetCacheHeaders;

/**
 * Отвечает за регистрацию, управление сущностей middleware.
 */
class Kernel extends HttpKernel
{
    /**
     * Группы маршрутов приложения.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            RequestValidator::class,
            RequestParser::class,
        ],
    ];

    /**
     * Хранит приоритет выполнения классов middleware.
     *
     * @var string[]
     */
    protected $middlewarePriority = [
        RequestParser::class,
        RequestValidator::class,
    ];

    /**
     * Промежуточные маршруты приложения.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'cache.headers' => SetCacheHeaders::class,
    ];
}
