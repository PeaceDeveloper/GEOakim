<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = $_SESSION[self::KEY] ?? '';
        return is_string($token) && $token !== '' && hash_equals($sessionToken, $token);
    }

    public static function validateRequest(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return self::validate(is_string($token) ? $token : null);
    }
}
