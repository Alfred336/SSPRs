<?php

namespace App\Services\Sspr;

use RuntimeException;

class ActiveDirectoryPasswordBroker
{
    /**
     * @return array{dn: string, display_name: string, email: ?string, phone: ?string}
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
            ['distinguishedName', 'displayName', 'cn', 'mail', 'mobile', 'telephoneNumber'],
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

        $entry = $entries[0];

        return [
            'dn' => (string) ($entry['distinguishedname'][0] ?? ''),
            'display_name' => (string) ($entry['displayname'][0] ?? $entry['cn'][0] ?? 'User'),
            'email' => $entry['mail'][0] ?? null,
            'phone' => $entry['mobile'][0] ?? $entry['telephonenumber'][0] ?? null,
        ];
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
}
