@php
    $variantRows = old('variants');

    if ($variantRows === null) {
        $variantRows = ($product?->variants ?? collect())->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'stock_quantity' => $variant->stock_quantity,
            'status' => $variant->status->value,
            'options' => $variant->options ? json_encode($variant->options, JSON_UNESCAPED_UNICODE) : '',
        ])->values();
    }

    $shortMax = 2000;
@endphp

<div
    class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]"
    x-data="{
        variants: {{ \Illuminate\Support\Js::from($variantRows) }},
        shortDesc: {{ \Illuminate\Support\Js::from(old('short_description', $product?->short_description) ?? '') }},
        shortMax: {{ $shortMax }},
        addVariant() {
            this.variants.push({ id: null, name: '', sku: '', price: '', stock_quantity: 0, status: 'active', options: '' });
        },
        removeVariant(index) {
            this.variants.splice(index, 1);
        }
    }"
>
    <div class="space-y-5">
        <section class="admin-card">
            <h2 class="admin-card__title">Identification</h2>
            <div class="space-y-4">
                <div>
                    <label for="name" class="admin-label">Nom</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required maxlength="255"
                        class="admin-field @error('name') admin-field--error @enderror">
                    <p class="admin-help">Nom affiché sur la boutique (pas le SKU).</p>
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="slug" class="admin-label">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $product?->slug) }}" placeholder="Généré automatiquement" class="admin-field">
                    </div>
                    <div>
                        <label for="sku" class="admin-label">SKU</label>
                        <input id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" placeholder="Ex. CA-5540 — généré si vide"
                            class="admin-field @error('sku') admin-field--error @enderror">
                        <p class="admin-help">Identifiant court (64 caractères max). Laissez vide pour le générer automatiquement.</p>
                        @error('sku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-card__title">Contenu</h2>
            <div class="space-y-4">
                <div>
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <label for="short_description" class="admin-label mb-0">Résumé</label>
                        <span class="text-xs" :class="shortDesc.length > shortMax ? 'font-semibold text-red-600' : 'text-slate-400'">
                            <span x-text="shortDesc.length"></span> / {{ $shortMax }}
                        </span>
                    </div>
                    <textarea id="short_description" name="short_description" rows="6" maxlength="{{ $shortMax }}" x-model="shortDesc"
                        class="admin-field min-h-[8rem] @error('short_description') admin-field--error @enderror"></textarea>
                    <p class="admin-help">Points forts affichés sur la fiche : une ligne par caractéristique, ex. <em>Autonomie : 48 h</em>.</p>
                    @error('short_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="admin-label">Description</label>
                    <textarea id="description" name="description" rows="8" class="admin-field min-h-[10rem]">{{ old('description', $product?->description) }}</textarea>
                    <p class="admin-help">Texte long de la fiche produit.</p>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-card__title">Images</h2>
            @if ($product?->images?->isNotEmpty())
                <div class="mb-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($product->images as $image)
                        <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                            <img src="{{ $image->url() }}" alt="" width="72" height="72" class="admin-media-thumb">
                            <span class="flex-1 text-sm">
                                <span class="block">
                                    <input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked(old('primary_image_id', $product->primaryImage?->id) == $image->id)>
                                    Image principale
                                </span>
                                <span class="mt-1 block text-slate-500">
                                    <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">
                                    Supprimer
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            <p class="admin-help">Jusqu’à 8 images JPEG, PNG ou WebP — 4 Mo max, compressées automatiquement.</p>
            @error('images.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </section>

        <section class="admin-card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="admin-card__title mb-0">Variantes</h2>
                <button type="button" class="admin-btn admin-btn--ghost" @click="addVariant()">Ajouter une variante</button>
            </div>
            @error('variants') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
            <div class="space-y-3">
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50/60 p-4 sm:grid-cols-6">
                        <input type="hidden" :name="`variants[${index}][id]`" x-model="variant.id">
                        <div class="sm:col-span-2">
                            <label class="admin-label">Nom</label>
                            <input type="text" :name="`variants[${index}][name]`" x-model="variant.name" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label">SKU</label>
                            <input type="text" :name="`variants[${index}][sku]`" x-model="variant.sku" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label">Prix</label>
                            <input type="number" min="0" :name="`variants[${index}][price]`" x-model="variant.price" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label">Stock</label>
                            <input type="number" min="0" :name="`variants[${index}][stock_quantity]`" x-model="variant.stock_quantity" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label">Statut</label>
                            <select :name="`variants[${index}][status]`" x-model="variant.status" class="admin-field">
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                        <div class="sm:col-span-5">
                            <label class="admin-label">Options (JSON ou texte)</label>
                            <input type="text" :name="`variants[${index}][options]`" x-model="variant.options" placeholder='{"couleur":"Noir"}' class="admin-field">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="admin-btn admin-btn--danger" @click="removeVariant(index)">Retirer</button>
                        </div>
                    </div>
                </template>
                <p x-show="variants.length === 0" class="text-sm text-slate-500">Aucune variante. Le stock du produit sera utilisé.</p>
            </div>
        </section>
    </div>

    <aside class="space-y-5 xl:sticky xl:top-20">
        <section class="admin-card">
            <h2 class="admin-card__title">Organisation</h2>
            <div class="space-y-4">
                <div>
                    <label for="category_id" class="admin-label">Catégorie</label>
                    <select id="category_id" name="category_id" required class="admin-field @error('category_id') admin-field--error @enderror">
                        <option value="">Choisir</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="brand_id" class="admin-label">Marque</label>
                    <select id="brand_id" name="brand_id" class="admin-field">
                        <option value="">Aucune</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product?->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="admin-label">Statut</label>
                    <select id="status" name="status" class="admin-field">
                        @foreach (\App\Enums\Status::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $product?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2 border-t border-slate-100 pt-3 text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="featured" value="1" @checked(old('featured', $product?->featured))>
                        Mis en avant
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_new" value="1" @checked(old('is_new', $product?->is_new))>
                        Nouveau
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $product?->is_best_seller))>
                        Best-seller
                    </label>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-card__title">Prix (FCFA)</h2>
            <div class="space-y-4">
                <div>
                    <label for="selling_price" class="admin-label">Prix de vente</label>
                    <input id="selling_price" name="selling_price" type="number" min="0" step="1" required value="{{ old('selling_price', $product?->selling_price) }}"
                        class="admin-field @error('selling_price') admin-field--error @enderror">
                    @error('selling_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="compare_price" class="admin-label">Prix barré</label>
                    <input id="compare_price" name="compare_price" type="number" min="0" step="1" value="{{ old('compare_price', $product?->compare_price) }}" class="admin-field">
                    <p class="admin-help">Affiché barré s’il est supérieur au prix de vente.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="purchase_price" class="admin-label">Prix d’achat</label>
                        <input id="purchase_price" name="purchase_price" type="number" min="0" step="1" value="{{ old('purchase_price', $product?->purchase_price ?? 0) }}" class="admin-field">
                    </div>
                    <div>
                        <label for="cost_price" class="admin-label">Coût</label>
                        <input id="cost_price" name="cost_price" type="number" min="0" step="1" value="{{ old('cost_price', $product?->cost_price) }}" class="admin-field">
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-card__title">Stock</h2>
            <div class="space-y-4">
                <div>
                    <label for="stock_quantity" class="admin-label">Quantité</label>
                    <input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}" class="admin-field">
                    <p class="admin-help">Ignoré s’il existe des variantes.</p>
                </div>
                <div>
                    <label for="low_stock_threshold" class="admin-label">Seuil stock bas</label>
                    <input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" step="1" value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}" class="admin-field">
                </div>
                <div>
                    <label for="weight" class="admin-label">Poids (kg)</label>
                    <input id="weight" name="weight" type="number" min="0" step="0.01" value="{{ old('weight', $product?->weight) }}" class="admin-field">
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-card__title">SEO</h2>
            <div class="space-y-4">
                <div>
                    <label for="meta_title" class="admin-label">Titre SEO</label>
                    <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $product?->meta_title) }}" class="admin-field">
                </div>
                <div>
                    <label for="meta_description" class="admin-label">Description SEO</label>
                    <input id="meta_description" name="meta_description" type="text" value="{{ old('meta_description', $product?->meta_description) }}" class="admin-field">
                </div>
            </div>
        </section>

        <div class="flex gap-2">
            <button type="submit" class="admin-btn h-10 flex-1 bg-night-950 px-4 text-white hover:bg-night-800">Enregistrer</button>
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn--ghost h-10 px-4">Annuler</a>
        </div>
    </aside>
</div>
