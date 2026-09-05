<div
    x-cloak
    x-show="{{ $show }}"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-night-950/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $idPrefix }}title"
>
    <div class="absolute inset-0" @click="modal = null"></div>
    <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="{{ $idPrefix }}title" class="text-xl font-semibold tracking-tight">{{ $title }}</h2>
                <p class="mt-1 text-sm text-night-800/70">{{ $subtitle }}</p>
            </div>
            <button type="button" class="rounded-lg p-1 text-night-800/50 hover:bg-slate-100 hover:text-night-950" @click="modal = null" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ $action }}" class="mt-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif
            <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
            @include('admin.delivery-fees._form', [
                'fee' => $fee,
                'idPrefix' => $idPrefix,
                'showErrors' => $showErrors,
            ])
        </form>
    </div>
</div>
