<?php

declare(strict_types=1);

namespace App\Domain\InviteLink;

use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\BSON\UTCDateTime;

final class InviteLinkRepository
{
    public function __construct(private MongoConnection $mongo)
    {
    }

    public function insert(array $data): void
    {
        $this->mongo->collection('invite_links')->insertOne($data);
    }

    public function findByUid(string $uid): ?array
    {
        $doc = $this->mongo->collection('invite_links')->findOne(['uid' => $uid]);
        return $doc ? $this->map($doc) : null;
    }

  /** @return array<int, array<string, mixed>> */
    public function findAll(): array
    {
        $cursor = $this->mongo->collection('invite_links')->find([], [
            'sort' => ['created_at' => -1],
        ]);

        $rows = [];
        foreach ($cursor as $doc) {
            $rows[] = $this->map($doc);
        }
        return $rows;
    }

    public function updateByUid(string $uid, array $fields): bool
    {
        $result = $this->mongo->collection('invite_links')->updateOne(
            ['uid' => $uid],
            ['$set' => $fields]
        );
        return $result->getModifiedCount() > 0 || $result->getMatchedCount() > 0;
    }

  /** @param array<string, mixed> $doc */
    private function map(array $doc): array
    {
        return [
            'uid' => $doc['uid'],
            'name' => $doc['name'] ?? $doc['label'] ?? '',
            'meeting_url' => $doc['meeting_url'],
            'expires_at' => $this->dateToString($doc['expires_at'] ?? null),
            'revoked_at' => $this->dateToString($doc['revoked_at'] ?? null),
            'created_by' => $doc['created_by'] ?? '',
            'created_at' => $this->dateToString($doc['created_at'] ?? null),
            'updated_at' => $this->dateToString($doc['updated_at'] ?? null),
        ];
    }

    private function dateToString(mixed $value): ?string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->format('Y-m-d H:i:s');
        }
        return is_string($value) ? $value : null;
    }
}
