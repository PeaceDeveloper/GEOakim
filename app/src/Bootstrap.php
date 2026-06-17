<?php

declare(strict_types=1);

namespace App;

use App\Http\Router;
use App\Infrastructure\Mongo\MongoConnection;

final class Bootstrap
{
    public static function init(): void
    {
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => self::isHttps(),
            ]);
            session_start();
        }
    }

    public static function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return (string) $value;
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        return strtolower($proto) === 'https';
    }

    public static function baseUrl(): string
    {
        $scheme = self::isHttps() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    public static function view(string $template, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        $viewsPath = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!is_file($viewsPath)) {
            http_response_code(500);
            echo 'View not found';
            return;
        }
        require $viewsPath;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public static function mongo(): MongoConnection
    {
        return MongoConnection::getInstance();
    }

    public static function router(): Router
    {
        return require dirname(__DIR__) . '/routes.php';
    }
}
