<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Bootstrap;
use App\Container;
use App\Support\Uuid;

final class JoinController
{
    public function show(string $uid): void
    {
        if (!Uuid::isValid($uid)) {
            $this->notFound();
            return;
        }

        $link = Container::get()->inviteLinkService()->resolvePublic($uid);
        if ($link === null) {
            $this->notFound();
            return;
        }

        Bootstrap::view('join/index', [
            'link' => $link,
            'link_uid' => $uid,
            'meeting_url' => $link['meeting_url'],
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        Bootstrap::view('errors/not_found');
    }
}
