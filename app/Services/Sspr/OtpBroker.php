<?php

namespace App\Services\Sspr;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class OtpBroker
{
    /**
     * @param  array{dn: string, display_name: string, email: ?string, phone: ?string}  $user
     */
    public function send(array $user, string $channel): string
    {
        $otp = (string) random_int(100000, 999999);
        $token = (string) Str::uuid();
        $expiresAt = now()->addMinutes((int) config('sspr.otp.expires_minutes', 10));

        Cache::put($this->cacheKey($token), [
            'hash' => Hash::make($otp),
            'dn' => Crypt::encryptString($user['dn']),
            'display_name' => $user['display_name'],
            'channel' => $channel,
            'attempts' => 0,
        ], $expiresAt);

        match ($channel) {
            'email' => $this->sendEmail((string) $user['email'], $otp),
            'sms' => $this->sendSms((string) $user['phone'], $otp),
            default => throw new RuntimeException('Unsupported OTP channel.'),
        };

        return $token;
    }

    /**
     * @return array{dn: string, display_name: string, channel: string}
     */
    public function verify(string $token, string $otp): array
    {
        $key = $this->cacheKey($token);
        $payload = Cache::get($key);

        if (! is_array($payload)) {
            throw new RuntimeException('The verification code has expired. Request a new code.');
        }

        $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;

        if ($payload['attempts'] > (int) config('sspr.otp.max_attempts', 5)) {
            Cache::forget($key);

            throw new RuntimeException('Too many incorrect verification attempts. Request a new code.');
        }

        Cache::put($key, $payload, now()->addMinutes((int) config('sspr.otp.expires_minutes', 10)));

        if (! Hash::check($otp, (string) $payload['hash'])) {
            throw new RuntimeException('The verification code is invalid.');
        }

        return [
            'dn' => Crypt::decryptString((string) $payload['dn']),
            'display_name' => (string) $payload['display_name'],
            'channel' => (string) $payload['channel'],
        ];
    }

    public function consume(string $token): void
    {
        Cache::forget($this->cacheKey($token));
    }

    protected function sendEmail(string $email, string $otp): void
    {
        if ($email === '') {
            throw new RuntimeException('This account does not have an email address in Active Directory.');
        }

        Mail::raw("Your SSPR verification code is {$otp}. It expires in ".config('sspr.otp.expires_minutes', 10).' minutes.', function ($message) use ($email): void {
            $message->to($email)->subject('SSPR Verification Code');
        });
    }

    protected function sendSms(string $phone, string $otp): void
    {
        if ($phone === '') {
            throw new RuntimeException('This account does not have a phone number in Active Directory.');
        }

        $response = Http::withToken((string) config('sspr.sms.token'))
            ->timeout(10)
            ->post((string) config('sspr.sms.endpoint'), array_filter([
                'from' => config('sspr.sms.from') ?: null,
                'to' => $phone,
                'message' => "Your SSPR verification code is {$otp}. It expires in ".config('sspr.otp.expires_minutes', 10).' minutes.',
            ], fn ($value) => $value !== null));

        if (! $response->successful()) {
            throw new RuntimeException('SMS provider rejected the OTP request.');
        }
    }

    protected function cacheKey(string $token): string
    {
        return 'sspr:otp:'.hash('sha256', $token);
    }
}
