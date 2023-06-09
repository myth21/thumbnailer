<?php

declare(strict_types=1);

namespace App\Http\Middleware\Web;

use App\DTO\ThumbnailRequestDto;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

use function preg_match;

/**
 * Отвечает за разбор параметров запроса.
 */
class RequestParser
{
    /**
     * Конструктор класса.
     *
     * @param string $urlPattern Строка урл паттерна.
     */
    public function __construct(protected string $urlPattern)
    {
    }

    /**
     * Парсит и устанавливает параметры в объекте запроса.
     *
     * @param Request $request Входящий запрос.
     * @param Closure $next    Функция замыкание.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            if (preg_match($this->urlPattern, $request->query('img'), $matches)) {
                $thumbnailRequestDto = new ThumbnailRequestDto($matches['code'], $matches['name'], $matches['type']);
                $request->attributes->set('thumbnailRequestDto', $thumbnailRequestDto);

                return $next($request);
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['source' => __METHOD__ . ':' . __LINE__]);
        }

        Log::error('Failed to parse all the required params', [
            'source' => __METHOD__ . ':' . __LINE__,
            'query'  => $request->query('img'),
        ]);
        return new Response('', Response::HTTP_NOT_FOUND);
    }
}
