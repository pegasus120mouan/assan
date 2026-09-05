@php $coupon = $coupon ?? null; @endphp
<div class="space-y-4">
    <div>
        <label class="admin-label">Code</label>
        <input name="code" required value="{{ old('code', $coupon?->code) }}" class="admin-field uppercase">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Type</label>
            <select name="type" class="admin-field">
                @foreach (\App\Enums\CouponType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $coupon?->type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="admin-label">Valeur</label>
            <input name="value" type="number" min="1" required value="{{ old('value', $coupon?->value) }}" class="admin-field">
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Minimum commande</label>
            <input name="minimum_order_amount" type="number" min="0" value="{{ old('minimum_order_amount', $coupon?->minimum_order_amount ?? 0) }}" class="admin-field">
        </div>
        <div>
            <label class="admin-label">Réduction max</label>
            <input name="maximum_discount" type="number" min="0" value="{{ old('maximum_discount', $coupon?->maximum_discount) }}" class="admin-field">
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Début</label>
            <input name="starts_at" type="datetime-local" value="{{ old('starts_at', $coupon?->starts_at?->format('Y-m-d\TH:i')) }}" class="admin-field">
        </div>
        <div>
            <label class="admin-label">Expiration</label>
            <input name="expires_at" type="datetime-local" value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\TH:i')) }}" class="admin-field">
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Limite globale</label>
            <input name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $coupon?->usage_limit) }}" class="admin-field">
        </div>
        <div>
            <label class="admin-label">Par client</label>
            <input name="usage_per_customer" type="number" min="1" value="{{ old('usage_per_customer', $coupon?->usage_per_customer) }}" class="admin-field">
        </div>
    </div>
    <div>
        <label class="admin-label">Statut</label>
        <select name="status" class="admin-field">
            @foreach (\App\Enums\Status::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $coupon?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
</div>
