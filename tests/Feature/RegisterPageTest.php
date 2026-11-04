<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    public function test_register_page_renders_account_creation_fields(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Create your account')
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Work email')
            ->assertSee('Confirm password')
            ->assertSee('href="' . route('login') . '"', false);
    }
}
