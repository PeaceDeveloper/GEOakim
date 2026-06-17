<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use App\Bootstrap;
use App\Container;
use App\Support\Csrf;
use RuntimeException;

final class AdminLinksController
{
    public function index(): void
    {
        $links = Container::get()->inviteLinkService()->listForAdmin();
        Bootstrap::json(['data' => $links]);
    }

    public function store(): void
    {
        if (!Csrf::validateRequest()) {
            Bootstrap::json(['error' => 'CSRF inválido'], 403);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $user = Container::get()->authService()->requireUser();

        try {
            $link = Container::get()->inviteLinkService()->create(
                (string) ($payload['name'] ?? ''),
                (string) ($payload['meeting_url'] ?? ''),
                (string) ($payload['expires_at'] ?? ''),
                $user['user_id'],
            );
            Bootstrap::json(['data' => $link], 201);
        } catch (RuntimeException $e) {
            Bootstrap::json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(string $uid): void
    {
        if (!Csrf::validateRequest()) {
            Bootstrap::json(['error' => 'CSRF inválido'], 403);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $link = Container::get()->inviteLinkService()->update(
                $uid,
                (string) ($payload['name'] ?? ''),
                (string) ($payload['meeting_url'] ?? ''),
                (string) ($payload['expires_at'] ?? ''),
            );
            Bootstrap::json(['data' => $link]);
        } catch (RuntimeException $e) {
            Bootstrap::json(['error' => $e->getMessage()], 422);
        }
    }

    public function revoke(string $uid): void
    {
        if (!Csrf::validateRequest()) {
            Bootstrap::json(['error' => 'CSRF inválido'], 403);
            return;
        }

        try {
            $link = Container::get()->inviteLinkService()->revoke($uid);
            Bootstrap::json(['data' => $link]);
        } catch (RuntimeException $e) {
            Bootstrap::json(['error' => $e->getMessage()], 422);
        }
    }
}
