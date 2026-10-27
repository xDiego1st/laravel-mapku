<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Map Viewport
    |--------------------------------------------------------------------------
    | Koordinat pusat (center) dan zoom level default saat peta pertama kali diinisialisasi.
    | Default berpusat di Kota Pekanbaru, Riau.
    */
    'default_map' => [
        'center' => [
            'lat' => (float) env('MAPPING_DEFAULT_LAT', 0.507068),
            'lng' => (float) env('MAPPING_DEFAULT_LNG', 101.447779),
        ],
        'default_zoom' => (int) env('MAPPING_DEFAULT_ZOOM', 12),
        'min_zoom' => (int) env('MAPPING_MIN_ZOOM', 8),
        'max_zoom' => (int) env('MAPPING_MAX_ZOOM', 22),
        'bounds' => [
            'south_west' => [0.25, 101.1],
            'north_east' => [0.75, 101.8],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Batas Administratif Wilayah (Boundary Overlay)
    |--------------------------------------------------------------------------
    | Mengatur overlay batas administratif wilayah berbasis GeoJSON WGS84.
    | Anda dapat mengganti wilayah Pekanbaru ke wilayah lain (Kabupaten/Kota mana pun)
    | hanya dengan mengganti opsi 'source' ke file GeoJSON Anda.
    */
    'boundary' => [
        'enabled' => env('MAPPING_BOUNDARY_ENABLED', true),
        'name' => env('MAPPING_BOUNDARY_NAME', 'Kota Pekanbaru'),
        'source' => env('MAPPING_BOUNDARY_SOURCE', 'resources/geojson/pekanbaru.geojson'),
        'fit_bounds' => env('MAPPING_BOUNDARY_FIT_BOUNDS', true),
        'style' => [
            'fill_color' => env('MAPPING_BOUNDARY_FILL_COLOR', '#0284c7'),
            'fill_opacity' => (float) env('MAPPING_BOUNDARY_FILL_OPACITY', 0.08),
            'stroke_color' => env('MAPPING_BOUNDARY_STROKE_COLOR', '#38bdf8'),
            'stroke_width' => (int) env('MAPPING_BOUNDARY_STROKE_WIDTH', 2),
            'stroke_dasharray' => [2, 2],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tile Proxy & Server-Side Caching
    |--------------------------------------------------------------------------
    | Mengamankan tile request dari pemblokiran CORS & Webview pada Superapp.
    | Server Laravel bertindak sebagai reverse proxy yang mengunduh tile dari
    | upstream provider dan menyimpannya di cache lokal.
    */
    'tile_proxy' => [
        'enabled' => env('MAPPING_TILE_PROXY_ENABLED', true),
        'route_prefix' => 'api/mapping/tile',
        'cache_store' => env('MAPPING_CACHE_STORE', null), // null = default cache store
        'cache_ttl_seconds' => (int) env('MAPPING_TILE_TTL', 604800), // 7 hari
        'user_agent' => 'Mozilla/5.0 (compatible; LaravelMappingTileProxy/1.0)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lapisan Peta Dasar (7 Base Map Layers)
    |--------------------------------------------------------------------------
    | Koleksi penyedia tile standar dengan dukungan tile proxy internal.
    */
    'tile_layers' => [
        'osm' => [
            'name' => 'OpenStreetMap (Default)',
            'key' => 'osm',
            'is_default' => false,
            'url' => '/api/mapping/tile/osm/{z}/{x}/{y}',
            'maxZoom' => 19,
            'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> kontributor',
        ],
        'google_satellite' => [
            'name' => 'Google Satellite HD',
            'key' => 'google_satellite',
            'is_default' => true,
            'url' => '/api/mapping/tile/google_satellite/{z}/{x}/{y}',
            'maxZoom' => 20,
            'attribution' => '&copy; Google Maps Imagery',
        ],
        'google_hybrid' => [
            'name' => 'Google Hybrid',
            'key' => 'google_hybrid',
            'is_default' => false,
            'url' => '/api/mapping/tile/google_hybrid/{z}/{x}/{y}',
            'maxZoom' => 20,
            'attribution' => '&copy; Google Maps Satellite + Labels',
        ],
        'esri_satellite' => [
            'name' => 'Esri World Imagery',
            'key' => 'esri_satellite',
            'is_default' => false,
            'url' => '/api/mapping/tile/esri_satellite/{z}/{x}/{y}',
            'maxZoom' => 19,
            'attribution' => 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        ],
        'dark_mode' => [
            'name' => 'Dark Mode (CartoDB)',
            'key' => 'dark_mode',
            'is_default' => false,
            'url' => '/api/mapping/tile/dark_mode/{z}/{x}/{y}',
            'maxZoom' => 20,
            'attribution' => '&copy; <a href="https://carto.com/" target="_blank">CARTO</a> &copy; OpenStreetMap',
        ],
        'topography' => [
            'name' => 'Topografi (OpenTopoMap)',
            'key' => 'topography',
            'is_default' => false,
            'url' => '/api/mapping/tile/topography/{z}/{x}/{y}',
            'maxZoom' => 17,
            'attribution' => 'Map data: &copy; OpenStreetMap, SRTM | Style: OpenTopoMap (CC-BY-SA)',
        ],
        'drone_layer' => [
            'name' => 'Drone Layer (Ortofoto)',
            'key' => 'drone_layer',
            'is_default' => false,
            'url' => '/api/mapping/tile/drone_layer/{z}/{x}/{y}',
            'maxZoom' => 21,
            'attribution' => '&copy; Diskominfo Drone Survey / Esri Clarity High Resolution',
        ],
    ],
];
