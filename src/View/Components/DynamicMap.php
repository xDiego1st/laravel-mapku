<?php

namespace Laragiser\Mapping\View\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Livewire\Component;
use Laragiser\Mapping\Support\SpatialEntityDTO;

class DynamicMap extends Component
{
    public string $mapInstanceId;
    public string $title = 'Peta Pemetaan Wilayah Geospasial';
    public ?string $subtitle = 'Command Center Geospasial';
    public ?string $brandLogo = null;
    public string $brandTitle = 'Smart GIS';
    public string $brandBadge = 'SPASIAL';
    public string $searchPlaceholder = 'Cari data wilayah, nama, ID...';
    public bool $enableSearch = true;

    public string $aboutTitle = 'Smart Spatial GIS';
    public string $aboutSubtitle = 'Command Center Geospasial';
    public string $aboutVersion = 'v1.0 Laravel Mapping';
    public string $aboutDescription = 'Platform pemetaan spasial interaktif. Dirancang sebagai fondasi pemetaan modular berbasis Geographic Information System (GIS).';
    public string $authorName = 'Rasyid Abdul Halim, S.Tr.Kom';
    public string $authorRole = 'Consultant IT';
    public string $authorInitials = 'RAH';
    public array $highlightCapabilities = [];

    public string $boundaryKotaUrl = '/api/mapping/boundary';
    public string $boundaryKecamatanUrl = '/api/cctv/boundary/kecamatan';
    public string $boundaryKelurahanUrl = '/api/cctv/boundary/kelurahan';

    public array $items = [];
    public array $categories = [];
    public array $telemetryMetrics = [];
    public array $summaryData = [];
    public string $telemetryTotalLabel = 'Total Entitas';
    public string $telemetryTotalTooltip = 'Total keseluruhan data spasial';

    public array $mapConfig = [];
    public array $tileLayers = [];
    public bool $enableCityMask = true;
    public bool $enableDistricts = true;
    public bool $enableSubdistricts = true;
    public bool $enableSpatialInspector = true;

    public string $jsNamespace = 'SpatialMapApp';
    public bool $enableDefaultJs = true;

    public bool $enablePluginPanel = false;
    public string $pluginPanelTitle = 'Memuat Data...';
    public string $pluginPanelId = 'spatial-plugin-loading-panel';
    public string $pluginErrorId = 'spatial-plugin-error-panel';
    public array $legendItems = [];

    public bool $enableFilter = false;
    public bool $enableSummary = false;
    public bool $isKependudukan = false;
    public ?bool $enableLogData = null;

    public ?string $headerControlsView = null;
    public ?string $legendExtraView = null;
    public ?string $bottomToolsView = null;
    public ?string $customDrawerView = null;
    public ?string $searchSlotView = null;

    public string $activeCategory = 'ALL';
    public string $activeDistrict = 'ALL';
    public string $searchQuery = '';

    public array $tabPermissions = [];
    public array $fieldMaskingConfig = [];

