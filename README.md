## Описание

Сервис разворачивается для каждого проект индивидуально. 
Он позволяет создать изображения разных размеров и оптимизаций на основе предоставленного. 
Настройки размеров и оптимизаций задаются в базе данных, таблицах: transformation, transformation_step. 

Сервис использует систему очередей RabbitMQ для получения сообщений.
Для создания изображений разных размеров и оптимизаций, сервис ожидает сообщения из очереди определенного формата, описано ниже.
После обработки предоставленного изображения, разные размеры доступны по HTTP запросу.
Для получения URL определенного изображения используется алгоритм, описанный ниже в [Получение URL изображения](#getImgUrl).

На текущий момент сервис обрабатывает только три типа изображения: jpg, png, webp.

**Ориентировочная интеграция**

1. Развернуть сервис (devops), так чтобы к RabbitMQ можно было подключиться из приложения.
2. Настроить конфигурацию обработки изображений, согласно требованиями приложения (back).
3. Подключиться к RabbitMQ из приложения, для отправки сообщений с данными об изображении (back). 
Сервис использует этот PHP пакет php-amqplib/php-amqplib для работы с RabbitMQ.
4. На фронт или бэк части приложения сформировать ссылки на изображения, согласно алгоритму указанному в [Получение URL изображения](#getImgUrl) (back||front).

## Запуск сервиса

После запуска контейнеров, необходимо войти в контейнер php и выполнить команды миграции:

```
make migrate
make seed
```

## Возможные проблемы

- Нет директории images, тогда нужно создать вручную в директории public.
- 500-ая ошибка или не создаётся изображение по причине "Unable to create a directory at dir/subdir/subdir".

```
mkdir public/images
chmod -R 777 .
```

## Формат сообщений шины данных

Сообщение отправляемые в шину для обработки изображений должны иметь следующий формат, пример:

```
{
    "subject": "Userstory\\Authentication\\RegisterController",
    "event": "thumbnailer.image.upload.complete",
    "version": "1.0.0",
    "timestamp": "2022-01-24T14:03:12UTC",
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "payload": {
        "base64Image": "/9j/4AAQSkZJRgABAQAAAQABAAD",
        "imageName": "image_name.png",
        "groupCode": "product"
    }
}
```
Для удаления изображений используйте идентификатор события delete:

```
"event": "thumbnailer.image.delete.complete"
```
Сообщение отправляемые в шину для удаления изображения должны иметь следующий формат, пример:
```
{
    "subject": "Userstory\\Authentication\\RegisterController",
    "event": "thumbnailer.image.delete.complete",
    "version": "1.0.0",
    "timestamp": "2022-01-24T14:03:12UTC",
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "payload": {
        "base64Image": "/9j/4AAQSkZJRgABAQAAAQABAAD",
        "imageName": "image_name.png",
        "groupCode": "product"
    }
}
```

"uuid" на PHP можно получить с помощью пакета https://github.com/ramsey/uuid:

```
Uuid::uuid4()->toString()
```

Подробнее о ключах: https://www.notion.so/userstoryteam/0a806e5d94544eba954d762b791d860a

## Получение URL изображения
<a name="твоё_название"></a>

Получить адрес изображение можно на основе его базового имени fileName, согласно некоторому алгоритму, псевдокод:

```
md5FileName = md5(fileName)
firstPartSubDrName = получитьСтрокуИзПервыхДвухСимволов(md5FileName)
secondPartSubDrName = получитьСтрокуИзТрёхСимволовНачинаяСТретьего(md5FileName)
imageName = имяЗапрашиваемогоИзображенияТоЕстьВключаяРасширение

/images/code/firstPartSubDrName/secondPartSubDrName/imageName;
```

Пример GET запроса на получение миниатюры изображения с именем test.jpg:

```
http://10.222.33.2/images/w144/09/8f6/test.jpg
```

## Управление данными (CRUD) в базе данных. 

Сервис имеет консольный интерфейс для crud:

#### Управление данными в таблице transformation (update работает по code)
```
php artisan command:show
php artisan command:create --code= --group_code= --title= --description=
php artisan command:update --code= --group_code= --title= --description=
php artisan command:delete --code=
```

#### Управление данными в таблице transformation_step (update-step работает по id)
```
php artisan command:show-steps
php artisan command:create-step --code= --sorting= --method= --params=key1:val1 --params=key2:val2
php artisan command:update-step --id= --code= --sorting= --method= --params=key1:val1 --params=key2:val2
php artisan command:delete-step --id=
```

#### Обновление (через truncate) данных в таблицах transformation и transformation_step данными

Обновить таблицы можно с помощью файла yml, указанным в конфигурации filesystems. 
По умолчанию при запуске команды приложение будет искать файл с данными по пути storage/app/seed/transformations.yml 
```
php artisan db:seed --class=TransformationDumpSeeder
```

Пример файла storage/app/seed/transformations.example.yml:
```
product-group:

  product-preview:
    title: Картинка анонса (используется в листинге)
    description: Применяются шаги трансформации, изменение по ширине до 305 пикселей, применяется сжатие с качеством до 90.
    steps:
      - {
        sorting: 1,
        method: Resize,
        params: { width: 305, height: 0 }
      }
      - {
        sorting: 2,
        method: Optimize,
        params: { quality: 90 }
      }

  product-detail:
    title: Детальная картинка (используется в корзине, заказах)
    description: Применяются шаги трансформации, изменение по ширине до 164 пикселей.
    steps:
      - {
        sorting: 1,
        method: Resize,
        params: { width: 164, height: 0 }
      }

  product-add-3200:
    title: Дополнительные изображения (используется в КТ)
    description: Применяются шаги трансформации, изменение по ширине до 3200 пикселей, применяется сжатие с качеством до 90.
    steps:
      - {
        sorting: 1,
        method: Resize,
        params: { width: 3200, height: 0 }
      }
      - {
        sorting: 2,
        method: Optimize,
        params: { quality: 90 }
      }

  product-add-506:
    title: Дополнительные изображения (используется в КТ)
    description: Применяются шаги трансформации, изменение по ширине до 506 пикселей, применяется сжатие с качеством до 90.
    steps:
      - {
        sorting: 1,
        method: Resize,
        params: { width: 506, height: 0 }
      }
      - {
        sorting: 2,
        method: Optimize,
        params: { quality: 90 }
      }

  product-add-90:
    title: Дополнительные изображения (используется в КТ)
    description: Применяются шаги трансформации, изменение по ширине до 90 пикселей.
    steps:
      - {
        sorting: 1,
        method: Resize,
        params: { width: 90, height: 0 }
      }

```

Внимание! Команда удаляет все текущие данные из таблиц transformation и transformation_step перед обновлением.


### Отладка трансформации изображений через запуск теста

Запустить тест можно командой:
```
export GROUP_CODE=breakpoint && php artisan test --filter QueueMessageProducerTest
```

При этом откройте в консоли просмотр логов, чтобы видеть, что происходит:

```
tail -f storage/logs/laravel.log
```
