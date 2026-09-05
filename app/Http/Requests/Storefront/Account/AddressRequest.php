<?php

namespace App\Http\Requests\Storefront\Account;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone'),
            'city' => $this->input('city') ?: 'Abidjan',
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
