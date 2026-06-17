<?php

declare(strict_types=1);

namespace App\Application;

use App\Infrastructure\Auth\LdapAuthService;
use RuntimeException;

final class AuthService
{
    public function __construct(private LdapAuthService $ldap)
    {
    }

    public function login(string $username, string $password): void
    {
        $user = $this->ldap->authenticate($username, $password);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['authenticated_at'] = time();
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function currentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'user_id' => $_SESSION['user_id'],
            'display_name' => $_SESSION['display_name'] ?? $_SESSION['user_id'],
        ];
    }

    public function requireUser(): array
    {
        $user = $this->currentUser();
        if ($user === null) {
            throw new RuntimeException('Não autenticado');
        }
        return $user;
    }
}
