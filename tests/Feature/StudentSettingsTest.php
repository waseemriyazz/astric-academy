<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk();
    }

    public function test_settings_page_contains_required_elements(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('Settings')
            ->assertSee('Appearance')
            ->assertSee('Dark Mode')
            ->assertSee('Change Password')
            ->assertSee('Account Information')
            ->assertSee('Danger Zone')
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_settings_page_has_theme_toggle(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('theme-toggle')
            ->assertSee('Dark Mode');
    }

    public function test_settings_page_has_password_form(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('Current Password')
            ->assertSee('New Password')
            ->assertSee('Confirm Password')
            ->assertSee('Update Password');
    }

    public function test_settings_page_has_account_information(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('Full Name')
            ->assertSee('Email Address')
            ->assertSee('Account Type')
            ->assertSee('Member Since')
            ->assertSee('Edit Profile');
    }

    public function test_settings_page_has_delete_account_option(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('Danger Zone')
            ->assertSee('Delete Account');
    }

    public function test_settings_page_sidebar_link_is_active(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/student/settings');

        $response->assertOk()
            ->assertSee('bg-indigo-50')
            ->assertSee('text-indigo-700');
    }
}