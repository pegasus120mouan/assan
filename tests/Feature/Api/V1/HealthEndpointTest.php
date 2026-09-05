<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_the_api_health_endpoint_returns_shop_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'name' => 'ASSAN',
                'status' => 'ok',
                'version' => 'v1',
                'locale' => 'fr',
                'timezone' => 'Africa/Abidjan',
                'currency' => 'XOF',
            ]);
    }
}
