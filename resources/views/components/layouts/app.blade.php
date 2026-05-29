<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'SSPR') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-700 text-sm font-bold text-white">S</span>
                    <span class="font-semibold">SSPR Portal</span>
                </a>

                <nav class="flex items-center gap-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100">Dashboard</a>
                    <a href="{{ route('profile.contact') }}" class="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-zinc-950 px-3 py-2 font-medium text-white hover:bg-zinc-800">Sign Out</button>
                    </form>
                </nav>
            </div>
        </header>

        {{ $slot }}
    </body>
</html>
