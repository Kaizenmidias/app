<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Support\TestingTargetGuard;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (getenv('MYSQL_TEST_ENABLED') === 'true') TestingTargetGuard::validateEnvironment();
        parent::setUp();
    }
}
