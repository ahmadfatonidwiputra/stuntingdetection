<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered_before_initial_superadmin_exists(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_first_registered_user_becomes_superadmin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'is_superadmin' => true,
        ]);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_screen_redirects_to_login_after_superadmin_exists(): void
    {
        User::factory()->superadmin()->create();

        $response = $this->get('/register');

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
    }

    public function test_new_users_can_not_register_after_superadmin_exists(): void
    {
        User::factory()->superadmin()->create();

        $response = $this->from(route('register'))->post('/register', [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
