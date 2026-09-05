<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const CACHE_KEY = 'shop.settings.values';

    /**
     * @return array<string, string|null>
     */
    public function values(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function (): array {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values()[$key] ?? $default;
    }

    /**
     * @param  array<string, mixed>  $items
     */
    public function putMany(array $items, string $group = 'general'): void
    {
        foreach ($items as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value === null ? null : (string) $value,
                    'type' => 'string',
                    'group' => $group,
                ]
            );
        }

        Cache::forget(self::CACHE_KEY);
        $this->applyToConfig();
    }

    public function applyToConfig(): void
    {
        $values = $this->values();

        if (filled($values['shop.name'] ?? null)) {
            config(['shop.name' => $values['shop.name'], 'app.name' => $values['shop.name']]);
        }

        if (filled($values['shop.tagline'] ?? null)) {
            config(['shop.tagline' => $values['shop.tagline']]);
        }

        $contact = config('shop.contact', []);

        foreach (['email', 'phone', 'sav', 'whatsapp', 'address'] as $field) {
            $key = 'shop.'.$field;
            if (array_key_exists($key, $values) && $values[$key] !== null && $values[$key] !== '') {
                $contact[$field] = $values[$key];
            }
        }

        config(['shop.contact' => $contact]);
    }
}
