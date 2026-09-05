<?php

namespace App\Http\Requests\Storefront\Account;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone'),
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['required', 'string', 'min:8', 'max:20', 'unique:users,phone,'.$userId],
            'address' => ['nullable', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
        ];
    }
}
