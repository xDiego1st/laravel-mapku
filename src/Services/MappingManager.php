<?php

namespace Laragiser\Mapping\Services;

use Illuminate\Support\Facades\Config;

class MappingManager
{
    public function __construct(
        protected TileProxyService $tileProxyService,
        protected BoundaryService $boundaryService
    ) {}

    public function getDefaultCenter(): array
    {
        return Config::get('mapping.default_map.center', ['lat' => 0.507068, 'lng' => 101.447779]);
    }

    public function getDefaultZoom(): int
    {
        return (int) Config::get('mapping.default_map.default_zoom', 12);
    }

    public function getTileLayers(): array
    {
        return Config::get('mapping.tile_layers', []);
    }

    public function getBoundaryConfig(): array
    {
        return Config::get('mapping.boundary', [
            'enabled' => true,
            'name' => 'Kota Pekanbaru',
            'source' => 'resources/geojson/pekanbaru.geojson',
            'fit_bounds' => true,
        ]);
    }

    public function tileProxy(): TileProxyService
    {
        return $this->tileProxyService;
    }

    public function boundary(): BoundaryService
    {
        return $this->boundaryService;
    }
}