    public function mount(
        array|Collection $items = [],
        ?string $title = null,
        ?string $subtitle = null,
        ?string $brandLogo = null,
        ?string $brandTitle = null,
        ?string $brandBadge = null,
        ?string $searchPlaceholder = null,
        ?bool $enableSearch = null,
        ?string $aboutTitle = null,
        ?string $aboutSubtitle = null,
        ?string $aboutVersion = null,
        ?string $aboutDescription = null,
        ?string $authorName = null,
        ?string $authorRole = null,
        ?string $authorInitials = null,
        array $highlightCapabilities = [],
        ?string $boundaryKotaUrl = null,
        ?string $boundaryKecamatanUrl = null,
        ?string $boundaryKelurahanUrl = null,
        ?string $telemetryTotalLabel = null,
        ?string $telemetryTotalTooltip = null,
        array $categories = [],
        array $telemetryMetrics = [],
        array $summaryData = [],
        array $mapConfig = [],
        ?bool $enableCityMask = null,
        ?bool $enableDistricts = null,
        ?bool $enableSubdistricts = null,
        ?bool $enableSpatialInspector = null,
        ?string $jsNamespace = null,
        ?bool $enableDefaultJs = null,
        ?bool $enablePluginPanel = null,
        ?string $pluginPanelTitle = null,
        ?string $pluginPanelId = null,
        ?string $pluginErrorId = null,
        array $legendItems = [],
        ?bool $enableFilter = null,
        ?bool $enableSummary = null,
        ?bool $isKependudukan = null,
        ?bool $enableLogData = null,
        ?string $headerControlsView = null,
        ?string $legendExtraView = null,
        ?string $bottomToolsView = null,
        ?string $customDrawerView = null,
        ?string $searchSlotView = null,
        ?string $activeCategory = null,
        array $tabPermissions = [],
        array $fieldMaskingConfig = [],
        ?string $mapInstanceId = null
    ): void {
        $this->mapInstanceId = $mapInstanceId ?? 'map_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);

        if (!empty($tabPermissions)) {
            $this->tabPermissions = array_replace_recursive($this->tabPermissions, $tabPermissions);
        }
        if (!empty($fieldMaskingConfig)) {
            $this->fieldMaskingConfig = array_replace_recursive($this->fieldMaskingConfig, $fieldMaskingConfig);
        }

        $this->isKependudukan = $isKependudukan ?? (request()->routeIs('kependudukan*') || request()->is('kependudukan*') || request()->is('peta*'));
        $this->enableLogData = $enableLogData ?? $this->isKependudukan;

        if ($enableSearch !== null) $this->enableSearch = $enableSearch;
        if ($enableFilter !== null) $this->enableFilter = $enableFilter;
        if ($enableSummary !== null) $this->enableSummary = $enableSummary;

        if ($headerControlsView) $this->headerControlsView = $headerControlsView;
        if ($legendExtraView) $this->legendExtraView = $legendExtraView;
        if ($bottomToolsView) $this->bottomToolsView = $bottomToolsView;
        if ($customDrawerView) $this->customDrawerView = $customDrawerView;
        if ($searchSlotView) $this->searchSlotView = $searchSlotView;

        if ($title) $this->title = $title;
        if ($subtitle) $this->subtitle = $subtitle;
        $this->brandLogo = $brandLogo ?: (file_exists(public_path('logo-kota.png')) ? asset('logo-kota.png') : null);
        if ($brandTitle) $this->brandTitle = $brandTitle;
        if ($brandBadge) $this->brandBadge = $brandBadge;
        if ($searchPlaceholder) $this->searchPlaceholder = $searchPlaceholder;

        if ($aboutTitle) $this->aboutTitle = $aboutTitle;
        if ($aboutSubtitle) $this->aboutSubtitle = $aboutSubtitle;
        if ($aboutVersion) $this->aboutVersion = $aboutVersion;
        if ($aboutDescription) $this->aboutDescription = $aboutDescription;
        if ($authorName) $this->authorName = $authorName;
        if ($authorRole) $this->authorRole = $authorRole;
        if ($authorInitials) $this->authorInitials = $authorInitials;

        if ($boundaryKotaUrl) $this->boundaryKotaUrl = $boundaryKotaUrl;
        if ($boundaryKecamatanUrl) $this->boundaryKecamatanUrl = $boundaryKecamatanUrl;
        if ($boundaryKelurahanUrl) $this->boundaryKelurahanUrl = $boundaryKelurahanUrl;
        if ($telemetryTotalLabel) $this->telemetryTotalLabel = $telemetryTotalLabel;
        if ($telemetryTotalTooltip) $this->telemetryTotalTooltip = $telemetryTotalTooltip;
        if ($activeCategory) $this->activeCategory = $activeCategory;

        $this->highlightCapabilities = !empty($highlightCapabilities) ? $highlightCapabilities : [
            ['icon' => '🗺️', 'title' => 'MapLibre GL JS', 'desc' => 'High-performance GPU-accelerated'],
            ['icon' => '🏛️', 'title' => 'Batas Wilayah', 'desc' => 'GeoJSON MultiPolygon WGS84'],
            ['icon' => '🎯', 'title' => 'Inspektor Spasial', 'desc' => 'Point-in-Polygon (Turf.js)'],
            ['icon' => '⚡', 'title' => 'Tile Proxy', 'desc' => 'Superapp & CORS Ready'],
        ];

        if ($enableCityMask !== null) $this->enableCityMask = $enableCityMask;
        if ($enableDistricts !== null) $this->enableDistricts = $enableDistricts;
        if ($enableSubdistricts !== null) $this->enableSubdistricts = $enableSubdistricts;
        if ($enableSpatialInspector !== null) $this->enableSpatialInspector = $enableSpatialInspector;
        if ($jsNamespace) $this->jsNamespace = $jsNamespace;
        if ($enableDefaultJs !== null) $this->enableDefaultJs = $enableDefaultJs;

        if ($enablePluginPanel !== null) $this->enablePluginPanel = $enablePluginPanel;
        if ($pluginPanelTitle) $this->pluginPanelTitle = $pluginPanelTitle;
        if ($pluginPanelId) $this->pluginPanelId = $pluginPanelId;
        if ($pluginErrorId) $this->pluginErrorId = $pluginErrorId;
        $this->legendItems = $legendItems;

        $this->items = collect($items)->map(function ($item) {
            if ($item instanceof SpatialEntityDTO) return $item->toArray();
            if (is_array($item)) return SpatialEntityDTO::fromArray($item)->toArray();
            if (is_object($item) && method_exists($item, 'toArray')) return SpatialEntityDTO::fromArray($item->toArray())->toArray();
            return (array) $item;
        })->values()->toArray();

        $this->categories = !empty($categories) ? $categories : [
            ['key' => 'ALL', 'label' => 'Semua', 'color' => 'slate'],
        ];

        $this->summaryData = !empty($summaryData) ? $summaryData : $this->calculateAutoSummary();
        $this->telemetryMetrics = $telemetryMetrics;

        $defaultMapConfig = Config::get('mapping.default_map', [
            'center' => ['lat' => 0.507068, 'lng' => 101.447779],
            'default_zoom' => 12,
            'min_zoom' => 8,
            'max_zoom' => 22,
            'bounds' => ['south_west' => [0.25, 101.1], 'north_east' => [0.75, 101.8]],
        ]);
        $this->mapConfig = array_merge($defaultMapConfig, $mapConfig);
        $this->tileLayers = Config::get('mapping.tile_layers', Config::get('cctv.tile_layers', []));
    }

