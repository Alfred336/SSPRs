<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationIsInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled() || $request->is('setup', 'setup/*', 'livewire*')) {
            return $next($request);
        }

        return redirect()->route('setup');
    }

    protected function isInstalled(): bool
    {
        return file_exists(base_path('.env')) && (bool) config('sspr.installed');
    }
}
