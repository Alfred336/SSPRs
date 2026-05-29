<main class="flex min-h-screen items-center justify-center px-4 py-10">
    <section class="grid w-full max-w-5xl overflow-hidden rounded-lg border border-white/10 bg-white shadow-2xl shadow-cyan-950/30 lg:grid-cols-[0.9fr_1.1fr]">
        <aside class="bg-[linear-gradient(150deg,#083344,#111827_55%,#18181b)] p-8 lg:p-10">
            <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-cyan-300 text-lg font-bold text-zinc-950">S</div>
            <div class="mt-10">
                <p class="text-sm font-semibold uppercase tracking-widest text-cyan-200">Active Directory Login</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-normal text-white">Access your profile portal.</h1>
                <p class="mt-4 text-sm leading-6 text-zinc-300">Sign in with your domain username, UPN, or email address.</p>
            </div>
        </aside>

        <form wire:submit="authenticate" class="bg-zinc-50 p-6 text-zinc-950 sm:p-8 lg:p-10">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <div>
                <h2 class="text-xl font-semibold">Sign in</h2>
                <p class="mt-1 text-sm text-zinc-600">Use your Active Directory credentials.</p>
            </div>

            <div class="mt-6 space-y-4">
                <label class="block">
                    <span class="text-sm font-medium text-zinc-700">Username, UPN, or email</span>
                    <input wire:model.blur="username" type="text" autocomplete="username" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                    @error('username') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-zinc-700">Password</span>
                    <input wire:model.blur="password" type="password" autocomplete="current-password" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                    @error('password') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                    <input wire:model.live="remember" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-cyan-700 focus:ring-cyan-600">
                    Remember this browser
                </label>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="authenticate" class="mt-6 inline-flex h-11 w-full items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                <span wire:loading.remove wire:target="authenticate">Sign In</span>
                <span wire:loading wire:target="authenticate">Signing in...</span>
            </button>

            <a href="{{ route('sspr.password.request') }}" class="mt-4 inline-flex h-10 w-full items-center justify-center rounded-md border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100">
                Reset Password
            </a>
        </form>
    </section>
</main>
