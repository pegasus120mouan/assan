<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethod;
use App\Enums\Status;
use App\Models\DeliveryFee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeliveryFeesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_icon_actions_and_edit_modal_on_the_list(): void
    {
        $admin = User::factory()->admin()->create();
        $fee = DeliveryFee::factory()->create(['fee' => 2000]);

        $this->actingAs($admin)
            ->get(route('admin.delivery-fees.index'))
            ->assertOk()
            ->assertSee('Nouveau tarif', false)
            ->assertSee('aria-label="Modifier"', false)
            ->assertSee('aria-label="Supprimer"', false)
            ->assertDontSee('>Modifier</a>', false)
            ->assertDontSee('>Supprimer</button>', false)
            ->assertSee(route('admin.delivery-fees.update', $fee), false)
            ->assertSee('_edit_modal', false);
    }

    public function test_edit_and_create_pages_open_the_list_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $fee = DeliveryFee::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.delivery-fees.edit', $fee))
            ->assertRedirect(route('admin.delivery-fees.index', ['edit' => $fee->id]));

        $this->actingAs($admin)
            ->get(route('admin.delivery-fees.create'))
            ->assertRedirect(route('admin.delivery-fees.index', ['create' => 1]));
    }

    public function test_admin_can_update_a_fee_from_the_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $fee = DeliveryFee::factory()->create(['fee' => 2000]);

        $this->actingAs($admin)
            ->from(route('admin.delivery-fees.index'))
            ->put(route('admin.delivery-fees.update', $fee), [
                'commune_id' => $fee->commune_id,
                'delivery_method' => DeliveryMethod::Standard->value,
                'fee' => 1800,
                'min_order_amount' => 0,
                'free_above_amount' => 50000,
                'status' => Status::Active->value,
                '_edit_modal' => $fee->id,
            ])
            ->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHas('status', 'Tarif de livraison mis à jour.');

        $this->assertSame(1800, $fee->fresh()->fee);
    }
}
