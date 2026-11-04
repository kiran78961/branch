<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_renders_at_login_url(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Sign in to Branch')
            ->assertSee('Work email')
            ->assertSee('Password');
    }
}
