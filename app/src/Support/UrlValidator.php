<?php

declare(strict_types=1);

namespace App\Support;

use App\Bootstrap;

final class UrlValidator
{
    public static function isAllowedMeetingUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $allowlist = Bootstrap::env('MEETING_URL_ALLOWLIST', 'teams.microsoft.com');
        $allowed = array_map('trim', explode(',', (string) $allowlist));

        foreach ($allowed as $pattern) {
            $pattern = strtolower($pattern);
            if ($host === $pattern) {
                return true;
            }
            if (str_starts_with($pattern, '*.') && str_ends_with($host, substr($pattern, 1))) {
                return true;
            }
        }

        return false;
    }
}
