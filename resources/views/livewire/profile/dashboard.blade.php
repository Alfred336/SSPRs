<main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    @if ($status)
        <div class="mb-6 rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">{{ $status }}</div>
    @endif

    <section class="grid gap-6 lg:grid-cols-[1fr_0.7fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">{{ $profile['display_name'] ?? auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-zinc-600">{{ $profile['username'] ?? auth()->user()->ad_username }}</p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-md border border-zinc-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Department</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $profile['department'] ?? 'Not set' }}</dd>
                </div>
                <div class="rounded-md border border-zinc-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Title</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $profile['title'] ?? 'Not set' }}</dd>
                </div>
                <div class="rounded-md border border-zinc-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Email</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $profile['email'] ?? 'Not set' }}</dd>
                </div>
                <div class="rounded-md border border-zinc-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Phone</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $profile['phone'] ?? 'Not set' }}</dd>
                </div>
            </dl>
        </div>

        <aside class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Profile actions</h2>
            <div class="mt-5 space-y-3">
                <a href="{{ route('profile.contact') }}" class="inline-flex h-11 w-full items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800">Edit Contact Info</a>
                <a href="{{ route('sspr.password.request') }}" class="inline-flex h-11 w-full items-center justify-center rounded-md border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100">Reset Password</a>
            </div>
        </aside>
    </section>
</main>
