<?php

namespace Laragiser\Mapping\Tests\Unit;

use Laragiser\Mapping\View\Components\Map;
use Tests\TestCase;

class MapComponentPropsTest extends TestCase
{
    public function test_map_component_initializes_with_all_5_default_ui_controls(): void
    {
        $map = new Map(
            id: 'unit-test-map',
            markers: [
                ['id' => 1, 'lat' => 0.507, 'lng' => 101.447, 'title' => 'CCTV 1', 'category' => 'CCTV'],
                ['id' => 2, 'lat' => 0.51, 'lng' => 101.45, 'title' => 'PDAM Pipe', 'category' => 'PDAM'],
            ]
        );

        // 1. Legenda Map
        $this->assertTrue($map->enableLegend);
        $this->assertNotEmpty($map->legendItems);
        $this->assertCount(2, $map->legendItems);

        // 2. Inspektor Spasial
        $this->assertTrue($map->enableSpatialInspector);

        // 3. Brand
        $this->assertTrue($map->enableBrand);
        $this->assertEquals('Smart GIS', $map->brandTitle);
        $this->assertEquals('CORE V1', $map->brandBadge);

        // 4. Search & Filtering
        $this->assertTrue($map->enableSearch);
        $this->assertTrue($map->enableFilter);
        $this->assertContains('CCTV', $map->categories);
        $this->assertContains('PDAM', $map->categories);

        // 5. Right GIS Controls
        $this->assertTrue($map->enableGisControls);
        $this->assertTrue($map->enableResetRotation);
        $this->assertTrue($map->enableResetView);
        $this->assertTrue($map->enableGps);
        $this->assertTrue($map->enableThemeSwitcher);
        $this->assertTrue($map->enableFullscreen);

        // 6. Back URL
        $mapWithBack = new Map(id: 'map-back', backUrl: '/admin/dashboard', backLabel: 'Kembali');
        $this->assertEquals('/admin/dashboard', $mapWithBack->backUrl);
        $this->assertEquals('Kembali', $mapWithBack->backLabel);
    }

    public function test_map_component_renders_blade_view_properly(): void
    {
        $view = $this->blade('<x-mapping-map id="test-blade-render" back-url="/admin/dashboard" back-label="Kembali ke Dashboard" />');

        // Assert container elements exist
        $view->assertSee('mapping-canvas-test-blade-render', false);
        $view->assertSee('mapping-top-command-bar', false);
        $view->assertSee('mapping-back-btn-test-blade-render', false);
        $view->assertSee('/admin/dashboard', false);
        $view->assertSee('Kembali ke Dashboard', false);
        $view->assertSee('mapping-spatial-inspector', false);
        $view->assertSee('mapping-legend-panel', false);
        $view->assertSee('mapping-right-gis-controls', false);
        $view->assertSee('mapping-theme-modal', false);

        // Assert 100% CCTV/PDAM Theme Customizer 2.0 Elements
        $view->assertSee('theme-slider-opacity-test-blade-render', false);
        $view->assertSee('theme-slider-blur-test-blade-render', false);
        $view->assertSee('theme-slider-sat-test-blade-render', false);
        $view->assertSee('theme-slider-border-test-blade-render', false);
        $view->assertSee('theme-custom-color-test-blade-render', false);
        $view->assertSee('Cyber Sky', false);
        $view->assertSee('Emerald Matrix', false);

        // Assert Sidebar Detail Drawer & Resizer Handle
        $view->assertSee('spatial-entity-drawer-test-blade-render', false);
        $view->assertSee('spatial-drawer-resizer-test-blade-render', false);
        $view->assertSee('drawer-status-badge-test-blade-render', false);
        $view->assertSee('drawer-gmaps-link-test-blade-render', false);

        // Assert Bottom-Center Telemetry Status Bar
        $view->assertSee('spatial-bottom-telemetry-test-blade-render', false);
        $view->assertSee('telemetry-pill-container', false);
        $view->assertSee('telemetry-total-count-test-blade-render', false);

        // Assert Top-Right Basemap Dropdown & Fullscreen Button (100% Identik CCTV/PDAM/Kependudukan)
        $view->assertSee('spatial-basemap-dropdown-btn-test-blade-render', false);
        $view->assertSee('spatial-basemap-dropdown-test-blade-render', false);
        $view->assertSee('spatial-fullscreen-btn-test-blade-render', false);

        // Assert Right-Side Complex Filter Sidebar & Omni Search (100% Identik Kependudukan)
        $view->assertSee('mapping-search-filter-btn-test-blade-render', false);
        $view->assertSee('mapping-filter-sidebar-test-blade-render', false);
        $view->assertSee('mapping-filter-resizer-test-blade-render', false);
        $view->assertSee('mapping-complex-search-input-test-blade-render', false);
    }
}
