<main class="min-h-screen bg-[radial-gradient(circle_at_top_left,#155e75,transparent_34rem),linear-gradient(135deg,#09090b,#18181b_45%,#0f172a)] px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl items-center">
        <section class="grid w-full overflow-hidden rounded-lg border border-white/10 bg-white shadow-2xl shadow-cyan-950/40 lg:grid-cols-[0.85fr_1.4fr]">
            <aside class="bg-zinc-950 p-8 text-white lg:p-10">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-cyan-400 text-lg font-bold text-zinc-950">S</div>

                <div class="mt-10">
                    <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Installation Wizard</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-normal text-white">Configure SSPR securely.</h1>
                    <p class="mt-4 text-sm leading-6 text-zinc-300">Connect Active Directory, outbound email, and SMS delivery before enabling password reset and profile management.</p>
                </div>

                <div class="mt-10 space-y-4 text-sm">
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ $dbPassed ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span><span>Database provisioned</span></div>
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ $ldapPassed ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span><span>Active Directory bind test</span></div>
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ $mailPassed ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span><span>SMTP delivery test</span></div>
                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full {{ $smsPassed ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span><span>SMS provider test</span></div>
                </div>
            </aside>

            <form wire:submit="save" class="max-h-[calc(100vh-4rem)] overflow-y-auto bg-zinc-50 p-6 text-zinc-950 sm:p-8 lg:p-10">
                @if (session('setup_saved'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('setup_saved') }}</div>
                @endif

                @error('save')
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="space-y-8">
                    <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <h2 class="text-lg font-semibold">Application Database</h2>
                                <p class="mt-1 text-sm text-zinc-600">Choose MySQL or PostgreSQL. The installer creates the `sspr` database if it does not exist and runs migrations during save.</p>
                            </div>
                            <button type="button" wire:click="testDatabaseConnection" wire:loading.attr="disabled" wire:target="testDatabaseConnection" class="inline-flex h-10 items-center justify-center rounded-md bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="testDatabaseConnection">Test Database</span>
                                <span wire:loading wire:target="testDatabaseConnection">Testing...</span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Database Engine</span>
                                <select wire:model.live="dbDriver" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                    <option value="mysql">MySQL / MariaDB</option>
                                    <option value="pgsql">PostgreSQL</option>
                                </select>
                                @error('dbDriver') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Database Name</span>
                                <input wire:model.blur="dbDatabase" type="text" placeholder="sspr" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                @error('dbDatabase') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Host</span>
                                <input wire:model.blur="dbHost" type="text" placeholder="127.0.0.1" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                @error('dbHost') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Port</span>
                                <input wire:model.blur="dbPort" type="number" min="1" max="65535" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                @error('dbPort') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Username</span>
                                <input wire:model.blur="dbUsername" type="text" autocomplete="username" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                @error('dbUsername') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-zinc-700">Password</span>
                                <input wire:model.blur="dbPassword" type="password" autocomplete="new-password" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
                                @error('dbPassword') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="block rounded-md border border-amber-200 bg-amber-50 p-4 md:col-span-2">
                                <span class="flex items-start gap-3">
                                    <input wire:model.live="dbDropExisting" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-amber-400 text-amber-700 focus:ring-amber-600">
                                    <span>
                                        <span class="block text-sm font-semibold text-amber-900">Drop existing database if it already exists</span>
                                        <span class="mt-1 block text-sm leading-5 text-amber-800">This permanently deletes all tables and data in the selected database before creating a fresh SSPR schema.</span>
                                    </span>
                                </span>
                                @error('dbDropExisting') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        @if ($dbStatus)
                            <p class="mt-4 rounded-md border px-3 py-2 text-sm {{ $dbPassed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">{{ $dbStatus }}</p>
                        @endif
                    </section>

                    <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <h2 class="text-lg font-semibold">Active Directory</h2>
                                <p class="mt-1 text-sm text-zinc-600">Use a least-privileged service account for lookup tests. The reset admin bind is added in Module 2.</p>
                            </div>
                            <button type="button" wire:click="testLdapConnection" wire:loading.attr="disabled" wire:target="testLdapConnection" class="inline-flex h-10 items-center justify-center rounded-md bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="testLdapConnection">Test AD</span>
                                <span wire:loading wire:target="testLdapConnection">Testing...</span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Domain Controller</span><input wire:model.blur="ldapHost" type="text" placeholder="dc01.example.com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('ldapHost') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Port</span><input wire:model.blur="ldapPort" type="number" min="1" max="65535" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('ldapPort') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block md:col-span-2"><span class="text-sm font-medium text-zinc-700">Base DN</span><input wire:model.blur="ldapBaseDn" type="text" placeholder="dc=example,dc=com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('ldapBaseDn') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Bind Username</span><input wire:model.blur="ldapUsername" type="text" placeholder="CN=svc-sspr,OU=Service Accounts,DC=example,DC=com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('ldapUsername') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Bind Password</span><input wire:model.blur="ldapPassword" type="password" autocomplete="new-password" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('ldapPassword') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-5 text-sm text-zinc-700">
                            <label class="inline-flex items-center gap-2"><input wire:model.live="ldapUseSsl" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-cyan-700 focus:ring-cyan-600">Use SSL</label>
                            <label class="inline-flex items-center gap-2"><input wire:model.live="ldapUseTls" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-cyan-700 focus:ring-cyan-600">Use TLS</label>
                        </div>

                        @if ($ldapStatus)
                            <p class="mt-4 rounded-md border px-3 py-2 text-sm {{ $ldapPassed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">{{ $ldapStatus }}</p>
                        @endif
                    </section>

                    <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <h2 class="text-lg font-semibold">Email SMTP</h2>
                                <p class="mt-1 text-sm text-zinc-600">The wizard sends a live test message to verify outbound OTP delivery.</p>
                            </div>
                            <button type="button" wire:click="testMailConnection" wire:loading.attr="disabled" wire:target="testMailConnection" class="inline-flex h-10 items-center justify-center rounded-md bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="testMailConnection">Test Email</span>
                                <span wire:loading wire:target="testMailConnection">Testing...</span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-sm font-medium text-zinc-700">SMTP Host</span><input wire:model.blur="mailHost" type="text" placeholder="smtp.example.com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailHost') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Port</span><input wire:model.blur="mailPort" type="number" min="1" max="65535" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailPort') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Username</span><input wire:model.blur="mailUsername" type="text" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailUsername') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Password</span><input wire:model.blur="mailPassword" type="password" autocomplete="new-password" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailPassword') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">SMTP Scheme</span><select wire:model.blur="mailEncryption" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100"><option value="">Default</option><option value="smtp">SMTP / STARTTLS</option><option value="smtps">SMTPS</option></select>@error('mailEncryption') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Test Recipient</span><input wire:model.blur="mailTestTo" type="email" placeholder="admin@example.com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailTestTo') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">From Address</span><input wire:model.blur="mailFromAddress" type="email" placeholder="sspr@example.com" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailFromAddress') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">From Name</span><input wire:model.blur="mailFromName" type="text" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('mailFromName') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                        </div>

                        @if ($mailStatus)
                            <p class="mt-4 rounded-md border px-3 py-2 text-sm {{ $mailPassed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">{{ $mailStatus }}</p>
                        @endif
                    </section>

                    <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <h2 class="text-lg font-semibold">SMS Provider</h2>
                                <p class="mt-1 text-sm text-zinc-600">The generic provider contract posts JSON with from, to, and message fields.</p>
                            </div>
                            <button type="button" wire:click="testSmsConnection" wire:loading.attr="disabled" wire:target="testSmsConnection" class="inline-flex h-10 items-center justify-center rounded-md bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="testSmsConnection">Test SMS</span>
                                <span wire:loading wire:target="testSmsConnection">Testing...</span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="block md:col-span-2"><span class="text-sm font-medium text-zinc-700">API Endpoint</span><input wire:model.blur="smsEndpoint" type="url" placeholder="https://sms.example.com/messages" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('smsEndpoint') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Bearer Token</span><input wire:model.blur="smsToken" type="password" autocomplete="new-password" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('smsToken') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Sender ID <span class="font-normal text-zinc-500">Optional</span></span><input wire:model.blur="smsFrom" type="text" placeholder="SSPR" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('smsFrom') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                            <label class="block"><span class="text-sm font-medium text-zinc-700">Test Phone Number</span><input wire:model.blur="smsTestTo" type="text" placeholder="+15551234567" class="mt-1 h-10 w-full rounded-md border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">@error('smsTestTo') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                        </div>

                        @if ($smsStatus)
                            <p class="mt-4 rounded-md border px-3 py-2 text-sm {{ $smsPassed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">{{ $smsStatus }}</p>
                        @endif
                    </section>
                </div>

                <div class="sticky bottom-0 -mx-6 mt-8 border-t border-zinc-200 bg-zinc-50/95 px-6 py-5 backdrop-blur sm:-mx-8 sm:px-8 lg:-mx-10 lg:px-10">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-zinc-600">All tests must pass before the installer writes `.env`, creates `sspr`, and migrates tables.</p>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex h-11 items-center justify-center rounded-md bg-cyan-700 px-5 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="save">Install Configuration</span>
                            <span wire:loading wire:target="save">Installing...</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>
