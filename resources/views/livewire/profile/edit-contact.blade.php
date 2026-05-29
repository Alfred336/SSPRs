<main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <div>
            <h1 class="text-2xl font-semibold">Edit contact information</h1>
            <p class="mt-1 text-sm text-zinc-600">Changes are written directly to your Active Directory user object.</p>
        </div>

        @if ($status)
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $status }}</div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-4">
            <label class="block">
                <span class="text-sm font-medium text-zinc-700">Email address</span>
                <input wire:model.blur="email" type="email" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                @error('email') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="text-sm font-medium text-zinc-700">Phone number</span>
                <input wire:model.blur="phone" type="text" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                @error('phone') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="inline-flex h-11 flex-1 items-center justify-center rounded-md border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex h-11 flex-1 items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </section>
</main>
