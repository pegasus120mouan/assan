<?php

namespace App\Http\Requests\Admin;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone'),
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
            'password' => $this->input('password') === '' ? null : $this->input('password'),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'min:8', 'max:20', Rule::unique('users', 'phone')->ignore($userId)],
            'role' => ['required', Rule::enum(UserRole::class)->only(UserRole::staffCases())],
            'status' => ['required', Rule::enum(Status::class)],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse e-mail',
            'phone' => 'téléphone',
            'role' => 'rôle',
            'status' => 'statut',
            'password' => 'mot de passe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function staffData(): array
    {
        $data = [
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'phone' => $this->validated('phone'),
            'role' => UserRole::from($this->validated('role')),
            'status' => Status::from($this->validated('status')),
        ];

        if (filled($this->validated('password'))) {
            $data['password'] = $this->validated('password');
        }

        return $data;
    }
}
