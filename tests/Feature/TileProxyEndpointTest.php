<?php

namespace Laragiser\Mapping\Tests\Feature;

use Tests\TestCase;

class TileProxyEndpointTest extends TestCase
{
    public function test_tile_proxy_serves_valid_image_with_cors_headers(): void
    {
        // Request tile OpenStreetMap zoom 12
        $response = $this->get('/api/mapping/tile/osm/12/3239/2054');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertStringStartsWith('image/', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_tile_proxy_options_preflight(): void
    {
        $response = $this->call('OPTIONS', '/api/mapping/tile/osm/12/3239/2054');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_tile_proxy_rejects_illegal_layer_with_empty_fallback(): void
    {
        $response = $this->get('/api/mapping/tile/unregistered_illegal_layer/12/3239/2054');

        // Harus mengembalikan 200 dengan transparent fallback tile, bukan 500 error
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }
}
