<?php

declare(strict_types=1);

namespace App\Application;

use App\Bootstrap;
use App\Domain\InviteLink\InviteLinkRepository;
use App\Support\UrlValidator;
use App\Support\Uuid;
use DateTimeImmutable;
use MongoDB\BSON\UTCDateTime;
use RuntimeException;

final class InviteLinkService
{
    public function __construct(private InviteLinkRepository $repository)
    {
    }

  /** @return array<string, mixed> */
    public function create(string $name, string $meetingUrl, string $expiresAt, string $createdBy): array
    {
        $name = trim($name);
        $meetingUrl = trim($meetingUrl);

        if ($name === '') {
            throw new RuntimeException('Nome é obrigatório');
        }
        if (!UrlValidator::isAllowedMeetingUrl($meetingUrl)) {
            throw new RuntimeException('URL da reunião inválida');
        }

        $expires = new DateTimeImmutable($expiresAt);
        if ($expires <= new DateTimeImmutable()) {
            throw new RuntimeException('Data de expiração deve ser futura');
        }

        $uid = Uuid::v4();
        $now = new UTCDateTime();

        $doc = [
            'uid' => $uid,
            'name' => $name,
            'meeting_url' => $meetingUrl,
            'expires_at' => new UTCDateTime($expires->getTimestamp() * 1000),
            'revoked_at' => null,
            'created_by' => $createdBy,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->repository->insert($doc);

        return $this->repository->findByUid($uid) + [
            'public_url' => Bootstrap::baseUrl() . '/' . $uid,
            'status' => 'active',
        ];
    }

  /** @return array<int, array<string, mixed>> */
    public function listForAdmin(): array
    {
        $links = $this->repository->findAll();
        return array_map(fn (array $link) => $this->enrich($link), $links);
    }

  /** @return array<string, mixed> */
    public function update(string $uid, string $name, string $meetingUrl, string $expiresAt): array
    {
        $link = $this->repository->findByUid($uid);
        if ($link === null) {
            throw new RuntimeException('Link não encontrado');
        }

        $name = trim($name);
        $meetingUrl = trim($meetingUrl);
        if ($name === '') {
            throw new RuntimeException('Nome é obrigatório');
        }
        if (!UrlValidator::isAllowedMeetingUrl($meetingUrl)) {
            throw new RuntimeException('URL da reunião inválida');
        }

        $expires = new DateTimeImmutable($expiresAt);
        if ($expires <= new DateTimeImmutable()) {
            throw new RuntimeException('Data de expiração deve ser futura');
        }

        $this->repository->updateByUid($uid, [
            'name' => $name,
            'meeting_url' => $meetingUrl,
            'expires_at' => new UTCDateTime($expires->getTimestamp() * 1000),
            'updated_at' => new UTCDateTime(),
        ]);

        return $this->enrich($this->repository->findByUid($uid));
    }

  /** @return array<string, mixed> */
    public function revoke(string $uid): array
    {
        $link = $this->repository->findByUid($uid);
        if ($link === null) {
            throw new RuntimeException('Link não encontrado');
        }

        $this->repository->updateByUid($uid, [
            'revoked_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ]);

        return $this->enrich($this->repository->findByUid($uid));
    }

  /** @return array<string, mixed>|null */
    public function resolvePublic(string $uid): ?array
    {
        $link = $this->repository->findByUid($uid);
        if ($link === null) {
            return null;
        }

        if (!empty($link['revoked_at'])) {
            return null;
        }

        $expiresAt = new DateTimeImmutable($link['expires_at']);
        if ($expiresAt <= new DateTimeImmutable()) {
            return null;
        }

        return $link;
    }

  /** @param array<string, mixed> $link */
    private function enrich(array $link): array
    {
        $status = 'active';
        if (!empty($link['revoked_at'])) {
            $status = 'revoked';
        } elseif (!empty($link['expires_at']) && new DateTimeImmutable($link['expires_at']) <= new DateTimeImmutable()) {
            $status = 'expired';
        }

        return $link + [
            'public_url' => Bootstrap::baseUrl() . '/' . $link['uid'],
            'status' => $status,
        ];
    }
}
