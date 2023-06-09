<?php

declare(strict_types=1);

namespace App\Http\Middleware\Web;

use App\DTO\ThumbnailRequestDto;
use App\Repository\TransformationRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as FacadeValidator;
use Illuminate\Validation\Rule;

/**
 * Отвечает за валидацию параметров запроса.
 */
class RequestValidator
{
    private const FIELD_NAME_LENGTH = 255;

    /**
     * Конструктор класса.
     *
     * @param TransformationRepositoryInterface $transformationRepository Объект хранилища.
     */
    public function __construct(protected TransformationRepositoryInterface $transformationRepository) {
    }

    /**
     * Проверяет параметры запроса.
     *
     * @param Request $request Входящий запрос.
     * @param Closure $next    Функция замыкание.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /* @var ThumbnailRequestDto $thumbnailRequestDto */
        $thumbnailRequestDto = $request->attributes->get('thumbnailRequestDto');
        /* @var TransformationRepositoryInterface $transformationRepository */
        $validator = FacadeValidator::make(
            [
                'code' => $thumbnailRequestDto->getCode(),
                'name' => $thumbnailRequestDto->getName(),
                'type' => $thumbnailRequestDto->getType(),
            ],
            [
                'code' => [
                    'required',
                    Rule::in($this->transformationRepository->getCodes()),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:' . self::FIELD_NAME_LENGTH,
                ],
                'type' => [
                    'required',
                    Rule::in($this->transformationRepository->getTypes()),
                ],
            ],
            [
                'code.in'  => 'Параметр :attribute должен быть один из: :values',
                'name.max' => 'Имя файла не должно быть более ' . self::FIELD_NAME_LENGTH . ' символов',
            ]
        );

        if ($validator->fails()) {
            Log::error($validator->getMessageBag()->first(), ['source' => __METHOD__ . ':' . __LINE__]);
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
