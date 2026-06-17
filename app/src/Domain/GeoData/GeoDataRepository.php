<?php

declare(strict_types=1);

namespace App\Domain\GeoData;

use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\BSON\UTCDateTime;

final class GeoDataRepository
{
    public function __construct(private MongoConnection $mongo)
    {
    }

    public function insert(array $data): string
    {
        $data['created_at'] = new UTCDateTime();
        $result = $this->mongo->collection('geo_data')->insertOne($data);
        return (string) $result->getInsertedId();
    }

  /** @return array<int, array<string, mixed>> */
    public function findAll(int $limit = 1000, ?string $linkUid = null): array
    {
        $filter = [];
        if ($linkUid !== null && $linkUid !== '') {
            $filter['link_uid'] = $linkUid;
        }

        $cursor = $this->mongo->collection('geo_data')->find($filter, [
            'sort' => ['created_at' => -1],
            'limit' => $limit,
        ]);

        return $this->mapDocuments($cursor);
    }

  /** @return array<int, array<string, mixed>> */
    private function mapDocuments(iterable $cursor): array
    {
        $rows = [];
        foreach ($cursor as $document) {
            $rows[] = [
                'timestamp' => $document['timestamp'] ?? '',
                'ip' => $document['ip'] ?? '',
                'user_agent' => $document['user_agent'] ?? '',
                'gpu_vendor' => $document['gpu_vendor'] ?? '',
                'gpu_renderer' => $document['gpu_renderer'] ?? '',
                'screen' => $document['screen'] ?? '',
                'platform' => $document['platform'] ?? '',
                'lang' => $document['lang'] ?? '',
                'timezone' => $document['timezone'] ?? '',
                'latitude' => $document['latitude'] ?? null,
                'longitude' => $document['longitude'] ?? null,
                'accuracy' => $document['accuracy'] ?? null,
                'location_type' => $document['location_type'] ?? null,
                'location_source' => $document['location_source'] ?? null,
                'location_label' => $document['location_label'] ?? null,
                'geo' => $document['geo'] ?? false,
                'link_uid' => $document['link_uid'] ?? null,
                'link_name' => $document['link_name'] ?? null,
            ];
        }
        return $rows;
    }
}
