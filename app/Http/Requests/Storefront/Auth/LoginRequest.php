<?php

namespace App\Http\Requests\Storefront\Auth;

use App\Enums\Status;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login' => 'e-mail ou téléphone',
            'password' => 'mot de passe',
        ];
    }

    public function authenticate(): void
    {
        $user = $this->userFromCredentials();

        Auth::login($user, $this->boolean('remember'));
    }

    public function userFromCredentials(): User
    {
        $this->ensureIsNotRateLimited();

        $user = $this->findUser();

        if (! $user || ! Hash::check((string) $this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        if ($user->status !== Status::Active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.inactive'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    public function findUser(): ?User
    {
        $login = trim((string) $this->input('login'));

        if (str_contains($login, '@')) {
            return User::query()->where('email', $login)->first();
        }

        $phone = Phone::normalize($login);

        if ($phone === null) {
            return null;
        }

        return User::query()->where('phone', $phone)->first();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => __('auth.throttle', ['seconds' => $seconds]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('login')).'|'.$this->ip());
    }
}
