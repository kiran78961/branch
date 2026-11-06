<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_holiday_list_from_sidebar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('holidays.index'))
            ->assertOk()
            ->assertSee('href="'.route('holidays.index').'"', false)
            ->assertSee('Holidays')
            ->assertSee('India-wide holidays')
            ->assertSee('2026 holiday list')
            ->assertSee('26 Jan')
            ->assertSee('Republic Day')
            ->assertSee('Diwali (Deepavali)')
            ->assertSee('25 Dec')
            ->assertSee('Christmas Day')
            ->assertSee('https://www.indiapost.gov.in/holidays-list', false)
            ->assertSee('State and local holiday schedules may differ.');
    }

    public function test_guest_is_redirected_to_sign_in_from_holiday_list(): void
    {
        $this->get(route('holidays.index'))
            ->assertRedirect(route('login'));
    }
}
