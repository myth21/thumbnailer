<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\MessageTrait;
use Tests\Support\FunctionalTester;

/**
 * Отвечает за тестирование нахождения записей в базе данных.
 */
class DatabaseCest
{
    use MessageTrait;

    /**
     * Тестирует наличие некоторых записей в таблице базы данных.
     *
     * @param FunctionalTester $I
     */
    public function someTableRowsExistenceTest(FunctionalTester $I): void
    {
        $I->seeInDatabase('transformation', [
            'group_code' => $this->getPayloadData('groupCode'),
        ]);

        $I->seeInDatabase('transformation_step', [
            'method' => $I->getModuleConfig('Db', 'aTestedMethod'),
        ]);
    }
}
