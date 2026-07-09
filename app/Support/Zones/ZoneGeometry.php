<?php

namespace App\Support\Zones;

final class ZoneGeometry
{
    /**
     * @return array<int, array{lat: string, lng: string}>|null
     */
    public function parseCoordinateString(string $coordinates): ?array
    {
        $coordinates = trim($coordinates);

        if ($coordinates === '') {
            return null;
        }

        if (preg_match('/^POLYGON\s*\(\((.*)\)\)$/i', $coordinates, $matches) === 1) {
            return $this->parseWktPoints($matches[1]);
        }

        return $this->parseAliasPoints($coordinates);
    }

    public function normalizePolygon(string $coordinates): ?string
    {
        $points = $this->parseCoordinateString($coordinates);

        if ($points === null || count($points) < 3) {
            return null;
        }

        $points = $this->ensureClosed($points);
        $pairs = array_map(
            fn (array $point): string => "{$point['lat']} {$point['lng']}",
            $points,
        );

        return 'POLYGON(('.implode(',', $pairs).'))';
    }

    public function isMapAlias(string $coordinates): bool
    {
        return preg_match('/^\s*\([^()]+,[^()]+\)(?:\s*,\s*\([^()]+,[^()]+\))*\s*$/', $coordinates) === 1;
    }

    public function containsPoint(string $polygon, float $lat, float $lng): bool
    {
        $points = $this->parseCoordinateString($polygon);

        if ($points === null || count($points) < 4) {
            return false;
        }

        $inside = false;
        $pointCount = count($points);

        for ($i = 0, $j = $pointCount - 1; $i < $pointCount; $j = $i++) {
            $currentLat = (float) $points[$i]['lat'];
            $currentLng = (float) $points[$i]['lng'];
            $previousLat = (float) $points[$j]['lat'];
            $previousLng = (float) $points[$j]['lng'];

            if ($this->isPointOnSegment($lat, $lng, $currentLat, $currentLng, $previousLat, $previousLng)) {
                return false;
            }

            $intersects = (($currentLng > $lng) !== ($previousLng > $lng))
                && ($lat < ($previousLat - $currentLat) * ($lng - $currentLng) / ($previousLng - $currentLng) + $currentLat);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * @return array<int, array{lat: string, lng: string}>|null
     */
    private function parseAliasPoints(string $coordinates): ?array
    {
        preg_match_all(
            '/\(\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*\)/',
            $coordinates,
            $matches,
            PREG_SET_ORDER,
        );

        if ($matches === []) {
            return null;
        }

        return array_map(
            fn (array $match): array => ['lat' => $match[1], 'lng' => $match[2]],
            $matches,
        );
    }

    /**
     * @return array<int, array{lat: string, lng: string}>|null
     */
    private function parseWktPoints(string $coordinates): ?array
    {
        $points = [];

        foreach (explode(',', $coordinates) as $pair) {
            $parts = preg_split('/\s+/', trim($pair));

            if ($parts === false || count($parts) !== 2) {
                return null;
            }

            $points[] = ['lat' => $parts[0], 'lng' => $parts[1]];
        }

        return $points;
    }

    /**
     * @param  array<int, array{lat: string, lng: string}>  $points
     * @return array<int, array{lat: string, lng: string}>
     */
    private function ensureClosed(array $points): array
    {
        $first = $points[0];
        $last = $points[array_key_last($points)];

        if ($first['lat'] !== $last['lat'] || $first['lng'] !== $last['lng']) {
            $points[] = $first;
        }

        return $points;
    }

    private function isPointOnSegment(
        float $lat,
        float $lng,
        float $startLat,
        float $startLng,
        float $endLat,
        float $endLng,
    ): bool {
        $crossProduct = ($lng - $startLng) * ($endLat - $startLat) - ($lat - $startLat) * ($endLng - $startLng);

        if (abs($crossProduct) > 0.000000001) {
            return false;
        }

        return $lat >= min($startLat, $endLat)
            && $lat <= max($startLat, $endLat)
            && $lng >= min($startLng, $endLng)
            && $lng <= max($startLng, $endLng);
    }
}
