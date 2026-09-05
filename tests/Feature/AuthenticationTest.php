<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_are_displayed(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Connexion', false);
        $this->get(route('register'))->assertOk()->assertSee('Créer un compte', false);
    }

    public function test_customer_can_register_and_cannot_spoof_an_admin_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Awa Kouassi',
            'email' => 'awa@example.com',
            'phone' => '07 01 02 03 04',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'awa@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertTrue($user->isActive());
        $this->assertSame('0701020304', $user->phone);
        $this->assertTrue($user->profile()->exists());
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_customer_can_login_with_email_and_access_account(): void
    {
        $user = User::factory()->customer()->create([
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('account.dashboard'))->assertOk()->assertSee('Mon compte', false);
    }

    public function test_customer_can_login_with_phone(): void
    {
        $user = User::factory()->customer()->create([
            'phone' => '0700000099',
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'login' => '07 00 00 00 99',
            'password' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'login' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord', false);
    }

    public function test_manager_can_access_the_admin_dashboard(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customer_cannot_access_the_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
