<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountExtrasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_toggling_wishlist(): void
    {
        $product = Product::factory()->create();

        $this->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_toggle_a_product_in_the_wishlist(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create(['name' => 'Powerbank 20 000 mAh']);

        $this->actingAs($user)
            ->from(route('catalog.index'))
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('catalog.index'))
            ->assertSessionHas('status', 'Ajouté aux favoris.');

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)
            ->get(route('account.wishlist'))
            ->assertOk()
            ->assertSee('Powerbank 20 000 mAh', false)
            ->assertSee('Retirer des favoris', false);

        $this->actingAs($user)
            ->from(route('account.wishlist'))
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('account.wishlist'))
            ->assertSessionHas('status', 'Retiré des favoris.');

        $this->assertSame(0, Wishlist::query()->count());
    }

    public function test_customer_can_update_profile(): void
    {
        $user = User::factory()->customer()->create([
            'name' => 'Awa Kouassi',
            'email' => 'awa@example.com',
            'phone' => '0700000001',
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'name' => 'Awa K.',
                'email' => 'awa.k@example.com',
                'phone' => '07 00 00 00 02',
                'address' => 'Riviera 2',
                'commune' => 'Cocody',
                'city' => 'Abidjan',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Profil mis à jour.');

        $user->refresh();

        $this->assertSame('Awa K.', $user->name);
        $this->assertSame('awa.k@example.com', $user->email);
        $this->assertSame('0700000002', $user->phone);
        $this->assertSame('Riviera 2', $user->profile?->address);
        $this->assertSame('Cocody', $user->profile?->commune);
    }

    public function test_customer_can_manage_addresses_and_cannot_edit_another_users(): void
    {
        $user = User::factory()->customer()->create(['name' => 'Awa Kouassi', 'phone' => '0700000010']);
        $other = User::factory()->customer()->create();
        $foreign = Address::factory()->for($other)->create(['label' => 'Bureau']);

        $this->actingAs($user)
            ->post(route('account.addresses.store'), [
                'label' => 'Maison',
                'recipient_name' => 'Awa Kouassi',
                'phone' => '07 00 00 00 10',
                'address' => 'Angré 8e tranche',
                'commune' => 'Cocody',
                'city' => 'Abidjan',
                'is_default' => '1',
            ])
            ->assertRedirect(route('account.addresses.index'))
            ->assertSessionHas('status', 'Adresse enregistrée.');

        $address = $user->addresses()->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
        $this->assertSame('0700000010', $address->phone);

        $this->actingAs($user)
            ->get(route('account.addresses.edit', $foreign))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('account.addresses.update', $foreign), [
                'label' => 'Hack',
                'recipient_name' => 'Hack',
                'phone' => '0700000099',
                'address' => 'Somewhere',
                'city' => 'Abidjan',
            ])
            ->assertForbidden();
    }

    public function test_customer_can_open_own_invoice_but_not_another_users(): void
    {
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $product = Product::factory()->create(['name' => 'Powerbank 20 000 mAh']);
        $order = Order::factory()->for($owner)->create([
            'customer_name' => 'Awa Kouassi',
        ]);
        OrderItem::factory()->for($order)->for($product)->create();

        $this->actingAs($owner)
            ->get(route('account.orders.invoice', $order))
            ->assertOk()
            ->assertSee('Facture', false)
            ->assertSee($order->order_number, false)
            ->assertSee('Powerbank 20 000 mAh', false);

        $this->actingAs($stranger)
            ->get(route('account.orders.invoice', $order))
            ->assertForbidden();
    }

    public function test_customer_sees_whatsapp_order_button_on_account_and_order(): void
    {
        config(['shop.contact.whatsapp' => '2250700000000']);

        $user = User::factory()->customer()->create([
            'name' => 'Awa Kouassi',
            'phone' => '0700000001',
        ]);
        $order = Order::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('Commande WhatsApp', false)
            ->assertSee('Commander sur WhatsApp', false)
            ->assertSee('https://wa.me/2250700000000', false)
            ->assertSee(rawurlencode('Bonjour, je suis Awa Kouassi'), false);

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Commander / suivre sur WhatsApp', false)
            ->assertSee('https://wa.me/2250700000000', false)
            ->assertSee(rawurlencode($order->order_number), false);
    }
}
