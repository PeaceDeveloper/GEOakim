<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Bootstrap;

final class RequireAuthMiddleware
{
    public static function handle(string $requestedPath): void
    {
        if (!empty($_SESSION['user_id'])) {
            $idleLimit = 8 * 3600;
            $last = $_SESSION['authenticated_at'] ?? 0;
            if (time() - (int) $last > $idleLimit) {
                session_destroy();
            } else {
                return;
            }
        }

        if (str_starts_with($requestedPath, '/api/')) {
            Bootstrap::json(['error' => 'Não autenticado'], 401);
            exit;
        }

        $redirect = urlencode($requestedPath);
        Bootstrap::redirect('/admin/login?redirect=' . $redirect);
    }
}
