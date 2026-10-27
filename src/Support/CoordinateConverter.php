<?php

namespace Laragiser\Mapping\Support;

/**
 * CoordinateConverter
 *
 * Utilitas geospasial presisi tinggi untuk konversi koordinat antara:
 * - UTM (Universal Transverse Mercator) Zone 47N / zona lain
 * - WGS84 Geodetik (Latitude, Longitude dalam Derajat Desimal)
 */
class CoordinateConverter
{
    /**
     * Konversi koordinat UTM ke Latitude & Longitude (WGS84 Derajat Desimal).
     *
     * @param float $easting Easting dalam meter (misal: 772450.0)
     * @param float $northing Northing dalam meter (misal: 56120.0)
     * @param int $zone Nomor zona UTM (default: 47 untuk Riau/Sumatera)
     * @param bool $northernHemisphere Apakah belahan bumi utara (default: true)
     * @return array [lng, lat] format standar GeoJSON/MapLibre (longitude dulu, kemudian latitude)
     */
    public static function utmToLatLng(
        float $easting,
        float $northing,
        int $zone = 47,
        bool $northernHemisphere = true
    ): array {
        $a = 6378137.0; // Semi-major axis WGS84
        $f = 1 / 298.257223563; // Flattening
        $b = $a * (1 - $f);
        $e = sqrt(1 - ($b / $a) ** 2);
        $e1sq = ($e * $e) / (1 - $e * $e);

        $k0 = 0.9996;
        $x = $easting - 500000.0; // Hapus false easting
        $y = $northernHemisphere ? $northing : $northing - 10000000.0;

        $m = $y / $k0;
        $mu = $m / ($a * (1 - $e ** 2 / 4 - 3 * $e ** 4 / 64 - 5 * $e ** 6 / 256));

        $e1 = (1 - sqrt(1 - $e ** 2)) / (1 + sqrt(1 - $e ** 2));

        $phi1 = $mu + (3 * $e1 / 2 - 27 * $e1 ** 3 / 32) * sin(2 * $mu)
            + (21 * $e1 ** 2 / 16 - 55 * $e1 ** 4 / 32) * sin(4 * $mu)
            + (151 * $e1 ** 3 / 96) * sin(6 * $mu);

        $n1 = $a / sqrt(1 - $e ** 2 * sin($phi1) ** 2);
        $t1 = tan($phi1) ** 2;
        $c1 = $e1sq * cos($phi1) ** 2;
        $r1 = $a * (1 - $e ** 2) / (1 - $e ** 2 * sin($phi1) ** 2) ** 1.5;
        $d = $x / ($n1 * $k0);

        $lat = $phi1 - ($n1 * tan($phi1) / $r1) * (
            $d ** 2 / 2 - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 ** 2 - 9 * $e1sq) * $d ** 4 / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 ** 2 - 252 * $e1sq - 3 * $c1 ** 2) * $d ** 6 / 720
        );

        $lon0 = ($zone - 1) * 6 - 180 + 3; // Meridian sentral
        $lon = deg2rad($lon0) + (
            $d - (1 + 2 * $t1 + $c1) * $d ** 3 / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 ** 2 + 8 * $e1sq + 24 * $t1 ** 2) * $d ** 5 / 120
        ) / cos($phi1);

        $latDeg = rad2deg($lat);
        $lngDeg = rad2deg($lon);

        return [round($lngDeg, 7), round($latDeg, 7)];
    }

    /**
     * Konversi koordinat Latitude & Longitude ke UTM.
     *
     * @param float $lat Latitude dalam derajat desimal
     * @param float $lng Longitude dalam derajat desimal
     * @param int|null $forceZone Zona UTM tertentu (opsional, jika null dihitung otomatis)
     * @return array ['easting' => float, 'northing' => float, 'zone' => int, 'hemisphere' => string]
     */
    public static function latLngToUtm(float $lat, float $lng, ?int $forceZone = null): array
    {
        $zone = $forceZone ?? (int) floor(($lng + 180) / 6) + 1;
        $lon0 = ($zone - 1) * 6 - 180 + 3;

        $a = 6378137.0;
        $f = 1 / 298.257223563;
        $e = sqrt(2 * $f - $f * $f);
        $k0 = 0.9996;

        $phi = deg2rad($lat);
        $lambda = deg2rad($lng);
        $lambda0 = deg2rad($lon0);

        $ePrimeSq = ($e * $e) / (1 - $e * $e);
        $n = $a / sqrt(1 - $e * $e * sin($phi) * sin($phi));
        $t = tan($phi) * tan($phi);
        $c = $ePrimeSq * cos($phi) * cos($phi);
        $al = cos($phi) * ($lambda - $lambda0);

        $m = $a * (
            (1 - $e * $e / 4 - 3 * $e ** 4 / 64 - 5 * $e ** 6 / 256) * $phi
            - (3 * $e * $e / 8 + 3 * $e ** 4 / 32 + 45 * $e ** 6 / 1024) * sin(2 * $phi)
            + (15 * $e ** 4 / 256 + 45 * $e ** 6 / 1024) * sin(4 * $phi)
            - (35 * $e ** 6 / 3072) * sin(6 * $phi)
        );

        $easting = $k0 * $n * (
            $al + (1 - $t + $c) * ($al ** 3) / 6
            + (5 - 18 * $t + $t * $t + 72 * $c - 58 * $ePrimeSq) * ($al ** 5) / 120
        ) + 500000.0;

        $northing = $k0 * (
            $m + $n * tan($phi) * (
                ($al ** 2) / 2
                + (5 - $t + 9 * $c + 4 * $c * $c) * ($al ** 4) / 24
                + (61 - 58 * $t + $t * $t + 600 * $c - 330 * $ePrimeSq) * ($al ** 6) / 720
            )
        );

        if ($lat < 0) {
            $northing += 10000000.0;
        }

        return [
            'easting' => round($easting, 2),
            'northing' => round($northing, 2),
            'zone' => $zone,
            'hemisphere' => $lat >= 0 ? 'N' : 'S',
        ];
    }
}
