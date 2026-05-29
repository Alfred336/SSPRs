<?php

namespace App\Services\Sspr;

use RuntimeException;

class ActiveDirectoryPasswordBroker
{
    /**
     * @return array{dn: string, display_name: string, email: ?string, phone: ?string, username?: ?string, title?: ?string, department?: ?string}
     */
    public function findUserByEmailOrPhone(string $identifier): array
    {
        $connection = $this->bind(
            (string) config('sspr.ldap.username'),
            (string) config('sspr.ldap.password'),
        );

        $escaped = ldap_escape($identifier, '', LDAP_ESCAPE_FILTER);
        $filter = '(&'.
            '(objectClass=user)'.
            '(!(userAccountControl:1.2.840.113556.1.4.803:=2))'.
            '(|(mail='.$escaped.')(userPrincipalName='.$escaped.')(mobile='.$escaped.')(telephoneNumber='.$escaped.'))'.
        ')';

        $search = @ldap_search(
            $connection,
            (string) config('sspr.ldap.base_dn'),
            $filter,
            $this->profileAttributes(),
            0,
            2,
            (int) config('sspr.ldap.timeout', 5),
        );

        if ($search === false) {
            throw new RuntimeException('Unable to search Active Directory: '.ldap_error($connection));
        }

        $entries = ldap_get_entries($connection, $search);

        if (($entries['count'] ?? 0) < 1) {
            throw new RuntimeException('No matching Active Directory user was found.');
        }

        return $this->mapEntry($entries[0]);
    }

    /**
     * @return array{dn: string, display_name: string, email: ?string, phone: ?string, username: ?string, title: ?string, department: ?string}
     */
    public function authenticate(string $username, string $password): array
    {
        $serviceConnection = $this->bind(
            (string) config('sspr.ldap.username'),
            (string) config('sspr.ldap.password'),
        );

        $escaped = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
        $filter = '(&'.
            '(objectClass=user)'.
            '(!(userAccountControl:1.2.840.113556.1.4.803:=2))'.
            '(|(sAMAccountName='.$escaped.')(userPrincipalName='.$escaped.')(mail='.$escaped.'))'.
        ')';

        $search = @ldap_search(
            $serviceConnection,
            (string) config('sspr.ldap.base_dn'),
            $filter,
            $this->profileAttributes(),
            0,
            2,
            (int) config('sspr.ldap.timeout', 5),
        );

        if ($search === false) {
            throw new RuntimeException('Unable to search Active Directory: '.ldap_error($serviceConnection));
        }

        $entries = ldap_get_entries($serviceConnection, $search);

        if (($entries['count'] ?? 0) < 1) {
            throw new RuntimeException('Invalid credentials.');
        }

        $profile = $this->mapEntry($entries[0]);

        $this->bind($profile['dn'], $password);

        return $profile;
    }

    /**
     * @return array{dn: string, display_name: string, email: ?string, phone: ?string, username: ?string, title: ?string, department: ?string}
     */
    public function profile(string $distinguishedName): array
    {
        $connection = $this->bind(
            (string) config('sspr.ldap.username'),
            (string) config('sspr.ldap.password'),
        );

        $search = @ldap_read(
            $connection,
            $distinguishedName,
            '(objectClass=user)',
            $this->profileAttributes(),
            0,
            1,
            (int) config('sspr.ldap.timeout', 5),
        );

        if ($search === false) {
            throw new RuntimeException('Unable to read Active Directory profile: '.ldap_error($connection));
        }

        $entries = ldap_get_entries($connection, $search);

        if (($entries['count'] ?? 0) < 1) {
            throw new RuntimeException('Active Directory profile was not found.');
        }

        return $this->mapEntry($entries[0]);
    }

