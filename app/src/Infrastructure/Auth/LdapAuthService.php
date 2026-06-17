<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Bootstrap;
use RuntimeException;

final class LdapAuthService
{
    public function authenticate(string $username, string $password): array
    {
        $username = trim(strtolower($username));
        $password = (string) $password;

        if ($username === '' || $password === '') {
            throw new RuntimeException('Credenciais inválidas');
        }

        if (str_contains($username, '@')) {
            $username = strstr($username, '@', true) ?: $username;
        }

        $host = Bootstrap::env('AD_MP_URL', 'mpes.gov.br');
        $domain = Bootstrap::env('AD_MP_DOMAIN', '@mpes.gov.br');
        $useSsl = filter_var(Bootstrap::env('AD_MP_USE_SSL', 'false'), FILTER_VALIDATE_BOOLEAN);
        $uri = ($useSsl ? 'ldaps://' : 'ldap://') . $host;

        $connection = @ldap_connect($uri);
        if ($connection === false) {
            throw new RuntimeException('Serviço de autenticação indisponível');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        $upn = $username . $domain;
        $bound = @ldap_bind($connection, $upn, $password);
        if (!$bound) {
            throw new RuntimeException('Credenciais inválidas');
        }

        $displayName = $username;
        $baseDn = Bootstrap::env('AD_MP_BASE_DN', 'dc=mpes,dc=gov,dc=br');
        $filter = '(sAMAccountName=' . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . ')';
        $search = @ldap_search($connection, $baseDn, $filter, ['displayName', 'cn']);
        if ($search !== false) {
            $entries = ldap_get_entries($connection, $search);
            if (($entries['count'] ?? 0) > 0) {
                $displayName = $entries[0]['displayname'][0] ?? $entries[0]['cn'][0] ?? $username;
            }
        }

        ldap_unbind($connection);

        return [
            'user_id' => $username,
            'display_name' => $displayName,
        ];
    }
}
