<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Service\ImageThumbnailerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Обрабатывает HTTP запросы на трансформацию изображения.
 */
class TransformationController
{
    /**
     * Конструктор класса.
     *
     * @param ImageThumbnailerInterface $imageThumbnailer Логика обработки,сохранения,получения изображения.
     */
    public function __construct(
        protected ImageThumbnailerInterface $imageThumbnailer
    ) {
    }

    /**
     * Обработать запрашиваемое изображение и вернуть его с запрашиваемым Content-Type.
     *
     * @param Request $request Запрос на создание изображения.
     *
     * @return Response
     */
    public function doImageByCode(Request $request): Response
    {
        try {
            $imageRequestDto = $request->attributes->get('thumbnailRequestDto');
            $imagick = $this->imageThumbnailer->doThumbnail($imageRequestDto);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), [
                'source' => __METHOD__ . ':' . __LINE__,
                'thrown' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $response = new Response($imagick->getImageBlob(), Response::HTTP_OK, [
            'Content-Type' => $imagick->getImageMimeType(),
        ]);

        return $response->send();
    }
}
