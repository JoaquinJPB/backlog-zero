<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // PHP tests shouldn't depend on a fresh frontend build. Page existence
        // is still enforced by inertia.testing.ensure_pages_exist.
        $this->withoutVite();
    }
}
