<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\BSON\UTCDateTime;

final class RateLimiter
{
    public function __construct(private MongoConnection $mongo)
    {
    }

    public function allow(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $collection = $this->mongo->collection('rate_limits');
        $now = time();
        $doc = $collection->findOne(['key' => $key]);

        if ($doc === null) {
            $collection->insertOne([
                'key' => $key,
                'count' => 1,
                'window_start' => $now,
                'expires_at' => new UTCDateTime(($now + $windowSeconds) * 1000),
            ]);
            return true;
        }

        $windowStart = (int) ($doc['window_start'] ?? 0);
        $count = (int) ($doc['count'] ?? 0);

        if ($now - $windowStart >= $windowSeconds) {
            $collection->updateOne(['key' => $key], [
                '$set' => [
                    'count' => 1,
                    'window_start' => $now,
                    'expires_at' => new UTCDateTime(($now + $windowSeconds) * 1000),
                ],
            ]);
            return true;
        }

        if ($count >= $maxAttempts) {
            return false;
        }

        $collection->updateOne(['key' => $key], ['$inc' => ['count' => 1]]);
        return true;
    }
}