    protected function calculateAutoSummary(): array
    {
        $total = count($this->items);
        $countsByStatus = [];
        $countsByCategory = [];

        foreach ($this->items as $item) {
            $st = strtoupper($item['status'] ?? 'ACTIVE');
            $cat = strtoupper($item['category'] ?? 'GENERAL');
            $countsByStatus[$st] = ($countsByStatus[$st] ?? 0) + 1;
            $countsByCategory[$cat] = ($countsByCategory[$cat] ?? 0) + 1;
        }

        return ['total' => $total, 'by_status' => $countsByStatus, 'by_category' => $countsByCategory];
    }

    public function updateFilter(string $category, string $district = 'ALL', string $search = ''): void
    {
        $this->activeCategory = $category;
        $this->activeDistrict = $district;
        $this->searchQuery = $search;

        $this->dispatch('spatial-filter-updated', [
            'category' => $category,
            'district' => $district,
            'search' => $search,
        ]);
    }

    public function render()
    {
        $user = auth()->user() ?? auth('web')->user();
        $roleName = strtoupper(str_replace([' ', '_'], '-', $user->role_name ?? ''));
        $isSuperAdmin = ($roleName === 'SUPER-ADMIN' || $roleName === 'SUPERADMIN');

        return view('mapping::components.dynamic-map', [
            'initialItemsJson' => json_encode($this->items),
            'mapConfigJson' => json_encode($this->mapConfig),
            'tileLayersJson' => json_encode($this->tileLayers),
            'categoriesJson' => json_encode($this->categories),
            'boundaryConfigJson' => json_encode(Config::get('mapping.boundary', [])),
            'currentUserRole' => $roleName ?: 'GUEST',
            'isSuperAdmin' => $isSuperAdmin,
            'lockedTabsJson' => json_encode($this->tabPermissions),
            'fieldMaskingJson' => json_encode($this->fieldMaskingConfig),
        ]);
    }
}
