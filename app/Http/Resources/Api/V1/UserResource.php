<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'profile' => $this->whenLoaded('profile', fn () => [
                'address' => $this->profile?->address,
                'commune' => $this->profile?->commune,
                'city' => $this->profile?->city,
                'country' => $this->profile?->country,
            ]),
        ];
    }
}
