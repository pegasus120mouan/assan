<div class="space-y-4">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nom</label>
        <input id="name" name="name" type="text" value="{{ old('name', $brand?->name) }}" required
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="mb-1 block text-sm font-medium">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $brand?->slug) }}" placeholder="Généré automatiquement"
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">Description</label>
        <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">{{ old('description', $brand?->description) }}</textarea>
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium">Statut</label>
        <select id="status" name="status" class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
            @foreach (\App\Enums\Status::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $brand?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="logo" class="mb-1 block text-sm font-medium">Logo</label>
        @if ($brand?->logoUrl())
            <img src="{{ $brand->logoUrl() }}" alt="" class="mb-2 h-16 w-16 rounded-lg object-cover">
        @endif
        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
        <p class="mt-1 text-xs text-night-800/50">JPEG, PNG ou WebP — 2 Mo max.</p>
        @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        <a href="{{ route('admin.brands.index') }}" class="text-sm underline">Annuler</a>
    </div>
</div>
