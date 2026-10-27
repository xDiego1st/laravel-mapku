<?php

namespace Laragiser\Mapping\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TileProxyService
{
    /**
     * Konfigurasi upstream tile layer yang didukung.
     */
    protected array $layers = [
        'osm' => [
            'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'subdomains' => ['a', 'b', 'c'],
            'ext' => 'png',
        ],
        'google_satellite' => [
            'url' => 'https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
            'subdomains' => ['0', '1', '2', '3'],
            'ext' => 'png',
        ],
        'google_hybrid' => [
            'url' => 'https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
            'subdomains' => ['0', '1', '2', '3'],
            'ext' => 'png',
        ],
        'google_road' => [
            'url' => 'https://mt{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
            'subdomains' => ['0', '1', '2', '3'],
            'ext' => 'png',
        ],
        'esri_satellite' => [
            'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            'subdomains' => [],
            'ext' => 'jpg',
        ],
        'dark_mode' => [
            'url' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
            'subdomains' => ['a', 'b', 'c', 'd'],
            'ext' => 'png',
        ],
        'topography' => [
            'url' => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            'subdomains' => ['a', 'b', 'c'],
            'ext' => 'png',
        ],
        'drone_layer' => [
            'url' => 'https://clarity.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            'subdomains' => [],
            'ext' => 'jpg',
        ],
    ];

    /**
     * Ambil tile gambar berdasarkan layer, zoom, x, y dengan caching server-side.
     */
    public function getTile(string $layer, int $z, int $x, int $y): ?array
    {
        if (!isset($this->layers[$layer])) {
            return null;
        }

        // Validasi batas koordinat tile standar Web Mercator
        $maxTile = pow(2, $z);
        if ($z < 0 || $z > 22 || $x < 0 || $x >= $maxTile || $y < 0 || $y >= $maxTile) {
            return null;
        }

        $config = $this->layers[$layer];
        $subdomains = $config['subdomains'];
        $urlTemplate = $config['url'];

        // Pilih subdomain rotasi round-robin
        $subdomain = '';
        if (!empty($subdomains)) {
            $idx = ($x + $y) % count($subdomains);
            $subdomain = $subdomains[$idx];
        }

        $tileUrl = str_replace(
            ['{s}', '{z}', '{x}', '{y}'],
            [$subdomain, $z, $x, $y],
            $urlTemplate
        );

        $ttl = (int) Config::get('mapping.tile_proxy.cache_ttl_seconds', 604800);
        $cacheStore = Config::get('mapping.tile_proxy.cache_store');
        $cacheKey = "mapping_tile_{$layer}_{$z}_{$x}_{$y}";

        $cache = $cacheStore ? Cache::store($cacheStore) : Cache::store();

        return $cache->remember($cacheKey, $ttl, function () use ($tileUrl, $config) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(6)
                    ->withHeaders([
                        'User-Agent' => Config::get('mapping.tile_proxy.user_agent', 'Mozilla/5.0 (compatible; LaravelMappingTileProxy/1.0)'),
                        'Accept' => 'image/webp,image/png,image/jpeg,image/*',
                    ])
                    ->get($tileUrl);

                if (!$response->successful()) {
                    return null;
                }

                return [
                    'body' => base64_encode($response->body()),
                    'content_type' => $response->header('Content-Type') ?? ($config['ext'] === 'jpg' ? 'image/jpeg' : 'image/png'),
                ];
            } catch (\Throwable $e) {
                Log::warning("TileProxy fetch failed: {$tileUrl} - " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Gambar transparan 1x1 PNG fallback ketika upstream gagal.
     */
    public function getEmptyTile(): array
    {
        return [
            'body' => base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')),
            'content_type' => 'image/png',
        ];
    }
}
