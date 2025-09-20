<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    protected $seed = true;
    
    protected function setUp(): void
    {
        parent::setUp();
        // Configuración adicional si es necesaria

        $this->artisan('db:seed');
    }
}
