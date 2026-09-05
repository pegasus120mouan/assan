@php
    $editingSelf = $staffUser && auth()->id() === $staffUser->id;
    $idPrefix = $idPrefix ?? '';
    $showErrors = $showErrors ?? false;
    $nameValue = $showErrors ? old('name', $staffUser?->name) : $staffUser?->name;
    $emailValue = $showErrors ? old('email', $staffUser?->email) : $staffUser?->email;
    $phoneValue = $showErrors ? old('phone', $staffUser?->phone) : $staffUser?->phone;
    $roleValue = $showErrors ? old('role', $staffUser?->role?->value ?? 'manager') : ($staffUser?->role?->value ?? 'manager');
    $statusValue = $showErrors ? old('status', $staffUser?->status?->value ?? 'active') : ($staffUser?->status?->value ?? 'active');
@endphp

<div class="space-y-4">
    <div>
        <label for="{{ $idPrefix }}name" class="mb-1 block text-sm font-medium">Nom</label>
        <input id="{{ $idPrefix }}name" name="name" type="text" value="{{ $nameValue }}" required
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @if ($showErrors)
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="{{ $idPrefix }}email" class="mb-1 block text-sm font-medium">E-mail</label>
        <input id="{{ $idPrefix }}email" name="email" type="email" value="{{ $emailValue }}" required
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @if ($showErrors)
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="{{ $idPrefix }}phone" class="mb-1 block text-sm font-medium">Téléphone</label>
        <input id="{{ $idPrefix }}phone" name="phone" type="text" value="{{ $phoneValue }}" required
            class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        @if ($showErrors)
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}role" class="mb-1 block text-sm font-medium">Rôle</label>
            <select id="{{ $idPrefix }}role" name="role" @disabled($editingSelf) class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                @foreach (\App\Enums\UserRole::staffCases() as $role)
                    <option value="{{ $role->value }}" @selected($roleValue === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @if ($editingSelf)
                <input type="hidden" name="role" value="{{ $staffUser->role->value }}">
                <p class="mt-1 text-xs text-night-800/50">Vous ne pouvez pas modifier votre propre rôle.</p>
            @endif
            @if ($showErrors)
                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="{{ $idPrefix }}status" class="mb-1 block text-sm font-medium">Statut</label>
            <select id="{{ $idPrefix }}status" name="status" @disabled($editingSelf) class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected($statusValue === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @if ($editingSelf)
                <input type="hidden" name="status" value="{{ $staffUser->status->value }}">
                <p class="mt-1 text-xs text-night-800/50">Vous ne pouvez pas désactiver votre propre compte.</p>
            @endif
            @if ($showErrors)
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}password" class="mb-1 block text-sm font-medium">Mot de passe{{ $staffUser ? ' (optionnel)' : '' }}</label>
            <input id="{{ $idPrefix }}password" name="password" type="password" autocomplete="new-password" @required(! $staffUser)
                class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
            @if ($showErrors)
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="{{ $idPrefix }}password_confirmation" class="mb-1 block text-sm font-medium">Confirmation</label>
            <input id="{{ $idPrefix }}password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
        </div>
    </div>

    <p class="text-xs text-night-800/50">Administrateur : accès complet, y compris les utilisateurs. Gestionnaire : ventes, catalogue et clients.</p>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        <button type="button" class="text-sm underline" @click="modal = null">Annuler</button>
    </div>
</div>
