@php
    $mapConfig = [
        'id' => $id,
        'center' => $center,
        'zoom' => $zoom,
        'minZoom' => $minZoom,
        'maxZoom' => $maxZoom,
        'activeTile' => $activeTile,
        'boundary' => $boundary,
        'boundaryConfig' => [
            'kota_url' => $boundaryKotaUrl ?? '/api/mapping/boundary',
            'kecamatan_url' => $boundaryKecamatanUrl ?? '/api/mapping/boundary/kecamatan',
            'kelurahan_url' => $boundaryKelurahanUrl ?? '/api/mapping/boundary/kelurahan',
        ],
        'enableCityMask' => $enableCityMask ?? true,
        'enableDistricts' => $enableDistricts ?? true,
        'enableSubdistricts' => $enableSubdistricts ?? true,
        'enableBoundaryLabels' => $enableBoundaryLabels ?? true,
        'tileLayers' => $tileLayers,
        'markers' => $markers,
        'categories' => $categories,
        'theme' => $theme,
        'enableSpatialInspector' => $enableSpatialInspector,
        'enableDetailDrawer' => $enableDetailDrawer,
        'enableTelemetry' => $enableTelemetry,
    ];
@endphp

<div class="mapping-wrapper theme-{{ $theme }}" id="mapping-wrapper-{{ $id }}" style="width: {{ $width }}; height: {{ $height }};">
    <!-- 1. WebGL Canvas Container -->
    <div id="mapping-canvas-{{ $id }}" class="mapping-canvas"
        data-mapping-instance="{{ $id }}"
        data-mapping-config='{!! json_encode($mapConfig) !!}'>
    </div>

    <!-- 2. Top Header Command Bar (Brand, Search, Filters) -->
    @if($enableBrand || $enableSearch || $enableFilter)
        @include('mapping::partials.header-command-bar')
    @endif

    <!-- 3. Complex Sliding Filter Drawer (Left Side, 100% Identik Kependudukan) -->
    @if($enableFilter || $enableSearch)
        @include('mapping::partials.filter-sidebar', [
            'id' => $id,
            'brandTitle' => $brandTitle,
            'categories' => $categories,
            'searchCategories' => $searchCategories,
            'filterFields' => $filterFields,
            'customFilterView' => $customFilterView,
            'districts' => $districts,
        ])
    @endif

    <!-- 4. Bottom-Left Stack: Spatial Inspector & Collapsible Legend -->
    <div class="mapping-bottom-left-stack" id="mapping-bottom-left-stack-{{ $id }}">
        @if($enableSpatialInspector)
            @include('mapping::partials.spatial-inspector')
        @endif

        @if($enableLegend)
            @include('mapping::partials.legend-panel')
        @endif
    </div>

    <!-- 5. Bottom-Center: Compact Telemetry Status Bar (100% Identik CCTV/PDAM/Kependudukan) -->
    @if($enableTelemetry)
        @include('mapping::partials.telemetry-bar', [
            'id' => $id,
            'markers' => $markers,
            'telemetryTotalLabel' => $telemetryTotalLabel,
            'telemetryTotalTooltip' => $telemetryTotalTooltip,
            'telemetryMetrics' => $telemetryMetrics,
        ])
    @endif

    <!-- 6. Floating Right-Side GIS Control Suite -->
    @if($enableGisControls)
        @include('mapping::partials.right-gis-controls')
    @endif

    <!-- 7. Offcanvas Slide-Over Detail Drawer (Right Side, 100% Identik CCTV/PDAM/Kependudukan) -->
    @if($enableDetailDrawer)
        @include('mapping::partials.entity-drawer')
    @endif

    <!-- 8. Modals -->
    @include('mapping::partials.basemap-modal', [
        'id' => $id,
        'tileLayers' => $tileLayers,
        'activeTile' => $activeTile
    ])

    @if($enableThemeSwitcher)
        @include('mapping::partials.theme-modal', ['id' => $id])
    @endif

    @include('mapping::partials.about-modal', [
        'id' => $id,
        'aboutVersion' => $aboutVersion,
        'aboutTitle' => $aboutTitle,
        'aboutSubtitle' => $aboutSubtitle,
        'aboutDescription' => $aboutDescription,
        'highlightCapabilities' => [
            'Tile Proxy Caching 7 Hari (Anti-CORS)',
            'Dynamic GeoJSON Boundary & PIP Inspector',
            'GPU-Accelerated WebGL Rendering MapLibre GL',
            'Full GIS Control Suite (Compass, GPS, Themes)',
            '100% Theme Customizer 2.0 with Sliders',
            'Slide-over Detail Drawer & Telemetry Status',
        ],
        'authorInitials' => 'RAH',
        'authorName' => 'Rasyid Abdul Halim, S.Tr.Kom',
        'authorRole' => 'Consultant IT',
    ])
</div>
