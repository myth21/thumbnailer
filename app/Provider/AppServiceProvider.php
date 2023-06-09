<?php

declare(strict_types = 1);

namespace App\Provider;

use App\Http\Middleware\Web\RequestParser;
use App\Job\JobMessage;
use App\Job\JobMessageInterface;
use App\Repository\ExternalImageRepository;
use App\Repository\TransformationRepository;
use App\Repository\TransformationRepositoryInterface;
use App\Service\FileFindersChain;
use App\Service\FileFindersChainInterface;
use App\Service\FileKeeperInterface;
use App\Service\ImageIdFileKeeper;
use App\Service\ImageNameFileKeeper;
use App\Service\ImageThumbnailer;
use App\Service\ImageThumbnailerInterface;
use App\Service\TransformationMethod\Optimize;
use App\Service\TransformationMethod\Resize;
use App\Service\TransformationService;
use App\Service\TransformationServiceInterface;
use Illuminate\Support\ServiceProvider;

use function app;
use function config;

/**
 * Отвечает за карту классов приложения.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Зарегистрировать фабрики приложения.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(JobMessageInterface::class, static function($app, $params) {
            return new JobMessage($params['decodedBodyMessage']);
        });

        $this->app->singleton(RequestParser::class, static function() {
            return new RequestParser(config('app.urlPattern'));
        });

        $this->app->bind(ImageThumbnailerInterface::class, ImageThumbnailer::class);

        $this->app->singleton(TransformationRepositoryInterface::class, TransformationRepository::class);
        $this->app->singleton(ExternalImageRepository::class, static function() {
            $params = config('database.connections.external');
            return new ExternalImageRepository($params);
        });
        $this->app->singleton('Resize', Resize::class);
        $this->app->singleton('Optimize', Optimize::class);

        $this->app->singleton(FileFindersChainInterface::class, static function() {
            $fileFinders = [];
            foreach (config('app.fileFinders') as $fileFinderClassName) {
                $fileFinders[] = app($fileFinderClassName);
            }

            return new FileFindersChain($fileFinders, app(FileKeeperInterface::class));
        });

        $this->app->singleton(TransformationServiceInterface::class, TransformationService::class);

        $this->app->singleton('ImageNameFileKeeper', ImageNameFileKeeper::class);
        $this->app->singleton('ImageIdFileKeeper', ImageIdFileKeeper::class);
        $this->app->singleton(FileKeeperInterface::class, static function() {
            return app(config('app.fileKeeper'));
        });
    }
}
