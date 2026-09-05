@php
    $fee = $fee ?? null;
    $idPrefix = $idPrefix ?? '';
    $showErrors = $showErrors ?? false;
    $communeValue = $showErrors ? old('commune_id', $fee?->commune_id) : $fee?->commune_id;
    $cityValue = $showErrors ? old('city_id', $fee?->city_id) : $fee?->city_id;
    $zoneValue = $showErrors ? old('zone_id', $fee?->zone_id) : $fee?->zone_id;
    $methodValue = $showErrors ? old('delivery_method', $fee?->delivery_method?->value ?? 'standard') : ($fee?->delivery_method?->value ?? 'standard');
    $feeValue = $showErrors ? old('fee', $fee?->fee) : $fee?->fee;
    $minOrderValue = $showErrors ? old('min_order_amount', $fee?->min_order_amount ?? 0) : ($fee?->min_order_amount ?? 0);
    $freeAboveValue = $showErrors ? old('free_above_amount', $fee?->free_above_amount) : $fee?->free_above_amount;
    $statusValue = $showErrors ? old('status', $fee?->status?->value ?? 'active') : ($fee?->status?->value ?? 'active');
@endphp

<div class="space-y-4">
    <div>
        <label for="{{ $idPrefix }}commune_id" class="admin-label">Commune</label>
        <select id="{{ $idPrefix }}commune_id" name="commune_id" class="admin-field">
            <option value="">Aucune</option>
            @foreach ($communes as $commune)
                <option value="{{ $commune->id }}" @selected((string) $communeValue === (string) $commune->id)>{{ $commune->name }}</option>
            @endforeach
        </select>
        @if ($showErrors)
            @error('commune_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}city_id" class="admin-label">Ville</label>
            <select id="{{ $idPrefix }}city_id" name="city_id" class="admin-field">
                <option value="">Aucune</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) $cityValue === (string) $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="{{ $idPrefix }}zone_id" class="admin-label">Zone</label>
            <select id="{{ $idPrefix }}zone_id" name="zone_id" class="admin-field">
                <option value="">Aucune</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected((string) $zoneValue === (string) $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}delivery_method" class="admin-label">Mode</label>
            <select id="{{ $idPrefix }}delivery_method" name="delivery_method" class="admin-field">
                @foreach (\App\Enums\DeliveryMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected($methodValue === $method->value)>{{ $method->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="{{ $idPrefix }}fee" class="admin-label">Frais (FCFA)</label>
            <input id="{{ $idPrefix }}fee" name="fee" type="number" min="0" required value="{{ $feeValue }}" class="admin-field">
            @if ($showErrors)
                @error('fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}min_order_amount" class="admin-label">Commande min.</label>
            <input id="{{ $idPrefix }}min_order_amount" name="min_order_amount" type="number" min="0" value="{{ $minOrderValue }}" class="admin-field">
        </div>
        <div>
            <label for="{{ $idPrefix }}free_above_amount" class="admin-label">Gratuit dès</label>
            <input id="{{ $idPrefix }}free_above_amount" name="free_above_amount" type="number" min="0" value="{{ $freeAboveValue }}" class="admin-field">
        </div>
    </div>
    <div>
        <label for="{{ $idPrefix }}status" class="admin-label">Statut</label>
        <select id="{{ $idPrefix }}status" name="status" class="admin-field">
            @foreach (\App\Enums\Status::cases() as $status)
                <option value="{{ $status->value }}" @selected($statusValue === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        <button type="button" class="text-sm underline" @click="modal = null">Annuler</button>
    </div>
</div>
