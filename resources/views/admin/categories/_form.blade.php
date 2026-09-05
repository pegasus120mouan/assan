<div class="space-y-4">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nom</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="mb-1 block text-sm font-medium">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $category?->slug) }}" placeholder="Généré automatiquement"
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="parent_id" class="mb-1 block text-sm font-medium">Catégorie parente</label>
        <select id="parent_id" name="parent_id" class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
            <option value="">Aucune</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category?->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">Description</label>
        <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">{{ old('description', $category?->description) }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="status" class="mb-1 block text-sm font-medium">Statut</label>
            <select id="status" name="status" class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $category?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort_order" class="mb-1 block text-sm font-medium">Ordre</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category?->sort_order ?? 0) }}"
                class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        </div>
    </div>

    <div>
        <p class="mb-2 text-sm font-medium">Icône <span class="text-red-600">*</span></p>
        <p class="mb-3 text-xs text-night-800/50">Choisissez l’icône affichée dans le menu catégories de la boutique.</p>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-5">
            @foreach (\App\Support\CategoryIcons::catalog() as $key => $meta)
                <label class="cursor-pointer">
                    <input type="radio" name="icon" value="{{ $key }}" class="peer sr-only" @checked((string) old('icon', $category?->icon) === $key) required>
                    <span class="flex h-full flex-col items-center gap-1.5 rounded-xl border border-night-900/15 px-2 py-3 text-center text-[11px] leading-tight text-night-800/70 peer-checked:border-shop-orange peer-checked:bg-orange-50 peer-checked:text-shop-orange peer-focus-visible:ring-2 peer-focus-visible:ring-shop-orange">
                        <x-category-icon :icon="$key" class="h-6 w-6 text-shop-orange" />
                        {{ $meta['label'] }}
                    </span>
                </label>
            @endforeach
        </div>
        @error('icon') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="image" class="mb-1 block text-sm font-medium">Image</label>
        @if ($category?->imageUrl())
            <img src="{{ $category->imageUrl() }}" alt="" class="mb-2 h-16 w-16 rounded-lg object-cover">
        @endif
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
        <p class="mt-1 text-xs text-night-800/50">JPEG, PNG ou WebP — 2 Mo max.</p>
        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        <a href="{{ route('admin.categories.index') }}" class="text-sm underline">Annuler</a>
    </div>
</div>
