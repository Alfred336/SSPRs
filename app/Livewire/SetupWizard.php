<?php

namespace App\Livewire;

use App\Support\EnvWriter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PDO;
use Throwable;

#[Layout('components.layouts.setup')]
class SetupWizard extends Component
{
    public string $ldapHost = '';

    public int $ldapPort = 389;

    public string $ldapBaseDn = '';

    public string $ldapUsername = '';

    public string $ldapPassword = '';

    public bool $ldapUseSsl = false;

    public bool $ldapUseTls = false;

    public string $dbDriver = 'mysql';

    public string $dbHost = '127.0.0.1';

    public int $dbPort = 3306;

    public string $dbDatabase = 'sspr';

    public string $dbUsername = '';

    public string $dbPassword = '';

    public bool $dbDropExisting = false;

    public bool $dbExists = false;

    public string $mailHost = '';

    public int $mailPort = 587;

    public string $mailUsername = '';

    public string $mailPassword = '';

    public string $mailEncryption = 'smtp';

    public string $mailFromAddress = '';

    public string $mailFromName = 'SSPR';

    public string $mailTestTo = '';

    public string $smsEndpoint = '';

    public string $smsToken = '';

    public string $smsFrom = '';

    public string $smsTestTo = '';

    public ?string $ldapStatus = null;

    public ?string $dbStatus = null;

    public ?string $mailStatus = null;

    public ?string $smsStatus = null;

    public bool $ldapPassed = false;

    public bool $dbPassed = false;

    public bool $mailPassed = false;

    public bool $smsPassed = false;

    public function mount(): void
    {
        $this->ldapHost = (string) config('sspr.ldap.host', '');
        $this->ldapPort = (int) config('sspr.ldap.port', 389);
        $this->ldapBaseDn = (string) config('sspr.ldap.base_dn', '');
        $this->ldapUsername = (string) config('sspr.ldap.username', '');
        $this->ldapUseSsl = (bool) config('sspr.ldap.use_ssl', false);
        $this->ldapUseTls = (bool) config('sspr.ldap.use_tls', false);

        $this->dbDriver = in_array(config('database.default'), ['mysql', 'pgsql'], true)
            ? (string) config('database.default')
            : 'mysql';
        $this->dbHost = (string) config("database.connections.{$this->dbDriver}.host", '127.0.0.1');
        $this->dbPort = (int) config("database.connections.{$this->dbDriver}.port", $this->dbDriver === 'pgsql' ? 5432 : 3306);
        $this->dbDatabase = (string) config("database.connections.{$this->dbDriver}.database", 'sspr');
        $this->dbUsername = (string) config("database.connections.{$this->dbDriver}.username", '');

        $this->mailHost = (string) config('mail.mailers.smtp.host', '');
        $this->mailPort = (int) config('mail.mailers.smtp.port', 587);
        $this->mailUsername = (string) config('mail.mailers.smtp.username', '');
        $this->mailEncryption = (string) config('mail.mailers.smtp.scheme', 'smtp');
        $this->mailFromAddress = (string) config('mail.from.address', '');
        $this->mailFromName = (string) config('mail.from.name', 'SSPR');

        $this->smsEndpoint = (string) config('sspr.sms.endpoint', '');
        $this->smsFrom = (string) config('sspr.sms.from', '');
        $this->smsTestTo = (string) config('sspr.sms.test_to', '');
    }

