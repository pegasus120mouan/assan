<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'Produit créé',
            'module' => 'products',
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => ['name' => 'Powerbank'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ];
    }
}
