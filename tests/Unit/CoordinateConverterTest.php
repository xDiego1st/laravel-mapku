<?php

namespace Laragiser\Mapping\Tests\Unit;

use Laragiser\Mapping\Support\CoordinateConverter;
use PHPUnit\Framework\TestCase;

class CoordinateConverterTest extends TestCase
{
    /**
     * Uji konversi UTM Zone 47N ke WGS84 Lat/Lng.
     * Lokasi pengujian: Kota Pekanbaru (Easting ~772450, Northing ~56120).
     */
    public function test_utm_to_lat_lng_conversion_accuracy(): void
    {
        $easting = 772450.0;
        $northing = 56120.0;
        $zone = 47;

        [$lng, $lat] = CoordinateConverter::utmToLatLng($easting, $northing, $zone, true);

        // Pekanbaru berada di sekitar Lat 0.507, Lng 101.447
        $this->assertEqualsWithDelta(101.447, $lng, 0.05, 'Longitude harus berada di sekitar Pekanbaru (101.44°E)');
        $this->assertEqualsWithDelta(0.507, $lat, 0.05, 'Latitude harus berada di sekitar Pekanbaru (0.50°N)');
    }

    /**
     * Uji konversi bolak-balik (Round-trip consistency): LatLng -> UTM -> LatLng.
     */
    public function test_round_trip_coordinate_conversion(): void
    {
        $initialLat = 0.507068;
        $initialLng = 101.447779;

        $utm = CoordinateConverter::latLngToUtm($initialLat, $initialLng, 47);

        $this->assertArrayHasKey('easting', $utm);
        $this->assertArrayHasKey('northing', $utm);
        $this->assertEquals(47, $utm['zone']);
        $this->assertEquals('N', $utm['hemisphere']);

        [$convertedLng, $convertedLat] = CoordinateConverter::utmToLatLng($utm['easting'], $utm['northing'], 47, true);

        $this->assertEqualsWithDelta($initialLng, $convertedLng, 0.0001, 'Round-trip Longitude harus presisi');
        $this->assertEqualsWithDelta($initialLat, $convertedLat, 0.0001, 'Round-trip Latitude harus presisi');
    }
}
