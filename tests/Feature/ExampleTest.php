<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_tamu_diarahkan_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route("admin.login")); // tamu diarahkan ke login
    }
}
