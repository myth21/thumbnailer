<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Repository\TransformationRepositoryInterface;
use App\Service\FileKeeperInterface;
use App\Service\ImageIdFileKeeper;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\SkippedTestError;
use Tests\AbstractTestCase;

use function app;
use function config;
use function get_headers;
use function stream_context_create;
use function substr;

/**
 * Тестирует отправку сообщений через http.
 */
class ImageIdTest extends AbstractTestCase
{
    /**
     * Объект хранения файлов.
     *
     * @var FileKeeperInterface
     */
    protected FileKeeperInterface $fileKeeper;

    /**
     * Объект хранилища данных.
     *
     * @var TransformationRepositoryInterface
     */
    protected TransformationRepositoryInterface $transformationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileKeeper = app(config('app.fileKeeper'));
        $this->transformationRepository = app(TransformationRepositoryInterface::class);
    }

    /**
     * Отправить изображение с идентификатором на обработку и проверить его создание и доступность по url.
     * Тест предназначен только для ImageIdFileKeeper.
     */
    public function testSendRequest(): void
    {
        if (! ($this->fileKeeper instanceof ImageIdFileKeeper)) {
            $message = 'WARNING: ';
            $message .= 'Test ' . __METHOD__ . ' is skipped, because one is executed for "ImageIdFileKeeper" param only. ';
            $message .= 'This case is required for success testing.';

            $this->expectException(SkippedTestError::class);
            Assert::markTestSkipped($message);
        }

        $params = config('database.connections.external');

        // Create temp table with codes.
        $connection = $this->createTempTable($params);

        // Prepare data for writing image.
        // Sub dir example iblock/191.
        $subDirNameInDatabase = '';
        $imageNameInDatabase = 'test.jpg';
        $imageIdInDatabase = '12';

        // Save image data in database.
        $connection->insert('INSERT INTO ' . $params['target_table'] . ' ('
            . $params['id_column'] . ','
            . $params['sub_dir_column'] . ','
            . $params['image_column'] .
            ') VALUES (\'' . $imageIdInDatabase . '\', \'' . $subDirNameInDatabase . '\', \'' . $imageNameInDatabase . '\')');

        // Do request for processing images by ids.
        $codes = $this->transformationRepository->getCodes();

        foreach ($codes as $code) {
            // Do request for creating an thumbnails.
            $creatingUrl = 'http://10.222.33.2/?img=/images/' . $code . '/' . $imageIdInDatabase . '.jpg';
            $response = $this->get($creatingUrl);
            $response->assertStatus(200);

            // Get sub dir name, for example w1200/12/345.
            $subDirName = $this->fileKeeper->getPublicSubDirName($imageIdInDatabase, $code);

            // Check access to image via public url directly.
            $checkingUrl = 'http://10.222.33.2/images/' . $subDirName . '/' . $imageIdInDatabase . '.jpg';
            $headers = get_headers($checkingUrl, true, stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                ],
            ]));
            $codeStatus = (int) substr($headers[0], 9, 3);
            self::assertEquals(200, $codeStatus);
        }
    }

    /**
     * Создать временную таблицу.
     *
     * @param array $params Параметры для создания временной таблицы.
     *
     * @return ConnectionInterface
     */
    private function createTempTable(array $params): ConnectionInterface
    {
        $connectionName = config('database.connections.external.connection');
        $connection = DB::connection($connectionName);
        $connection->statement('DROP TABLE IF EXISTS ' . $params['target_table']);
        $createTableSql = 'CREATE TEMP TABLE ' . $params['target_table'] . ' ('
            . $params['id_column'] . ' integer not null,'
            . $params['sub_dir_column'] . ' text not null,'
            . $params['image_column'] . ' text not null
        )';
        $connection->statement($createTableSql);

        return $connection;
    }
}
