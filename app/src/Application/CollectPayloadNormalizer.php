<?php

declare(strict_types=1);

namespace App\Application;

final class CollectPayloadNormalizer
{
  /** @param array<string, mixed> $raw */
    public function normalize(array $raw): array
    {
        $linkUid = (string) ($raw['u'] ?? '');
        if ($linkUid === '') {
            return [];
        }

        $eventMap = ['0' => 'page_load', '1' => 'click', 0 => 'page_load', 1 => 'click'];
        $ev = $raw['ev'] ?? null;
        $collectionEvent = $eventMap[$ev] ?? (is_string($ev) ? $ev : 'unknown');

        $lt = $raw['lt'] ?? null;
        $locationType = match ($lt) {
            1, '1' => 'precise',
            0, '0' => 'none',
            default => is_string($lt) ? $lt : 'none',
        };

        $normalized = [
            'link_uid' => $linkUid,
            'collection_event' => $collectionEvent,
            'location_type' => $locationType,
            'timestamp' => $raw['ts'] ?? null,
            'screen' => $raw['sr'] ?? 'N/A',
            'lang' => $raw['lg'] ?? 'N/A',
            'platform' => $raw['pf'] ?? 'N/A',
            'timezone' => $raw['tz'] ?? 'N/A',
            'gpu_vendor' => $raw['gv'] ?? 'N/A',
            'gpu_renderer' => $raw['gr'] ?? 'N/A',
        ];

        if ($locationType === 'precise') {
            $normalized['lat'] = $raw['a'] ?? null;
            $normalized['lon'] = $raw['o'] ?? null;
            $normalized['accuracy'] = $raw['ac'] ?? null;
        }

        return $normalized;
    }
}
