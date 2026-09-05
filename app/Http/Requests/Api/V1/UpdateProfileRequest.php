<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone')]);
        }

        if (is_string($this->email)) {
            $this->merge(['email' => Str::lower(trim($this->email))]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['sometimes', 'required', 'string', 'min:8', 'max:20', 'unique:users,phone,'.$userId],
            'address' => ['nullable', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
        ];
    }
}
