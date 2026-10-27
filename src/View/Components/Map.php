<?php

namespace Laragiser\Mapping\View\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use Laragiser\Mapping\Support\SpatialEntityDTO;

class Map extends Component
{
    public string $id;
    public string $height;
    public string $width;
    public array $center;
    public int $zoom;
    public int $minZoom;
    public int $maxZoom;
    public array $boundary;
    public array $tileLayers;
    public array $markers;
    public ?string $activeTile;
    public bool $enableControls;
    public bool $enableFullscreen;
    public bool $enableThemeSwitcher;
    public string $theme;

    // 5 Default Standard UI Controls Props
    public string $brandTitle;
    public string $brandBadge;
    public ?string $brandLogo;
    public bool $enableBrand;

    public bool $enableSearch;
    public string $searchPlaceholder;
    public bool $enableFilter;
    public array $categories;

    public bool $enableLegend;
    public array $legendItems;

    public bool $enableSpatialInspector;

    public bool $enableGisControls;
    public bool $enableResetRotation;
    public bool $enableResetView;
    public bool $enableGps;

    // Detail Drawer & Telemetry Props (100% Identik CCTV/PDAM/Kependudukan)
    public bool $enableDetailDrawer;
    public bool $enableTelemetry;
    public string $telemetryTotalLabel;
    public string $telemetryTotalTooltip;
    public array $telemetryMetrics;

    // Batas Administrasi Props (100% Identik CCTV/PDAM/Kependudukan)
    public ?string $boundaryKotaUrl;
    public ?string $boundaryKecamatanUrl;
    public ?string $boundaryKelurahanUrl;
    public bool $enableCityMask;
    public bool $enableDistricts;
    public bool $enableSubdistricts;
    public bool $enableBoundaryLabels;

    // Advanced Filter & Customization Props (Sistem UI Kompleks Kependudukan)
    public array $searchCategories;
    public array $filterFields;
    public ?string $customFilterView;
    public array $districts;

    public ?string $backUrl;
    public ?string $backLabel;

    public string $aboutTitle;
    public string $aboutSubtitle;
    public string $aboutVersion;
    public string $aboutDescription;

