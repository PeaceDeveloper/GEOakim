<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use App\Application\CollectPayloadNormalizer;
use App\Bootstrap;
use App\Container;
use RuntimeException;

final class CollectController
{
    public function __construct(private CollectPayloadNormalizer $normalizer = new CollectPayloadNormalizer())
    {
    }

    public function store(): void
    {
        $raw = json_decode(file_get_contents('php://input'), true) ?? [];
        $payload = $this->normalizer->normalize($raw);
        $linkUid = (string) ($payload['link_uid'] ?? '');

        if ($linkUid === '') {
            Bootstrap::json(['error' => 'invalid request'], 422);
            return;
        }

        try {
            $result = Container::get()->collectionService()->collect($linkUid, $payload);
            Bootstrap::json($result);
        } catch (RuntimeException $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 400;
            $message = $code >= 500 ? 'invalid request' : 'invalid request';
            Bootstrap::json(['error' => $message], $code);
        }
    }
}