    public function updatedDbDriver(string $driver): void
    {
        if ($driver === 'mysql') {
            $this->dbPort = 3306;
        }

        if ($driver === 'pgsql') {
            $this->dbPort = 5432;
        }

        $this->dbPassed = false;
        $this->dbStatus = null;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['dbHost', 'dbPort', 'dbDatabase', 'dbUsername', 'dbPassword', 'dbDropExisting'], true)) {
            $this->dbPassed = false;
            $this->dbStatus = null;
        }
    }

    public function testDatabaseConnection(): void
    {
        $this->validate($this->databaseRules());
        $this->dbPassed = false;

        try {
            $this->createDatabaseIfMissing();
            $this->dbPassed = true;
            $this->dbStatus = "Database '{$this->dbDatabase}' is reachable and ready.";
        } catch (Throwable $exception) {
            $this->dbStatus = 'Database test failed: '.$exception->getMessage();
        }
    }

    public function testLdapConnection(): void
    {
        $this->validate($this->ldapRules());
        $this->ldapPassed = false;

        try {
            if (! extension_loaded('ldap')) {
                throw new \RuntimeException('The PHP LDAP extension is not enabled.');
            }

            if (! class_exists(\LdapRecord\Connection::class)) {
                throw new \RuntimeException('Install LdapRecord-Laravel before testing LDAP.');
            }

            $connection = new \LdapRecord\Connection([
                'hosts' => [$this->ldapHost],
                'port' => $this->ldapPort,
                'base_dn' => $this->ldapBaseDn,
                'username' => $this->ldapUsername,
                'password' => $this->ldapPassword,
                'use_ssl' => $this->ldapUseSsl,
                'use_tls' => $this->ldapUseTls,
                'timeout' => 5,
            ]);

            $connection->connect();
            $connection->auth()->attempt($this->ldapUsername, $this->ldapPassword, true);

            $this->ldapPassed = true;
            $this->ldapStatus = 'Active Directory connection verified.';
        } catch (Throwable $exception) {
            $this->ldapStatus = 'Active Directory test failed: '.$exception->getMessage();
        }
    }

    public function testMailConnection(): void
    {
        $this->validate($this->mailRules());
        $this->mailPassed = false;

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $this->mailHost,
                'mail.mailers.smtp.port' => $this->mailPort,
                'mail.mailers.smtp.username' => $this->mailUsername,
                'mail.mailers.smtp.password' => $this->mailPassword,
                'mail.mailers.smtp.scheme' => $this->mailEncryption ?: null,
                'mail.from.address' => $this->mailFromAddress,
                'mail.from.name' => $this->mailFromName,
            ]);

            Mail::raw('Your SSPR email configuration test was successful.', function ($message): void {
                $message->to($this->mailTestTo)->subject('SSPR Email Test');
            });

            $this->mailPassed = true;
            $this->mailStatus = 'Email test message sent.';
        } catch (Throwable $exception) {
            $this->mailStatus = 'Email test failed: '.$exception->getMessage();
        }
    }

    public function testSmsConnection(): void
    {
        $this->validate($this->smsRules());
        $this->smsPassed = false;

        try {
            $response = Http::withToken($this->smsToken)
                ->timeout(10)
                ->post($this->smsEndpoint, array_filter([
                    'from' => $this->smsFrom ?: null,
                    'to' => $this->smsTestTo,
                    'message' => 'Your SSPR SMS configuration test was successful.',
                ], fn ($value) => $value !== null));

            if (! $response->successful()) {
                throw new \RuntimeException('Provider returned HTTP '.$response->status().'.');
            }

            $this->smsPassed = true;
            $this->smsStatus = 'SMS provider accepted the test message.';
        } catch (Throwable $exception) {
            $this->smsStatus = 'SMS test failed: '.$exception->getMessage();
        }
    }

    public function save(EnvWriter $env): void
    {
        $this->validate([
            ...$this->databaseRules(),
            ...$this->ldapRules(),
            ...$this->mailRules(),
            ...$this->smsRules(),
        ]);

        if (! $this->dbPassed || ! $this->ldapPassed || ! $this->mailPassed || ! $this->smsPassed) {
            $this->addError('save', 'Test Database, Active Directory, Email, and SMS successfully before saving.');

            return;
        }

        $env->write([
            'APP_NAME' => 'SSPR',
            'DB_CONNECTION' => $this->dbDriver,
            'DB_HOST' => $this->dbHost,
            'DB_PORT' => (string) $this->dbPort,
            'DB_DATABASE' => $this->dbDatabase,
            'DB_USERNAME' => $this->dbUsername,
            'DB_PASSWORD' => $this->dbPassword,
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => $this->mailHost,
            'MAIL_PORT' => (string) $this->mailPort,
            'MAIL_USERNAME' => $this->mailUsername,
            'MAIL_PASSWORD' => $this->mailPassword,
            'MAIL_SCHEME' => $this->mailEncryption,
            'MAIL_FROM_ADDRESS' => $this->mailFromAddress,
            'MAIL_FROM_NAME' => $this->mailFromName,
            'LDAP_CONNECTION' => 'default',
            'LDAP_HOST' => $this->ldapHost,
            'LDAP_PORT' => (string) $this->ldapPort,
            'LDAP_BASE_DN' => $this->ldapBaseDn,
            'LDAP_USERNAME' => $this->ldapUsername,
            'LDAP_PASSWORD' => $this->ldapPassword,
            'LDAP_SSL' => $this->ldapUseSsl ? 'true' : 'false',
            'LDAP_TLS' => $this->ldapUseTls ? 'true' : 'false',
            'LDAP_TIMEOUT' => '5',
            'SMS_ENDPOINT' => $this->smsEndpoint,
            'SMS_TOKEN' => $this->smsToken,
            'SMS_FROM' => $this->smsFrom,
            'SMS_TEST_TO' => $this->smsTestTo,
            'SSPR_INSTALLED' => 'false',
        ]);

        Artisan::call('config:clear');

        $this->configureRuntimeDatabase();

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $exception) {
            $this->addError('save', 'Configuration was saved, but migrations failed: '.$exception->getMessage());

            return;
        }

        $env->write([
            'SSPR_INSTALLED' => 'true',
        ]);

        Artisan::call('config:clear');

        session()->flash('status', 'Installation completed. Sign in to continue.');

        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.setup-wizard');
    }

    /**
     * @return array<string, string>
     */
    protected function databaseRules(): array
    {
        return [
            'dbDriver' => 'required|in:mysql,pgsql',
            'dbHost' => 'required|string|max:255',
            'dbPort' => 'required|integer|min:1|max:65535',
            'dbDatabase' => 'required|string|alpha_dash|max:64',
            'dbUsername' => 'required|string|max:255',
            'dbPassword' => 'nullable|string|max:500',
            'dbDropExisting' => 'boolean',
        ];
    }

    protected function createDatabaseIfMissing(): void
    {
        if ($this->dbDriver === 'mysql') {
            $this->createMysqlDatabase();

            return;
        }

        $this->createPostgresDatabase();
    }

    protected function createMysqlDatabase(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            throw new \RuntimeException('The PHP pdo_mysql extension is not enabled.');
        }

        $pdo = new PDO(
            "mysql:host={$this->dbHost};port={$this->dbPort};charset=utf8mb4",
            $this->dbUsername,
            $this->dbPassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        $database = str_replace('`', '``', $this->dbDatabase);
        $exists = $pdo->prepare('select schema_name from information_schema.schemata where schema_name = :database');
        $exists->execute(['database' => $this->dbDatabase]);
        $this->dbExists = $exists->fetchColumn() !== false;

        if ($this->dbExists && ! $this->dbDropExisting) {
            throw new \RuntimeException("Database '{$this->dbDatabase}' already exists. Confirm that you want to drop and recreate it before continuing.");
        }

        if ($this->dbExists) {
            $pdo->exec("DROP DATABASE `{$database}`");
        }

        $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    protected function createPostgresDatabase(): void
    {
        if (! extension_loaded('pdo_pgsql')) {
            throw new \RuntimeException('The PHP pdo_pgsql extension is not enabled.');
        }

        $pdo = new PDO(
            "pgsql:host={$this->dbHost};port={$this->dbPort};dbname=postgres",
            $this->dbUsername,
            $this->dbPassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        $exists = $pdo->prepare('select 1 from pg_database where datname = :database');
        $exists->execute(['database' => $this->dbDatabase]);
        $this->dbExists = $exists->fetchColumn() !== false;

        if ($this->dbExists && ! $this->dbDropExisting) {
            throw new \RuntimeException("Database '{$this->dbDatabase}' already exists. Confirm that you want to drop and recreate it before continuing.");
        }

        $database = str_replace('"', '""', $this->dbDatabase);

        if ($this->dbExists) {
            $terminate = $pdo->prepare('select pg_terminate_backend(pid) from pg_stat_activity where datname = :database and pid <> pg_backend_pid()');
            $terminate->execute(['database' => $this->dbDatabase]);
            $pdo->exec("DROP DATABASE \"{$database}\"");
        }

        $pdo->exec("CREATE DATABASE \"{$database}\" ENCODING 'UTF8'");
    }

    protected function configureRuntimeDatabase(): void
    {
        config([
            'database.default' => $this->dbDriver,
            "database.connections.{$this->dbDriver}.host" => $this->dbHost,
            "database.connections.{$this->dbDriver}.port" => $this->dbPort,
            "database.connections.{$this->dbDriver}.database" => $this->dbDatabase,
            "database.connections.{$this->dbDriver}.username" => $this->dbUsername,
            "database.connections.{$this->dbDriver}.password" => $this->dbPassword,
            'session.driver' => 'database',
            'cache.default' => 'database',
            'queue.default' => 'database',
        ]);

        DB::purge($this->dbDriver);
        DB::reconnect($this->dbDriver);
    }

    /**
     * @return array<string, string>
     */
    protected function ldapRules(): array
    {
        return [
            'ldapHost' => 'required|string|max:255',
            'ldapPort' => 'required|integer|min:1|max:65535',
            'ldapBaseDn' => 'required|string|max:500',
            'ldapUsername' => 'required|string|max:500',
            'ldapPassword' => 'required|string|max:500',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mailRules(): array
    {
        return [
            'mailHost' => 'required|string|max:255',
            'mailPort' => 'required|integer|min:1|max:65535',
            'mailUsername' => 'nullable|string|max:255',
            'mailPassword' => 'nullable|string|max:500',
            'mailEncryption' => 'nullable|in:smtp,smtps',
            'mailFromAddress' => 'required|email|max:255',
            'mailFromName' => 'required|string|max:255',
            'mailTestTo' => 'required|email|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function smsRules(): array
    {
        return [
            'smsEndpoint' => 'required|url|max:500',
            'smsToken' => 'required|string|max:1000',
            'smsFrom' => 'nullable|string|max:50',
            'smsTestTo' => 'required|string|max:50',
        ];
    }
}
