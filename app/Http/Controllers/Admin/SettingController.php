<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(SettingsService $settings): View
    {
        $this->authorize('accessAdmin', User::class);

        return view('admin.settings.edit', [
            'values' => [
                'shop.name' => $settings->get('shop.name', config('shop.name')),
                'shop.tagline' => $settings->get('shop.tagline', config('shop.tagline')),
                'shop.email' => $settings->get('shop.email', config('shop.contact.email')),
                'shop.phone' => $settings->get('shop.phone', config('shop.contact.phone')),
                'shop.sav' => $settings->get('shop.sav', config('shop.contact.sav')),
                'shop.whatsapp' => $settings->get('shop.whatsapp', config('shop.contact.whatsapp')),
                'shop.address' => $settings->get('shop.address', config('shop.contact.address')),
            ],
        ]);
    }

    public function update(SettingRequest $request, SettingsService $settings, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('accessAdmin', User::class);

        $payload = $request->settings();
        $settings->putMany($payload);
        $audit->record('Paramètres mis à jour', 'settings', null, null, $payload);

        return back()->with('status', 'Paramètres enregistrés.');
    }
}