    /**
     * @param  array{email: string|null, phone: string|null}  $attributes
     */
    public function updateContactInfo(string $distinguishedName, array $attributes): void
    {
        $connection = $this->bind(
            (string) config('sspr.ldap.reset_username', config('sspr.ldap.username')),
            (string) config('sspr.ldap.reset_password', config('sspr.ldap.password')),
        );

        $changes = [];

        if (($attributes['email'] ?? null) !== null) {
            $changes['mail'] = [$attributes['email']];
        } elseif (array_key_exists('email', $attributes)) {
            @ldap_mod_del($connection, $distinguishedName, ['mail' => []]);
        }

        if (($attributes['phone'] ?? null) !== null) {
            $changes['mobile'] = [$attributes['phone']];
            $changes['telephoneNumber'] = [$attributes['phone']];
        } elseif (array_key_exists('phone', $attributes)) {
            @ldap_mod_del($connection, $distinguishedName, ['mobile' => []]);
            @ldap_mod_del($connection, $distinguishedName, ['telephoneNumber' => []]);
        }

        if ($changes === []) {
            return;
        }

        if (@ldap_mod_replace($connection, $distinguishedName, $changes) !== true) {
            throw new RuntimeException('Active Directory rejected the profile update: '.ldap_error($connection));
        }
    }

    public function resetPassword(string $distinguishedName, string $newPassword): void
    {
        $connection = $this->bind(
            (string) config('sspr.ldap.reset_username', config('sspr.ldap.username')),
            (string) config('sspr.ldap.reset_password', config('sspr.ldap.password')),
        );

        $entry = [
            'unicodePwd' => [$this->encodeActiveDirectoryPassword($newPassword)],
        ];

        if (@ldap_modify($connection, $distinguishedName, $entry) !== true) {
            throw new RuntimeException('Active Directory rejected the password reset: '.ldap_error($connection));
        }
    }

    /**
     * @return resource
     */
    protected function bind(string $username, string $password)
    {
        if (! extension_loaded('ldap')) {
            throw new RuntimeException('The PHP LDAP extension is not enabled.');
        }

        $host = (string) config('sspr.ldap.host');
        $port = (int) config('sspr.ldap.port', 389);
        $scheme = config('sspr.ldap.use_ssl') ? 'ldaps' : 'ldap';
        $connection = @ldap_connect("{$scheme}://{$host}", $port);

        if ($connection === false) {
            throw new RuntimeException('Unable to initialize LDAP connection.');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
            ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, (int) config('sspr.ldap.timeout', 5));
        }

        if (config('sspr.ldap.use_tls') && @ldap_start_tls($connection) !== true) {
            throw new RuntimeException('Unable to start LDAP TLS: '.ldap_error($connection));
        }

        if (@ldap_bind($connection, $username, $password) !== true) {
            throw new RuntimeException('Unable to bind to Active Directory: '.ldap_error($connection));
        }

        return $connection;
    }

    protected function encodeActiveDirectoryPassword(string $password): string
    {
        return mb_convert_encoding('"'.$password.'"', 'UTF-16LE', 'UTF-8');
    }

    /**
     * @return list<string>
     */
    protected function profileAttributes(): array
    {
        return [
            'distinguishedName',
            'displayName',
            'cn',
            'sAMAccountName',
            'userPrincipalName',
            'mail',
            'mobile',
            'telephoneNumber',
            'title',
            'department',
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array{dn: string, display_name: string, email: ?string, phone: ?string, username: ?string, title: ?string, department: ?string}
     */
    protected function mapEntry(array $entry): array
    {
        return [
            'dn' => (string) ($entry['distinguishedname'][0] ?? ''),
            'display_name' => (string) ($entry['displayname'][0] ?? $entry['cn'][0] ?? 'User'),
            'email' => $entry['mail'][0] ?? null,
            'phone' => $entry['mobile'][0] ?? $entry['telephonenumber'][0] ?? null,
            'username' => $entry['samaccountname'][0] ?? $entry['userprincipalname'][0] ?? null,
            'title' => $entry['title'][0] ?? null,
            'department' => $entry['department'][0] ?? null,
        ];
    }
}
