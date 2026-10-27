<?php

namespace Laragiser\Mapping\Tests\Feature;

use Tests\TestCase;

class BoundaryEndpointTest extends TestCase
{
    public function test_boundary_endpoint_returns_valid_geojson_and_etag(): void
    {
        $response = $this->get('/api/mapping/boundary');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertNotEmpty($response->headers->get('ETag'));

        $data = $response->json();
        $this->assertEquals('FeatureCollection', $data['type'] ?? null);
        $this->assertNotEmpty($data['features'] ?? []);
        $this->assertEquals('Kota Pekanbaru', $data['features'][0]['properties']['nm_dati2'] ?? null);
    }

    public function test_boundary_endpoint_returns_304_for_matching_etag(): void
    {
        $initial = $this->get('/api/mapping/boundary');
        $etag = $initial->headers->get('ETag');

        $cachedResponse = $this->withHeaders(['If-None-Match' => $etag])
            ->get('/api/mapping/boundary');

        $cachedResponse->assertStatus(304);
    }

    public function test_boundary_endpoint_supports_kecamatan_and_kelurahan_levels(): void
    {
        $responseKec = $this->get('/api/mapping/boundary/kecamatan');
        $responseKec->assertStatus(200);
        $responseKec->assertHeader('Content-Type', 'application/json');
        $dataKec = $responseKec->json();
        $this->assertEquals('FeatureCollection', $dataKec['type'] ?? null);
        $this->assertNotEmpty($dataKec['features'] ?? []);

        $responseKel = $this->get('/api/mapping/boundary/kelurahan');
        $responseKel->assertStatus(200);
        $responseKel->assertHeader('Content-Type', 'application/json');
        $dataKel = $responseKel->json();
        $this->assertEquals('FeatureCollection', $dataKel['type'] ?? null);
        $this->assertNotEmpty($dataKel['features'] ?? []);
    }
}
