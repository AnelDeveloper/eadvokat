<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SSR is exercised manually in the browser; tests assert on Inertia page data.
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
    }
}
