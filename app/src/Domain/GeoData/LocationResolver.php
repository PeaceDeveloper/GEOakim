<?php

declare(strict_types=1);

namespace App\Domain\GeoData;

final class LocationResolver
{
  /** @param array<string, mixed> $clientData */
    public function resolve(array $clientData): array
    {
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
                'geo' => true,
            ];
        }

        return [
            'latitude' => null,
            'longitude' => null,
            'accuracy' => null,
            'location_type' => 'none',
            'location_source' => null,
            'location_label' => null,
            'geo' => false,
        ];
    }
}
