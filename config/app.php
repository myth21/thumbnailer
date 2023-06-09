<?php

declare(strict_types = 1);

use App\Provider\AppServiceProvider;
use App\Provider\EventServiceProvider;
use App\Provider\RouteServiceProvider;
use App\Service\LocalFileFinder;
use App\Service\RemoteFileFinder;
use App\Service\HttpFileFinder;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use App\Listener\InputImageListener;
use App\Listener\DeleteImageListener;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name'            => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env'             => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug'           => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url'             => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone'        => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale'          => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale'    => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Autoloader Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers'       => [

        /*
         * Laravel Framework Service Providers...
         */
        BusServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleSupportServiceProvider::class,
        DatabaseServiceProvider::class,
        FilesystemServiceProvider::class,
        QueueServiceProvider::class,
        TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        EventServiceProvider::class,

        // Это используется в очередях.
        FoundationServiceProvider::class,
        NotificationServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        AppServiceProvider::class,
        RouteServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases'         => Facade::defaultAliases()->merge([])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Добавленные собственной конфигурации
    |--------------------------------------------------------------------------
    */

    // Объявление классов отвечающих за поиск и получение файлов, в порядке их применения.
    // Добавить класс RemoteFileFinder::class когда потребуется генерировать изображения найденные в базе и в ФС.
    'fileFinders'     => [LocalFileFinder::class, HttpFileFinder::class, RemoteFileFinder::class],

    // Объявление класса отвечающего за сохранение и получение url изображений.
    'fileKeeper'      => env('FILE_KEEPER', 'ImageNameFileKeeper'),

    // Паттерн url для обработки запросов на создание миниатюр png|jpg|jpeg|webp...
    'urlPattern'      => '/^\/(images)\/(?P<code>[^\/]+)\/(?P<name>.+?)\.(?P<type>jpg|webp|png)$/u',

    // Допустимые версии сообщений из шины.
    'supportVersions' => [
        'min' => '1.0.0',
        'max' => '1.0.0',
    ],
    // Допустимые идентификаторы события приходяще из шины.
    'supportEvents'   => [
        'thumbnailer.image.upload.complete' => InputImageListener::class,
        'thumbnailer.image.delete.complete' => DeleteImageListener::class,
    ],

    'pngOutBinaryPath'   => '/usr/local/bin/pngout',
];
