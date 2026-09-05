<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_manage_staff_users(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_manager_cannot_manage_staff_users(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.users.index').'"', false);

        $this->actingAs($manager)
            ->post(route('admin.users.store'), $this->staffPayload())
            ->assertForbidden();
    }

    public function test_admin_can_list_staff_users_but_not_customers(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Administrateur OVL',
            'email' => 'admin@example.com',
        ]);
        User::factory()->manager()->create([
            'name' => 'Gestionnaire Boutique',
            'email' => 'manager@example.com',
        ]);
        User::factory()->customer()->create([
            'name' => 'Awa Kouassi',
            'email' => 'awa@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Administrateur OVL', false)
            ->assertSee('Gestionnaire Boutique', false)
            ->assertSee('Nouvel utilisateur', false)
            ->assertSee('Modifier', false)
            ->assertSee(route('admin.users.store'), false)
            ->assertSee('_edit_modal', false)
            ->assertDontSee('awa@example.com', false);
    }

    public function test_admin_can_create_a_manager(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->staffPayload([
                'name' => 'Koffi Yao',
                'email' => 'koffi@example.com',
                'phone' => '07 11 22 33 44',
                'role' => UserRole::Manager->value,
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Utilisateur créé.');

        $created = User::query()->where('email', 'koffi@example.com')->first();

        $this->assertNotNull($created);
        $this->assertSame('Koffi Yao', $created->name);
        $this->assertSame('0711223344', $created->phone);
        $this->assertSame(UserRole::Manager, $created->role);
        $this->assertTrue($created->isActive());
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_admin_can_create_another_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->staffPayload([
                'name' => 'Second Admin',
                'email' => 'second.admin@example.com',
                'role' => UserRole::Admin->value,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(User::query()->where('email', 'second.admin@example.com')->first()?->isAdmin());
    }

    public function test_admin_cannot_create_a_customer_from_staff_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), $this->staffPayload([
                'role' => UserRole::Customer->value,
                '_create_modal' => '1',
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertRedirect(route('admin.users.index', ['create' => 1]));

        $this->assertSame(0, User::query()->where('role', UserRole::Customer)->count());
    }

    public function test_admin_can_update_a_manager_and_cannot_edit_a_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->manager()->create([
            'name' => 'Ancien nom',
            'email' => 'manager@example.com',
        ]);
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->put(route('admin.users.update', $manager), $this->staffPayload([
                'name' => 'Nouveau nom',
                'email' => 'manager@example.com',
                'phone' => $manager->phone,
                'role' => UserRole::Manager->value,
                'password' => '',
                'password_confirmation' => '',
                '_edit_modal' => $manager->id,
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Utilisateur mis à jour.');

        $this->assertSame('Nouveau nom', $manager->fresh()->name);
        $this->assertTrue(Hash::check('password', $manager->fresh()->password));

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $manager))
            ->assertRedirect(route('admin.users.index', ['edit' => $manager->id]));

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $customer))
            ->assertNotFound();
    }

    public function test_admin_cannot_delete_self_or_the_last_active_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $manager = User::factory()->manager()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->put(route('admin.users.update', $admin), $this->staffPayload([
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role' => UserRole::Manager->value,
                'password' => '',
                'password_confirmation' => '',
                '_edit_modal' => $admin->id,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $manager))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Utilisateur supprimé.');

        $this->assertDatabaseMissing('users', ['id' => $manager->id]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function staffPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Koffi Yao',
            'email' => 'koffi@example.com',
            'phone' => '0711223344',
            'role' => UserRole::Manager->value,
            'status' => Status::Active->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }
}
