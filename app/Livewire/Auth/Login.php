<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\Sspr\ActiveDirectoryPasswordBroker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(ActiveDirectoryPasswordBroker $activeDirectory): void
    {
        $this->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:500',
        ]);

        $limiterKey = 'login:'.request()->ip().':'.Str::lower($this->username);

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->addError('username', 'Too many login attempts. Try again in '.RateLimiter::availableIn($limiterKey).' seconds.');

            return;
        }

        try {
            $profile = $activeDirectory->authenticate($this->username, $this->password);
        } catch (Throwable $exception) {
            report($exception);
            RateLimiter::hit($limiterKey, 300);

            $this->addError('username', 'These credentials do not match Active Directory.');

            return;
        }

        RateLimiter::clear($limiterKey);

        $user = User::updateOrCreate(
            ['ad_dn' => $profile['dn']],
            [
                'name' => $profile['display_name'],
                'email' => $profile['email'] ?: $this->fallbackEmail($profile),
                'password' => Hash::make(Str::random(48)),
                'ad_username' => $profile['username'],
                'department' => $profile['department'],
                'title' => $profile['title'],
                'phone' => $profile['phone'],
            ],
        );

        Auth::login($user, $this->remember);
        request()->session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }

    /**
     * @param  array{dn: string, username: ?string}  $profile
     */
    protected function fallbackEmail(array $profile): string
    {
        return Str::slug($profile['username'] ?: hash('sha256', $profile['dn'])).'@ad.local';
    }
}
