<div
    x-data="{
        open: false,
        title: 'Supprimer',
        message: '',
        confirmLabel: 'Supprimer',
        action: '',
    }"
    @admin-confirm-delete.window="
        title = $event.detail.title || 'Supprimer';
        message = $event.detail.message || '';
        confirmLabel = $event.detail.confirmLabel || 'Supprimer';
        action = $event.detail.action || '';
        open = true;
    "
    @keydown.escape.window="open = false"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-night-950/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="admin-confirm-title"
    >
        <div class="absolute inset-0" @click="open = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 id="admin-confirm-title" class="text-lg font-semibold tracking-tight text-night-950" x-text="title"></h2>
            <p class="mt-2 text-sm text-night-800/70" x-text="message"></p>
            <form method="POST" class="mt-6 flex items-center justify-end gap-2" :action="action">
                @csrf
                @method('DELETE')
                <button type="button" class="admin-btn admin-btn--ghost h-9 px-4" @click="open = false">
                    Annuler
                </button>
                <button type="submit" class="admin-btn admin-btn--danger h-9 px-4" x-text="confirmLabel">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
