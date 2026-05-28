<?php

namespace App\Livewire\Auth;

use App\Services\Sspr\ActiveDirectoryPasswordBroker;
use App\Services\Sspr\OtpBroker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.setup')]
class SsprPasswordReset extends Component
{
    public string $step = 'identify';

    public string $identifier = '';

    public string $channel = 'email';

    public string $otp = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $token = null;

    public ?string $displayName = null;

    public ?string $status = null;

    public function sendOtp(ActiveDirectoryPasswordBroker $activeDirectory, OtpBroker $otpBroker): void
    {
        $this->validate([
            'identifier' => 'required|string|min:3|max:255',
            'channel' => 'required|in:email,sms',
        ]);

        $limiterKey = $this->limiterKey('send');

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->addError('identifier', 'Too many reset attempts. Try again in '.RateLimiter::availableIn($limiterKey).' seconds.');

            return;
        }

        RateLimiter::hit($limiterKey, 300);

        try {
            $user = $activeDirectory->findUserByEmailOrPhone($this->identifier);
            $this->token = $otpBroker->send($user, $this->channel);
            $this->displayName = $user['display_name'];
            $this->step = 'verify';
            $this->status = 'Verification code sent. Check your selected delivery method.';
        } catch (Throwable $exception) {
            report($exception);

            $this->status = 'If the account exists and has that delivery method, a verification code has been sent.';
            $this->step = 'identify';
        }
    }

    public function verifyOtp(OtpBroker $otpBroker): void
    {
        $this->validate([
            'otp' => 'required|digits:6',
        ]);

        if ($this->token === null) {
            $this->addError('otp', 'Request a new verification code.');

            return;
        }

        try {
            $payload = $otpBroker->verify($this->token, $this->otp);
            $this->displayName = $payload['display_name'];
            $this->step = 'reset';
            $this->status = 'Verification complete. Choose a new password.';
        } catch (Throwable $exception) {
            $this->addError('otp', $exception->getMessage());
        }
    }

    public function resetPassword(ActiveDirectoryPasswordBroker $activeDirectory, OtpBroker $otpBroker): void
    {
        $this->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($this->token === null) {
            $this->addError('password', 'Request a new verification code.');

            return;
        }

        $limiterKey = $this->limiterKey('reset');

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            $this->addError('password', 'Too many reset attempts. Try again in '.RateLimiter::availableIn($limiterKey).' seconds.');

            return;
        }

        RateLimiter::hit($limiterKey, 300);

        try {
            $payload = $otpBroker->verify($this->token, $this->otp);
            $activeDirectory->resetPassword($payload['dn'], $this->password);
            $otpBroker->consume($this->token);

            $this->reset(['identifier', 'otp', 'password', 'password_confirmation', 'token']);
            $this->step = 'complete';
            $this->status = 'Your Active Directory password has been reset.';
        } catch (Throwable $exception) {
            report($exception);

            $this->addError('password', 'The password reset could not be completed. Confirm the new password meets your domain policy and try again.');
        }
    }

    public function restart(): void
    {
        $this->reset(['step', 'identifier', 'channel', 'otp', 'password', 'password_confirmation', 'token', 'displayName', 'status']);
        $this->step = 'identify';
        $this->channel = 'email';
    }

    public function render()
    {
        return view('livewire.auth.sspr-password-reset');
    }

    protected function limiterKey(string $action): string
    {
        return 'sspr:'.$action.':'.request()->ip().':'.hash('sha256', strtolower($this->identifier));
    }
}
