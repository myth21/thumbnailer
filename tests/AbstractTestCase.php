<?php

declare(strict_types = 1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Отвечает за предоставления экземпляра приложения для тестов.
 */
abstract class AbstractTestCase extends BaseTestCase
{
    use CreatesApplicationTrait;
}
