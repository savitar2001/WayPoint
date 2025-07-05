<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    /**
     * Runs before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }
}
