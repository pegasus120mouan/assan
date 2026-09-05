@php $promotion = $promotion ?? null; @endphp
<div class="space-y-4">
    <div>
        <label class="admin-label">Nom</label>
        <input name="name" required value="{{ old('name', $promotion?->name) }}" class="admin-field">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Type</label>
            <select name="type" class="admin-field">
                @foreach (\App\Enums\PromotionType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $promotion?->type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="admin-label">Remise</label>
            <select name="discount_type" class="admin-field">
                @foreach (\App\Enums\DiscountType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('discount_type', $promotion?->discount_type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="admin-label">Valeur</label>
        <input name="value" type="number" min="0" required value="{{ old('value', $promotion?->value) }}" class="admin-field">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Produit</label>
            <select name="product_id" class="admin-field">
                <option value="">—</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id', $promotion?->product_id) === (string) $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="admin-label">Catégorie</label>
            <select name="category_id" class="admin-field">
                <option value="">—</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $promotion?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Début</label>
            <input name="starts_at" type="datetime-local" value="{{ old('starts_at', $promotion?->starts_at?->format('Y-m-d\TH:i')) }}" class="admin-field">
        </div>
        <div>
            <label class="admin-label">Fin</label>
            <input name="expires_at" type="datetime-local" value="{{ old('expires_at', $promotion?->expires_at?->format('Y-m-d\TH:i')) }}" class="admin-field">
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="admin-label">Priorité</label>
            <input name="priority" type="number" min="0" value="{{ old('priority', $promotion?->priority ?? 0) }}" class="admin-field">
        </div>
        <div>
            <label class="admin-label">Statut</label>
            <select name="status" class="admin-field">
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $promotion?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
