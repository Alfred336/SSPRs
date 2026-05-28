<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - {{ config('app.name', 'SSPR') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <section class="w-full max-w-md rounded-lg border border-white/10 bg-white p-8 text-zinc-950 shadow-2xl shadow-cyan-950/30">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-cyan-400 text-lg font-bold text-zinc-950">S</div>

                <h1 class="mt-8 text-2xl font-semibold">Sign in</h1>
                <p class="mt-2 text-sm text-zinc-600">Active Directory login will be enabled in the authenticated profile portal module.</p>

                @if (session('status'))
                    <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-8 space-y-3">
                    <a href="{{ route('sspr.password.request') }}" class="inline-flex h-11 w-full items-center justify-center rounded-md border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100">
                        Reset Password
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
