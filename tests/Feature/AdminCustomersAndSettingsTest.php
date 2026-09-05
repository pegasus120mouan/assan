<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomersAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_admin_clients_or_settings(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.customers.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_customers_and_cannot_open_a_staff_user_as_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create([
            'name' => 'Awa Kouassi',
            'email' => 'awa@example.com',
        ]);
        $manager = User::factory()->manager()->create([
            'name' => 'Staff Manager',
            'email' => 'manager@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Awa Kouassi', false)
            ->assertSee('awa@example.com', false)
            ->assertDontSee('manager@example.com', false);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Awa Kouassi', false);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $manager))
            ->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.customers.update', $manager), [
                'status' => Status::Inactive->value,
            ])
            ->assertNotFound();
    }

    public function test_admin_can_deactivate_a_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->from(route('admin.customers.show', $customer))
            ->patch(route('admin.customers.update', $customer), [
                'status' => Status::Inactive->value,
            ])
            ->assertRedirect(route('admin.customers.show', $customer))
            ->assertSessionHas('status', 'Client mis à jour.');

        $this->assertSame(Status::Inactive, $customer->fresh()->status);
    }

    public function test_admin_can_update_shop_settings_and_they_overlay_the_storefront(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->put(route('admin.settings.update'), [
                'shop' => [
                    'name' => 'OVL Demo',
                    'tagline' => 'Boutique de test',
                    'email' => 'hello@ovl.test',
                    'phone' => '07 11 22 33 44',
                    'sav' => '07 11 22 33 45',
                    'whatsapp' => '2250700000000',
                    'address' => 'Cocody, Abidjan',
                ],
            ])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('status', 'Paramètres enregistrés.');

        $this->assertSame('OVL Demo', Setting::query()->where('key', 'shop.name')->value('value'));
        $this->assertSame('OVL Demo', shop_name());
        $this->assertSame('hello@ovl.test', config('shop.contact.email'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('OVL Demo', false)
            ->assertSee('07 11 22 33 44', false);
    }
}
