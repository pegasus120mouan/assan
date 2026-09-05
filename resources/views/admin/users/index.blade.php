@extends('layouts.admin')

@section('title', 'Utilisateurs — '.shop_name())
@section('heading', 'Utilisateurs')

@section('content')
    <div x-data="{ modal: {{ \Illuminate\Support\Js::from($initialModal) }} }" @keydown.escape.window="modal = null">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Utilisateurs</h1>
                <p class="mt-1 text-sm text-night-800/70">Comptes qui gèrent la boutique (administrateurs et gestionnaires).</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white" @click="modal = 'create'">
                Nouvel utilisateur
            </button>
        </div>

        <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, e-mail, téléphone"
                class="w-full rounded-xl border border-night-900/15 bg-white px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2 sm:max-w-xs">
            <select name="role" class="rounded-xl border border-night-900/15 bg-white px-3 py-2.5 text-sm">
                <option value="">Tous les rôles</option>
                @foreach (\App\Enums\UserRole::staffCases() as $role)
                    <option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-night-900/15 bg-white px-3 py-2.5 text-sm">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-full border border-night-900/15 bg-white px-4 py-2 text-sm font-medium">Filtrer</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-night-900/10 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-night-800/50">
                        <tr>
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3">Téléphone</th>
                            <th class="px-4 py-3">Rôle</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-night-900/10">
                        @forelse ($users as $staffUser)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $staffUser->name }}</p>
                                    <p class="text-xs text-night-800/50">{{ $staffUser->email }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $staffUser->phone }}</td>
                                <td class="px-4 py-3">{{ $staffUser->role->label() }}</td>
                                <td class="px-4 py-3">
                                    <span @class(['rounded-full px-2 py-0.5 text-xs', 'bg-emerald-50 text-emerald-700' => $staffUser->isActive(), 'bg-slate-100 text-night-800/60' => ! $staffUser->isActive()])>
                                        {{ $staffUser->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="text-sm underline" @click="modal = {{ $staffUser->id }}">Modifier</button>
                                        @if (auth()->id() !== $staffUser->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $staffUser) }}" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 underline">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-night-800/50">Aucun utilisateur pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-night-900/10 px-4 py-3">{{ $users->links() }}</div>
            @endif
        </div>

        @include('admin.users._modal', [
            'show' => "modal === 'create'",
            'title' => 'Nouvel utilisateur',
            'subtitle' => 'Créez un compte pour gérer la plateforme.',
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'staffUser' => null,
            'idPrefix' => 'create-',
            'hiddenName' => '_create_modal',
            'hiddenValue' => '1',
            'showErrors' => (bool) old('_create_modal'),
        ])

        @foreach ($modalUsers as $modalUser)
            @include('admin.users._modal', [
                'show' => 'modal === '.$modalUser->id,
                'title' => 'Modifier '.$modalUser->name,
                'subtitle' => 'Mettez à jour le compte qui gère la boutique.',
                'action' => route('admin.users.update', $modalUser),
                'method' => 'PUT',
                'staffUser' => $modalUser,
                'idPrefix' => 'edit-'.$modalUser->id.'-',
                'hiddenName' => '_edit_modal',
                'hiddenValue' => $modalUser->id,
                'showErrors' => (string) old('_edit_modal') === (string) $modalUser->id,
            ])
        @endforeach
    </div>
@endsection
