<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationConfigurationTest extends TestCase
{
    public function test_the_application_is_configured_for_cote_divoire(): void
    {
        $this->assertSame('ASSAN', config('app.name'));
        $this->assertSame('Africa/Abidjan', config('app.timezone'));
        $this->assertSame('fr', config('app.locale'));
        $this->assertSame('XOF', config('shop.currency'));
        $this->assertSame('FCFA', config('shop.currency_label'));
        $this->assertSame('cash_on_delivery', config('payment.default'));
        $this->assertSame('internal', config('delivery.default'));
        $this->assertSame(2000, config('delivery.standard_fee'));
        $this->assertFalse(config('whatsapp.enabled'));
    }
}