    public function __construct(
        ?string $id = null,
        string $height = '500px',
        string $width = '100%',
        ?array $center = null,
        ?int $zoom = null,
        ?int $minZoom = null,
        ?int $maxZoom = null,
        ?array $boundary = null,
        ?array $tileLayers = null,
        array|Collection $markers = [],
        ?string $activeTile = null,
        bool $enableControls = true,
        bool $enableFullscreen = true,
        bool $enableThemeSwitcher = true,
        string $theme = 'sky',
        string $brandTitle = 'Smart GIS',
        string $brandBadge = 'CORE V1',
        ?string $brandLogo = null,
        bool $enableBrand = true,
        bool $enableSearch = true,
        string $searchPlaceholder = 'Cari lokasi, titik, atau ID...',
        bool $enableFilter = true,
        array $categories = [],
        bool $enableLegend = true,
        array $legendItems = [],
        bool $enableSpatialInspector = true,
        bool $enableGisControls = true,
        bool $enableResetRotation = true,
        bool $enableResetView = true,
        bool $enableGps = true,
        bool $enableDetailDrawer = true,
        bool $enableTelemetry = true,
        string $telemetryTotalLabel = 'Total Entitas',
        string $telemetryTotalTooltip = 'Total keseluruhan data spasial terdaftar',
        array $telemetryMetrics = [],
        ?string $boundaryKotaUrl = null,
        ?string $boundaryKecamatanUrl = null,
        ?string $boundaryKelurahanUrl = null,
        bool $enableCityMask = true,
        bool $enableDistricts = true,
        bool $enableSubdistricts = true,
        bool $enableBoundaryLabels = true,
        array $searchCategories = [],
        array $filterFields = [],
        ?string $customFilterView = null,
        array $districts = [],
        ?string $backUrl = null,
        ?string $backLabel = 'Kembali',
        string $aboutTitle = 'Smart Spatial GIS',
        string $aboutSubtitle = 'Laravel Mapping Package',
        string $aboutVersion = 'v1.0.0',
        string $aboutDescription = 'Komponen pemetaan spasial modular berbasis WebGL GPU-accelerated MapLibre GL JS dengan Tile Proxy, Boundary Management, dan kontrol GIS lengkap.'
    ) {
        $this->id = $id ?? 'mapping_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $this->height = $height;
        $this->width = $width;

        $defaultConfig = Config::get('mapping.default_map', []);
        $this->center = $center ?? ($defaultConfig['center'] ?? ['lat' => 0.507068, 'lng' => 101.447779]);
        $this->zoom = $zoom ?? ($defaultConfig['default_zoom'] ?? 12);
        $this->minZoom = $minZoom ?? ($defaultConfig['min_zoom'] ?? 8);
        $this->maxZoom = $maxZoom ?? ($defaultConfig['max_zoom'] ?? 22);

        $defaultBoundary = Config::get('mapping.boundary', []);
        $this->boundary = array_merge([
            'enabled' => true,
            'name' => 'Kota Pekanbaru',
            'source' => 'resources/geojson/pekanbaru.geojson',
            'fit_bounds' => true,
            'style' => [
                'fill_color' => '#0284c7',
                'fill_opacity' => 0.08,
                'stroke_color' => '#38bdf8',
                'stroke_width' => 2,
            ],
        ], $defaultBoundary, $boundary ?? []);

        $this->boundaryKotaUrl = $boundaryKotaUrl ?? '/api/mapping/boundary';
        $this->boundaryKecamatanUrl = $boundaryKecamatanUrl ?? '/api/mapping/boundary/kecamatan';
        $this->boundaryKelurahanUrl = $boundaryKelurahanUrl ?? '/api/mapping/boundary/kelurahan';
        $this->enableCityMask = $enableCityMask;
        $this->enableDistricts = $enableDistricts;
        $this->enableSubdistricts = $enableSubdistricts;
        $this->enableBoundaryLabels = $enableBoundaryLabels;

        $this->tileLayers = !empty($tileLayers) ? $tileLayers : Config::get('mapping.tile_layers', []);
        $this->activeTile = $activeTile ?? 'google_satellite';
        $this->enableControls = $enableControls;
        $this->enableFullscreen = $enableFullscreen;
        $this->enableThemeSwitcher = $enableThemeSwitcher;
        $this->theme = $theme;

        $this->brandTitle = $brandTitle;
        $this->brandBadge = $brandBadge;
        $this->brandLogo = $brandLogo;
        $this->enableBrand = $enableBrand;

        $this->enableSearch = $enableSearch;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->enableFilter = $enableFilter;

        $this->enableLegend = $enableLegend;
        $this->enableSpatialInspector = $enableSpatialInspector;

        $this->enableGisControls = $enableGisControls;
        $this->enableResetRotation = $enableResetRotation;
        $this->enableResetView = $enableResetView;
        $this->enableGps = $enableGps;

        $this->enableDetailDrawer = $enableDetailDrawer;
        $this->enableTelemetry = $enableTelemetry;
        $this->telemetryTotalLabel = $telemetryTotalLabel;
        $this->telemetryTotalTooltip = $telemetryTotalTooltip;
        $this->telemetryMetrics = $telemetryMetrics;

        $this->searchCategories = !empty($searchCategories) ? $searchCategories : [
            ['key' => 'ALL', 'label' => 'SEMUA', 'icon' => '🔍', 'placeholder' => 'Ketik kata kunci pencarian...'],
            ['key' => 'id', 'label' => 'ID TITIK', 'icon' => '🔢', 'placeholder' => 'Ketik ID titik atau aset...'],
            ['key' => 'title', 'label' => 'NAMA / LABEL', 'icon' => '📍', 'placeholder' => 'Ketik nama lokasi atau kamera...'],
            ['key' => 'district', 'label' => 'WILAYAH', 'icon' => '🏛️', 'placeholder' => 'Ketik nama kecamatan atau distrik...'],
        ];
        $this->filterFields = $filterFields;
        $this->customFilterView = $customFilterView;

        $this->aboutTitle = $aboutTitle;
        $this->aboutSubtitle = $aboutSubtitle;
        $this->aboutVersion = $aboutVersion;
        $this->aboutDescription = $aboutDescription;

        $this->backUrl = $backUrl;
        $this->backLabel = $backLabel;

        $this->markers = collect($markers)->map(function ($item) {
            if ($item instanceof SpatialEntityDTO) {
                return $item->toArray();
            }
            if (is_array($item)) {
                return SpatialEntityDTO::fromArray($item)->toArray();
            }
            if (is_object($item) && method_exists($item, 'toArray')) {
                return SpatialEntityDTO::fromArray($item->toArray())->toArray();
            }
            return (array) $item;
        })->values()->toArray();

        // Extract categories if empty
        if (empty($categories)) {
            $extractedCategories = collect($this->markers)
                ->pluck('category')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            $this->categories = !empty($extractedCategories) ? $extractedCategories : ['ALL'];
        } else {
            $this->categories = $categories;
        }

        // Extract districts if empty
        if (empty($districts)) {
            $this->districts = collect($this->markers)
                ->pluck('district')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } else {
            $this->districts = $districts;
        }

        // Generate legend items if empty
        if (empty($legendItems)) {
            $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'cyan'];
            $this->legendItems = collect($this->categories)->map(function ($cat, $index) use ($colors) {
                $color = $colors[$index % count($colors)];
                $count = collect($this->markers)->filter(fn($m) => ($m['category'] ?? '') === $cat)->count();
                return [
                    'id' => strtolower($cat),
                    'label' => $cat,
                    'color' => $color,
                    'count' => $count,
                    'shape' => 'circle',
                ];
            })->toArray();
        } else {
            $this->legendItems = $legendItems;
        }
    }

    public function render(): View
    {
        return view('mapping::components.map');
    }
}
