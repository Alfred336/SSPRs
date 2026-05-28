<main class="min-h-screen bg-zinc-950 px-4 py-8 text-zinc-100 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-5xl items-center justify-center">
        <section class="grid w-full overflow-hidden rounded-lg border border-white/10 bg-white shadow-2xl shadow-cyan-950/30 lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="bg-[linear-gradient(150deg,#083344,#111827_55%,#18181b)] p-8 lg:p-10">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-cyan-300 text-lg font-bold text-zinc-950">S</div>

                <div class="mt-10">
                    <p class="text-sm font-semibold uppercase tracking-widest text-cyan-200">Password Reset</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-normal text-white">Reset your AD password.</h1>
                    <p class="mt-4 text-sm leading-6 text-zinc-300">Verify your identity using the email address or phone number already stored on your Active Directory profile.</p>
                </div>

                <div class="mt-10 space-y-4 text-sm">
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ in_array($step, ['identify', 'verify', 'reset', 'complete'], true) ? 'bg-cyan-300' : 'bg-zinc-600' }}"></span><span>Find account</span></div>
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ in_array($step, ['verify', 'reset', 'complete'], true) ? 'bg-cyan-300' : 'bg-zinc-600' }}"></span><span>Verify code</span></div>
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ in_array($step, ['reset', 'complete'], true) ? 'bg-cyan-300' : 'bg-zinc-600' }}"></span><span>Set password</span></div>
                </div>
            </aside>

            <div class="bg-zinc-50 p-6 text-zinc-950 sm:p-8 lg:p-10">
                @if ($status)
                    <div class="mb-6 rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">{{ $status }}</div>
                @endif

                @if ($step === 'identify')
                    <form wire:submit="sendOtp" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Account verification</h2>
                            <p class="mt-1 text-sm text-zinc-600">Enter the email address or phone number associated with your AD account.</p>
                        </div>

                        <label class="block">
                            <span class="text-sm font-medium text-zinc-700">Email or phone</span>
                            <input wire:model.blur="identifier" type="text" autocomplete="username" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                            @error('identifier') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <div>
                            <span class="text-sm font-medium text-zinc-700">Delivery method</span>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-zinc-300 bg-white px-3 py-3 text-sm has-[:checked]:border-cyan-700 has-[:checked]:bg-cyan-50">
                                    <input wire:model.live="channel" type="radio" value="email" class="h-4 w-4 text-cyan-700 focus:ring-cyan-600">
                                    Email
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-zinc-300 bg-white px-3 py-3 text-sm has-[:checked]:border-cyan-700 has-[:checked]:bg-cyan-50">
                                    <input wire:model.live="channel" type="radio" value="sms" class="h-4 w-4 text-cyan-700 focus:ring-cyan-600">
                                    SMS
                                </label>
                            </div>
                            @error('channel') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="sendOtp" class="inline-flex h-11 w-full items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="sendOtp">Send Verification Code</span>
                            <span wire:loading wire:target="sendOtp">Sending...</span>
                        </button>
                    </form>
                @endif

                @if ($step === 'verify')
                    <form wire:submit="verifyOtp" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Enter verification code</h2>
                            <p class="mt-1 text-sm text-zinc-600">Use the six-digit code sent to your selected delivery method.</p>
                        </div>

                        <label class="block">
                            <span class="text-sm font-medium text-zinc-700">Verification code</span>
                            <input wire:model.blur="otp" type="text" inputmode="numeric" maxlength="6" class="mt-1 h-12 w-full rounded-md border border-zinc-300 bg-white px-3 text-center text-lg font-semibold tracking-widest outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                            @error('otp') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="button" wire:click="restart" class="inline-flex h-11 flex-1 items-center justify-center rounded-md border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100">Start Over</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="verifyOtp" class="inline-flex h-11 flex-1 items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="verifyOtp">Verify Code</span>
                                <span wire:loading wire:target="verifyOtp">Verifying...</span>
                            </button>
                        </div>
                    </form>
                @endif

                @if ($step === 'reset')
                    <form wire:submit="resetPassword" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Choose a new password</h2>
                            <p class="mt-1 text-sm text-zinc-600">Use at least 12 characters with uppercase, lowercase, numbers, and symbols.</p>
                        </div>

                        <label class="block">
                            <span class="text-sm font-medium text-zinc-700">New password</span>
                            <input wire:model.blur="password" type="password" autocomplete="new-password" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                            @error('password') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-zinc-700">Confirm password</span>
                            <input wire:model.blur="password_confirmation" type="password" autocomplete="new-password" class="mt-1 h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                        </label>

                        <button type="submit" wire:loading.attr="disabled" wire:target="resetPassword" class="inline-flex h-11 w-full items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                            <span wire:loading wire:target="resetPassword">Resetting...</span>
                        </button>
                    </form>
                @endif

                @if ($step === 'complete')
                    <div class="space-y-6">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 text-emerald-900">
                            <h2 class="text-xl font-semibold">Password reset complete</h2>
                            <p class="mt-2 text-sm">You can now sign in with your new Active Directory password.</p>
                        </div>

                        <button type="button" wire:click="restart" class="inline-flex h-11 w-full items-center justify-center rounded-md bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-zinc-800">Reset Another Password</button>
                    </div>
                @endif
            </div>
        </section>
    </div>
</main>
