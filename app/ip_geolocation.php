<?php

function isPrivateOrLocalIp(string $ip): bool {
    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '0:0:0:0:0:0:0:1') {
        return true;
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    return true;
}

function isIpGeoEnabled(): bool {
    $value = $_ENV['IP_GEO_ENABLED'] ?? null;
    if ($value === null || $value === '') {
        return true;
    }
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function resolveIpLocation(string $ip): ?array {
    if (!isIpGeoEnabled() || isPrivateOrLocalIp($ip)) {
        return null;
    }

    $provider = $_ENV['IP_GEO_PROVIDER'] ?? 'ip-api';
    if ($provider === '') {
        $provider = 'ip-api';
    }

    if ($provider === 'ip-api') {
        return resolveIpLocationViaIpApi($ip);
    }

    return null;
}

function resolveIpLocationViaIpApi(string $ip): ?array {
    $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,lat,lon,city,country';

    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'ignore_errors' => true,
            'header' => "User-Agent: GEOakim/1.0\r\n"
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
        return null;
    }

    if (!isset($data['lat'], $data['lon']) || !is_numeric($data['lat']) || !is_numeric($data['lon'])) {
        return null;
    }

    $city = trim($data['city'] ?? '');
    $country = trim($data['country'] ?? '');
    $label = trim($city . ($city && $country ? ', ' : '') . $country);

    return [
        'lat' => (float) $data['lat'],
        'lon' => (float) $data['lon'],
        'accuracy' => 50000,
        'label' => $label !== '' ? $label : null,
        'provider' => 'ip-api'
    ];
}

function resolveLocationFields(string $ip, array $clientData): array {
    $locationType = $clientData['location_type'] ?? null;
    $lat = $clientData['lat'] ?? null;
    $lon = $clientData['lon'] ?? null;
    $accuracy = $clientData['accuracy'] ?? null;

    $hasPreciseCoords = $locationType === 'precise'
        && is_numeric($lat)
        && is_numeric($lon);

    if ($hasPreciseCoords) {
        return [
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
            'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
            'location_type' => 'precise',
            'location_source' => 'gps',
            'location_label' => null,
            'geo' => true
        ];
    }

    $ipLocation = resolveIpLocation($ip);
    if ($ipLocation !== null) {
        return [
            'latitude' => $ipLocation['lat'],
            'longitude' => $ipLocation['lon'],
            'accuracy' => $ipLocation['accuracy'],
            'location_type' => 'approximate',
            'location_source' => 'ip',
            'location_label' => $ipLocation['label'],
            'geo' => true
        ];
    }

    return [
        'latitude' => null,
        'longitude' => null,
        'accuracy' => null,
        'location_type' => 'none',
        'location_source' => null,
        'location_label' => null,
        'geo' => false
    ];
}

?>
