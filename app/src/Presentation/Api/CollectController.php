<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use App\Bootstrap;
use App\Container;
use RuntimeException;

final class CollectController
{
    public function store(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $linkUid = (string) ($payload['link_uid'] ?? '');

        if ($linkUid === '') {
            Bootstrap::json(['error' => 'link_uid obrigatório'], 422);
            return;
        }

        try {
            $result = Container::get()->collectionService()->collect($linkUid, $payload);
            Bootstrap::json($result);
        } catch (RuntimeException $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 400;
            Bootstrap::json(['error' => $e->getMessage()], $code);
        }
    }
}
