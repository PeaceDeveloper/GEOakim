<?php

declare(strict_types=1);

namespace App\Application;

use App\Bootstrap;
use App\Domain\GeoData\GeoDataRepository;
use App\Domain\GeoData\LocationResolver;
use App\Infrastructure\Security\RateLimiter;
use RuntimeException;

final class CollectionService
{
    public function __construct(
        private GeoDataRepository $geoDataRepository,
        private InviteLinkService $inviteLinkService,
        private LocationResolver $locationResolver,
        private RateLimiter $rateLimiter,
    ) {
    }

  /** @param array<string, mixed> $payload */
    public function collect(string $linkUid, array $payload): array
    {
        $link = $this->inviteLinkService->resolvePublic($linkUid);
        if ($link === null) {
            throw new RuntimeException('Link inválido ou expirado', 410);
        }

        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $limit = (int) (Bootstrap::env('COLLECT_RATE_LIMIT', '30') ?: '30');

        if (!$this->rateLimiter->allow('collect:ip:' . $ip, $limit, 60)) {
            throw new RuntimeException('Muitas requisições', 429);
        }
        if (!$this->rateLimiter->allow('collect:link:' . $linkUid, $limit * 2, 60)) {
            throw new RuntimeException('Muitas requisições', 429);
        }

        $location = $this->locationResolver->resolve($payload);

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'gpu_vendor' => $payload['gpu_vendor'] ?? 'N/A',
            'gpu_renderer' => $payload['gpu_renderer'] ?? 'N/A',
            'screen' => $payload['screen'] ?? 'N/A',
            'platform' => $payload['platform'] ?? 'N/A',
            'lang' => $payload['lang'] ?? 'N/A',
            'timezone' => $payload['timezone'] ?? 'N/A',
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'accuracy' => $location['accuracy'],
            'location_type' => $location['location_type'],
            'location_source' => $location['location_source'],
            'location_label' => $location['location_label'],
            'geo' => $location['geo'],
            'link_uid' => $linkUid,
            'link_name' => $link['name'],
            'collection_event' => $payload['collection_event'] ?? 'unknown',
        ];

        $id = $this->geoDataRepository->insert($entry);

        return ['status' => 'success', 'id' => $id, 'storage' => 'mongodb'];
    }
}
