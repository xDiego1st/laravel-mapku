<?php

namespace Laragiser\Mapping\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BoundaryService
{
    /**
     * Dapatkan data GeoJSON batas wilayah berdasarkan konfigurasi atau path custom.
     */
    public function getBoundaryGeoJson(?string $customSource = null, string $level = 'kota'): ?array
    {
        $level = strtolower($level);
        if ($customSource) {
            $source = $customSource;
        } else {
            $source = match ($level) {
                'kecamatan' => Config::get('mapping.boundary.kecamatan_source', 'resources/geojson/kecamatan.geojson'),
                'kelurahan' => Config::get('mapping.boundary.kelurahan_source', 'resources/geojson/kelurahan.geojson'),
                default => Config::get('mapping.boundary.source', 'resources/geojson/pekanbaru.geojson'),
            };
        }
        $resolvedPath = $this->resolvePath($source);

        if (!$resolvedPath || !file_exists($resolvedPath)) {
            Log::warning("Boundary GeoJSON tidak ditemukan pada path: {$source}");
            return null;
        }

        try {
            $content = file_get_contents($resolvedPath);
            $json = json_decode($content, true);

            if (!is_array($json) || (!isset($json['type']) && !isset($json['features']))) {
                Log::warning("Format GeoJSON pada {$resolvedPath} tidak valid.");
                return null;
            }

            // Normalisasi jika berformat single Feature ke FeatureCollection
            if (isset($json['type']) && $json['type'] === 'Feature') {
                $json = [
                    'type' => 'FeatureCollection',
                    'features' => [$json],
                ];
            }

            return $json;
        } catch (\Throwable $e) {
            Log::error("Gagal membaca boundary GeoJSON: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hitung ETag untuk respons GeoJSON.
     */
    public function getEtag(array $geoJson): string
    {
        return '"' . md5(json_encode($geoJson)) . '"';
    }

    /**
     * Resolusi path file GeoJSON (mendukung absolute, relative root, dan package fallback).
     */
    public function resolvePath(string $path): ?string
    {
        // 1. Cek absolute path
        if (File::exists($path)) {
            return $path;
        }

        // 2. Cek relative terhadap base_path host application
        $baseRelative = base_path($path);
        if (File::exists($baseRelative)) {
            return $baseRelative;
        }

        // 3. Cek relative terhadap folder database/data (misal file asli di repo)
        $dbDataPath = base_path('database/data/' . basename($path));
        if (File::exists($dbDataPath)) {
            return $dbDataPath;
        }

        // 4. Cek fallback di dalam package
        $packageFallback = __DIR__ . '/../../resources/geojson/' . basename($path);
        if (File::exists($packageFallback)) {
            return $packageFallback;
        }

        $defaultPekanbaru = __DIR__ . '/../../resources/geojson/pekanbaru.geojson';
        if (File::exists($defaultPekanbaru)) {
            return $defaultPekanbaru;
        }

        return null;
    }
}
