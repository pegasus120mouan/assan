<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'shop.name' => ['required', 'string', 'max:120'],
            'shop.tagline' => ['nullable', 'string', 'max:255'],
            'shop.email' => ['nullable', 'email', 'max:255'],
            'shop.phone' => ['nullable', 'string', 'max:30'],
            'shop.sav' => ['nullable', 'string', 'max:30'],
            'shop.whatsapp' => ['nullable', 'string', 'max:30'],
            'shop.address' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function settings(): array
    {
        $shop = $this->validated('shop');

        return [
            'shop.name' => $shop['name'],
            'shop.tagline' => $shop['tagline'] ?? null,
            'shop.email' => $shop['email'] ?? null,
            'shop.phone' => $shop['phone'] ?? null,
            'shop.sav' => $shop['sav'] ?? null,
            'shop.whatsapp' => $shop['whatsapp'] ?? null,
            'shop.address' => $shop['address'] ?? null,
        ];
    }
}
