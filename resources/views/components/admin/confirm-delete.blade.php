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
            <form method="POST" class="mt-6 flex flex-wrap justify-end gap-2" :action="action">
                @csrf
                @method('DELETE')
                <button type="button" class="rounded-full border border-night-900/15 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50" @click="open = false">
                    Annuler
                </button>
                <button type="submit" class="rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" x-text="confirmLabel"></button>
            </form>
        </div>
    </div>
</div>
