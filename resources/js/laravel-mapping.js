/**
 * Laravel Mapping Package Engine
 * Author: Laragiser / Diskominfotiksan Kota Pekanbaru
 * Powered by MapLibre GL JS (WebGL / GPU-accelerated)
 */

(function (window, document) {
    'use strict';

    // ─── 0. POINT IN POLYGON HELPER (STANDALONE RAYCASTING) ─────────────────
    function isPointInPolygon(point, vs) {
        const x = point[0], y = point[1];
        let inside = false;
        for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
            const xi = vs[i][0], yi = vs[i][1];
            const xj = vs[j][0], yj = vs[j][1];
            const intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    // ─── 0.1 6 THEME PRESETS (100% IDENTIK CCTV / PDAM / KEPENDUDUKAN) ─────
    const themePresets = {
        sky: {
            name: 'Cyber Sky',
            accent: '#0284c7',
            hover: '#0369a1',
            light: '#38bdf8',
            rgb: '2, 132, 199',
            glassBg: '8, 28, 52',
            glassLightBg: '14, 44, 78',
            glassBorder: '30, 92, 150'
        },
        emerald: {
            name: 'Emerald Matrix',
            accent: '#059669',
            hover: '#047857',
            light: '#34d399',
            rgb: '5, 150, 105',
            glassBg: '6, 38, 28',
            glassLightBg: '12, 60, 44',
            glassBorder: '24, 130, 92'
        },
        violet: {
            name: 'Electric Violet',
            accent: '#7c3aed',
            hover: '#6d28d9',
            light: '#a78bfa',
            rgb: '124, 58, 237',
            glassBg: '28, 16, 50',
            glassLightBg: '44, 26, 76',
            glassBorder: '98, 55, 160'
        },
        amber: {
            name: 'Sunset Amber',
            accent: '#d97706',
            hover: '#b45309',
            light: '#fbbf24',
            rgb: '217, 119, 6',
            glassBg: '38, 24, 10',
            glassLightBg: '58, 38, 16',
            glassBorder: '130, 80, 28'
        },
        rose: {
            name: 'Crimson Rose',
            accent: '#e11d48',
            hover: '#be123c',
            light: '#fb7185',
            rgb: '225, 29, 72',
            glassBg: '40, 12, 24',
            glassLightBg: '64, 20, 40',
            glassBorder: '140, 38, 78'
        },
        slate: {
            name: 'Stealth Slate',
            accent: '#475569',
            hover: '#334155',
            light: '#94a3b8',
            rgb: '71, 85, 105',
            glassBg: '15, 23, 42',
            glassLightBg: '30, 41, 59',
            glassBorder: '51, 65, 85'
        }
    };

    function hexToRgb(hex) {
        if (!hex) return '2, 132, 199';
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
        const num = parseInt(hex, 16);
        if (isNaN(num)) return '2, 132, 199';
        return `${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}`;
    }

    function deriveGlassBase(hex) {
        hex = (hex || '').replace('#', '');
        if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
        const num = parseInt(hex, 16);
        if (isNaN(num)) return { bg: '8, 28, 52', lightBg: '14, 44, 78', border: '30, 92, 150' };
        const r = (num >> 16) & 255;
        const g = (num >> 8) & 255;
        const b = num & 255;
        return {
            bg: `${Math.round(6 + r * 0.16)}, ${Math.round(8 + g * 0.16)}, ${Math.round(12 + b * 0.16)}`,
            lightBg: `${Math.round(12 + r * 0.24)}, ${Math.round(16 + g * 0.24)}, ${Math.round(22 + b * 0.24)}`,
            border: `${Math.round(24 + r * 0.48)}, ${Math.round(30 + g * 0.48)}, ${Math.round(40 + b * 0.48)}`
        };
    }

    // ─── 1. GLOBAL INSTANCE REGISTRY (MULTI-INSTANCE ISOLATION) ─────────────
    window.LaravelMapping = window.LaravelMapping || {
        instances: new Map(),

        register(id, instance) {
            this.instances.set(id, instance);
            window.dispatchEvent(new CustomEvent('mapping:registered', { detail: { id, instance } }));
        },

        get(id) {
            return this.instances.get(id);
        },

        destroy(id) {
            const inst = this.instances.get(id);
            if (inst && typeof inst.destroy === 'function') {
                inst.destroy();
                this.instances.delete(id);
            }
        },

        getAll() {
            return Array.from(this.instances.values());
        }
    };

    // ─── 2. MAP INSTANCE ENGINE CLASS ───────────────────────────────────────
    class MappingEngine {
        constructor(config) {
            this.id = config.id;
            this.container = document.getElementById(config.containerId || `mapping-canvas-${config.id}`);
            this.config = Object.assign({
                center: [101.447779, 0.507068], // [lng, lat]
                zoom: 12,
                minZoom: 8,
                maxZoom: 22,
                activeTile: 'google_satellite',
                boundary: { enabled: true, source: '/api/mapping/boundary', fit_bounds: true, name: 'Kota Pekanbaru' },
                tileLayers: {},
                markers: [],
                categories: [],
                cluster: true,
                theme: 'sky',
                enableSpatialInspector: true,
            }, config);

            this.map = null;
            this.isReady = false;
            this.activeTileKey = this.config.activeTile;
            this.boundaryGeoJson = null;
            this.activeCategory = 'ALL';
            this.searchQuery = '';
            this.userGpsMarker = null;
            this.activeEntity = null;

            // Inisialisasi Tema Glassmorphism 2.0 (100% Identik CCTV & PDAM)
            const p = themePresets[this.config.theme] || themePresets.sky;
            this.currentTheme = {
                preset: this.config.theme || 'sky',
                themeAccent: p.accent,
                themeAccentHover: p.hover,
                themeAccentLight: p.light,
                themeAccentRgb: p.rgb,
                glassBgBase: p.glassBg,
                glassLightBgBase: p.glassLightBg,
                glassBorderColor: p.glassBorder,
                glassOpacity: 0.88,
                glassBlur: 16,
                glassSaturation: 180,
                borderOpacity: 0.35
            };

            // Simpan initial coordinate dan zoom untuk fitur Reset View
            if (Array.isArray(this.config.center)) {
                this.initialCenter = this.config.center;
            } else if (this.config.center && typeof this.config.center === 'object') {
                this.initialCenter = [
                    parseFloat(this.config.center.lng || this.config.center.longitude || 101.447779),
                    parseFloat(this.config.center.lat || this.config.center.latitude || 0.507068)
                ];
            } else {
                this.initialCenter = [101.447779, 0.507068];
            }
            this.initialZoom = this.config.zoom || 12;

            if (this.container) {
                this.init();
            } else {
                console.warn(`[LaravelMapping] Container #${config.containerId} tidak ditemukan.`);
            }
        }

        init() {
            if (!window.maplibregl) {
                console.error('[LaravelMapping] MapLibre GL JS belum dimuat di browser!');
                return;
            }

            // Pulihkan konfigurasi tema dan efek kaca dari localStorage
            this.loadThemeConfig();

            // Inisialisasi Resizer Sidebar Detail, Filter, & Category Pills
            this.setupDrawerResizer();
            this.setupFilterResizer();
            this.setupKeyboardShortcuts();
            this.setupCategoryPills();

            const savedTile = localStorage.getItem(`mapping_basemap_${this.id}`);
            if (savedTile && this.config.tileLayers[savedTile]) {
                this.activeTileKey = savedTile;
            }

            // Bangun Style Awal MapLibre (Raster Tile Layer)
            const initialStyle = this.buildInitialStyle();

            this.map = new window.maplibregl.Map({
                container: this.container,
                style: initialStyle,
                center: this.initialCenter,
                zoom: this.initialZoom,
                minZoom: this.config.minZoom,
                maxZoom: this.config.maxZoom,
                attributionControl: false
            });

            const defaultGlyphsUrl = (window.location.origin || '') + '/vendor/laravel-mapping/fonts/{fontstack}/{range}.pbf';
            if (typeof this.map.setGlyphs === 'function') {
                try {
                    this.map.setGlyphs(defaultGlyphsUrl);
                } catch (e) { }
            }

            // Atribusi kompak di kanan bawah
            this.map.addControl(new window.maplibregl.AttributionControl({ compact: true }), 'bottom-right');

            this.map.on('load', () => {
                this.isReady = true;
                try { this.map.resize(); } catch (e) { }
                setTimeout(() => { try { this.map.resize(); } catch (e) { } }, 150);
                setTimeout(() => { try { this.map.resize(); } catch (e) { } }, 500);

                window.addEventListener('resize', () => {
                    try { this.map.resize(); } catch (e) { }
                });

                this.setupBoundaryOverlay();
                this.setupMarkersLayer();
                this.setupMouseMoveInspector();
                this.setupRotationTracker();
                this.updateFilterCounts();

                // Daftarkan ke Global Registry
                window.LaravelMapping.register(this.id, this);

                // Listen to live markers update & entity focus
                window.addEventListener('mapping:update-markers', (e) => {
                    if (!e.detail) return;
                    const targetId = e.detail.mapId || e.detail.id;
                    const markers = e.detail.markers;
                    if (targetId && targetId !== this.id) return;
                    this.setMarkers(markers);
                });

                window.addEventListener('mapping:focus-entity', (e) => {
                    if (!e.detail) return;
                    const targetId = e.detail.mapId;
                    const entityId = e.detail.entityId || e.detail.id;
                    if (targetId && targetId !== this.id) return;
                    this.focusEntity(entityId);
                });

                window.addEventListener('mapping:focus-marker', (e) => {
                    if (!e.detail) return;
                    const targetId = e.detail.mapId;
                    const entityId = e.detail.entityId || e.detail.id;
                    if (targetId && targetId !== this.id) return;
                    this.focusEntity(entityId);
                });

                window.addEventListener('mapping:entity-location-updated', (e) => {
                    if (!e.detail) return;
                    const targetId = e.detail.mapId || e.detail.id;
                    if (targetId && targetId !== this.id) return;
                    this.updateEntityLocation(e.detail.entityId, e.detail.lat, e.detail.lng, e.detail.speed, e.detail.heading);
                });

                if (!window.SpatialMapInstance) {
                    window.SpatialMapInstance = this.map;
                }

                // Dispatch Event Siap
                const eventPayload = { id: this.id, map: this.map, engine: this };
                window.dispatchEvent(new CustomEvent('mapping:ready', { detail: eventPayload }));
                if (this.container) {
                    this.container.dispatchEvent(new CustomEvent('mapping-ready', { detail: eventPayload, bubbles: true }));
                }
            });
        }

        buildInitialStyle() {
            const tileConfig = this.config.tileLayers[this.activeTileKey] || {
                url: '/api/mapping/tile/google_satellite/{z}/{x}/{y}',
                maxZoom: 20,
                attribution: '&copy; Google Maps Imagery'
            };

            const tileUrl = tileConfig.url.startsWith('http') || tileConfig.url.startsWith('/')
                ? tileConfig.url
                : window.location.origin + '/' + tileConfig.url.replace(/^\//, '');

            const defaultGlyphsUrl = (window.location.origin || '') + '/vendor/laravel-mapping/fonts/{fontstack}/{range}.pbf';

            return {
                version: 8,
                glyphs: defaultGlyphsUrl,
                sources: {
                    'base_tile_source': {
                        type: 'raster',
                        tiles: [tileUrl],
                        tileSize: 256,
                        maxzoom: tileConfig.maxZoom || 20,
                        attribution: tileConfig.attribution || ''
                    }
                },
                layers: [
                    {
                        id: 'base_tile_layer',
                        type: 'raster',
                        source: 'base_tile_source',
                        minzoom: 0,
                        maxzoom: 22
                    }
                ]
            };
        }

        toggleBasemapDropdown(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const dropdown = document.getElementById(`spatial-basemap-dropdown-${this.id}`);
            const btn = document.getElementById(`spatial-basemap-dropdown-btn-${this.id}`);
            if (!dropdown) return;

            const isHidden = dropdown.style.display === 'none' || !dropdown.style.display;
            dropdown.style.display = isHidden ? 'flex' : 'none';
            if (btn) btn.classList.toggle('is-active', isHidden);

            if (isHidden) {
                // Pastikan inline positioning tidak menimpa CSS layout
                dropdown.style.position = '';
                dropdown.style.top = '';
                dropdown.style.left = '';
                dropdown.style.right = '';

                // Hitung batas tinggi agar tidak meluap dari viewport container
                if (this.container && btn) {
                    const wrapperRect = this.container.getBoundingClientRect();
                    const btnRect = btn.getBoundingClientRect();
                    const availableHeight = wrapperRect.bottom - btnRect.bottom - 16;
                    dropdown.style.maxHeight = (availableHeight > 220 ? Math.min(500, availableHeight) : 400) + 'px';
                } else {
                    dropdown.style.maxHeight = '500px';
                }

                const closeHandler = (e) => {
                    if (!dropdown.contains(e.target) && (!btn || !btn.contains(e.target))) {
                        this.closeBasemapDropdown();
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => {
                    document.addEventListener('click', closeHandler);
                }, 10);
            }
        }

        closeBasemapDropdown() {
            const dropdown = document.getElementById(`spatial-basemap-dropdown-${this.id}`);
            const btn = document.getElementById(`spatial-basemap-dropdown-btn-${this.id}`);
            if (dropdown) dropdown.style.display = 'none';
            if (btn) btn.classList.remove('is-active');
        }

        switchBaseTile(tileKey) {
            if (!this.map || !this.config.tileLayers[tileKey]) return;

            const tileConfig = this.config.tileLayers[tileKey];
            const tileUrl = tileConfig.url.startsWith('http') || tileConfig.url.startsWith('/')
                ? tileConfig.url
                : window.location.origin + '/' + tileConfig.url.replace(/^\//, '');

            this.activeTileKey = tileKey;

            try {
                if (this.map.getSource('base_tile_source')) {
                    this.map.removeLayer('base_tile_layer');
                    this.map.removeSource('base_tile_source');
                }

                this.map.addSource('base_tile_source', {
                    type: 'raster',
                    tiles: [tileUrl],
                    tileSize: 256,
                    maxzoom: tileConfig.maxZoom || 20,
                    attribution: tileConfig.attribution || ''
                });

                const beforeLayer = this.map.getLayer('boundary_fill') ? 'boundary_fill' : undefined;
                this.map.addLayer({
                    id: 'base_tile_layer',
                    type: 'raster',
                    source: 'base_tile_source',
                    minzoom: 0,
                    maxzoom: 22
                }, beforeLayer);

                // Update UI text on button
                const nameEl = document.getElementById(`spatial-current-basemap-name-${this.id}`);
                if (nameEl) nameEl.textContent = tileConfig.name || tileKey;

                // Update indicator dot inside dropdown
                const items = document.querySelectorAll(`#spatial-basemap-dropdown-${this.id} .spatial-basemap-item`);
                items.forEach(el => {
                    const isActive = el.getAttribute('data-layer') === tileKey;
                    el.classList.toggle('is-active', isActive);
                    const ind = el.querySelector('.spatial-basemap-indicator');
                    if (ind) ind.classList.toggle('hidden', !isActive);
                });

                localStorage.setItem(`mapping_basemap_${this.id}`, tileKey);
                window.dispatchEvent(new CustomEvent('mapping:tile-changed', { detail: { id: this.id, tileKey } }));
            } catch (err) {
                console.error('[LaravelMapping] Gagal mengganti basemap tile:', err);
            }
        }

        toggleCityMask(checked) {
            if (!this.map) return;
            if (this.map.getLayer('layer_mask_kota')) {
                this.map.setLayoutProperty('layer_mask_kota', 'visibility', checked ? 'visible' : 'none');
            }
        }

        toggleDistrictsFill(checked) {
            if (!this.map) return;
            if (this.map.getLayer('layer_fill_kecamatan')) {
                this.map.setLayoutProperty('layer_fill_kecamatan', 'visibility', checked ? 'visible' : 'none');
            }
            try {
                localStorage.setItem(`mapping_districts_fill_${this.id}`, checked ? '1' : '0');
            } catch (e) { }
        }

        toggleDistrictsLayer(checked) {
            if (!this.map) return;
            if (this.map.getLayer('layer_boundary_kecamatan')) {
                this.map.setLayoutProperty('layer_boundary_kecamatan', 'visibility', checked ? 'visible' : 'none');
            }
        }

        toggleSubdistrictsFill(checked) {
            if (!this.map) return;
            if (this.map.getLayer('layer_fill_kelurahan')) {
                this.map.setLayoutProperty('layer_fill_kelurahan', 'visibility', checked ? 'visible' : 'none');
            }
            try {
                localStorage.setItem(`mapping_subdistricts_fill_${this.id}`, checked ? '1' : '0');
            } catch (e) { }
        }

        toggleSubdistrictsLayer(checked) {
            if (!this.map) return;
            if (this.map.getLayer('layer_boundary_kelurahan')) {
                this.map.setLayoutProperty('layer_boundary_kelurahan', 'visibility', checked ? 'visible' : 'none');
            }
        }

        toggleBoundaryLabels(checked) {
            if (!this.map) return;
            const visibility = checked ? 'visible' : 'none';
            if (this.map.getLayer('layer_label_kecamatan')) {
                this.map.setLayoutProperty('layer_label_kecamatan', 'visibility', visibility);
            }
            if (this.map.getLayer('layer_label_kelurahan')) {
                this.map.setLayoutProperty('layer_label_kelurahan', 'visibility', visibility);
            }
        }

        setBoundaryFillOpacity(val) {
            if (!this.map) return;
            val = Math.max(0.05, Math.min(0.85, parseFloat(val) || 0.25));
            this.currentBoundaryFillOpacity = val;
            if (this.map.getLayer('layer_fill_kecamatan')) {
                this.map.setPaintProperty('layer_fill_kecamatan', 'fill-opacity', val);
            }
            if (this.map.getLayer('layer_fill_kelurahan')) {
                this.map.setPaintProperty('layer_fill_kelurahan', 'fill-opacity', val);
            }
            if (this.map.getLayer('boundary_fill')) {
                this.map.setPaintProperty('boundary_fill', 'fill-opacity', val);
            }
            const labelEl = document.getElementById(`spatial-boundary-fill-opacity-val-${this.id}`) ||
                document.getElementById(`spatial-boundary-opacity-val-${this.id}`);
            if (labelEl) {
                labelEl.textContent = Math.round(val * 100) + '%';
            }
            const sliderEl = document.getElementById(`spatial-slider-boundary-opacity-${this.id}`);
            if (sliderEl) {
                sliderEl.value = Math.round(val * 100);
            }
            try {
                localStorage.setItem(`mapping_boundary_fill_opacity_${this.id}`, String(val));
            } catch (e) { }
        }

        toggleBoundary(checked) {
            this.toggleCityMask(checked);
            this.toggleDistrictsLayer(checked);
        }

        setBoundaryOpacity(opacity) {
            this.setBoundaryFillOpacity(opacity);
        }

        toggleFullscreen() {
            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
            if (!wrapper) return;
            if (!document.fullscreenElement) {
                if (wrapper.requestFullscreen) {
                    wrapper.requestFullscreen().catch(err => console.warn(err));
                } else if (wrapper.webkitRequestFullscreen) {
                    wrapper.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(err => console.warn(err));
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        }

        async setupBoundaryOverlay() {
            if (!this.map) return;

            const bCfg = this.config.boundaryConfig || {};
            const kotaUrl = bCfg.kota_url || this.config.boundary?.source || '/api/mapping/boundary';
            const kecUrl = bCfg.kecamatan_url || '/api/mapping/boundary/kecamatan';
            const kelUrl = bCfg.kelurahan_url || '/api/mapping/boundary/kelurahan';

            const DISTRICT_PALETTE = [
                '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b',
                '#ec4899', '#06b6d4', '#f97316', '#14b8a6',
                '#6366f1', '#84cc16', '#d946ef', '#e11d48',
                '#0ea5e9', '#22c55e', '#f43f5e', '#a855f7'
            ];

            const SUBDISTRICT_PALETTE = [
                '#0284c7', '#059669', '#7c3aed', '#d97706', '#db2777', '#0891b2',
                '#ea580c', '#0d9488', '#4f46e5', '#65a30d', '#c026d3', '#e11d48',
                '#2563eb', '#16a34a', '#9333ea', '#b45309', '#be185d', '#0e7490',
                '#c2410c', '#0f766e', '#4338ca', '#4d7c0f', '#a21caf', '#be123c',
                '#0369a1', '#047857', '#6d28d9', '#b45309', '#9d174d', '#155e75'
            ];

            const buildColorExpr = (data, propKey, palette) => {
                if (!data || !data.features || data.features.length === 0) return palette[0];
                const expr = ['match', ['coalesce', ['get', propKey], ['get', 'nama_' + propKey], ['get', 'nm_' + propKey], '']];
                const seen = new Set();
                let idx = 0;
                data.features.forEach(f => {
                    const val = f.properties?.[propKey] || f.properties?.['nm_' + propKey] || f.properties?.['nama_' + propKey];
                    if (val && !seen.has(val)) {
                        seen.add(val);
                        expr.push(val, palette[idx % palette.length]);
                        idx++;
                    }
                });
                expr.push('#64748b');
                return expr;
            };

            const fetchWithLocalCache = async (url, cacheKey) => {
                try {
                    const cached = localStorage.getItem(cacheKey);
                    if (cached) {
                        const parsed = JSON.parse(cached);
                        if (parsed && parsed.data && Date.now() - parsed.ts < 86400000) {
                            return parsed.data;
                        }
                    }
                } catch (e) { }

                try {
                    const res = await fetch(url);
                    if (res.ok) {
                        const data = await res.json();
                        try {
                            localStorage.setItem(cacheKey, JSON.stringify({ data, ts: Date.now() }));
                        } catch (e) { }
                        return data;
                    }
                } catch (err) {
                    console.warn('[LaravelMapping] Gagal memuat boundary ' + url, err);
                }
                return null;
            };

            const getMarkerBeforeId = () => {
                return ['cluster_glow', 'cluster_circles', 'cluster_count', 'unclustered_points_halo', 'unclustered_points'].find(id => this.map.getLayer(id));
            };
            const layerBefore = getMarkerBeforeId();

            // 1. KOTA & TOPENG LUAR KOTA
            if (kotaUrl) {
                const kotaData = await fetchWithLocalCache(kotaUrl, 'mapping_boundary_kota_v1');
                if (kotaData && kotaData.features) {
                    this.boundaryGeoJson = kotaData;
                    if (this.config.boundary && this.config.boundary.fit_bounds !== false) {
                        this.fitGeoJsonBounds(kotaData);
                    }

                    // Masking luar kota
                    try {
                        const worldRing = [[-180, -90], [180, -90], [180, 90], [-180, 90], [-180, -90]];
                        let holes = [];
                        kotaData.features.forEach(f => {
                            if (f.geometry.type === 'Polygon') {
                                holes.push(f.geometry.coordinates[0]);
                            } else if (f.geometry.type === 'MultiPolygon') {
                                f.geometry.coordinates.forEach(poly => holes.push(poly[0]));
                            }
                        });
                        const maskData = {
                            type: 'FeatureCollection',
                            features: [{
                                type: 'Feature',
                                properties: {},
                                geometry: { type: 'Polygon', coordinates: [worldRing, ...holes] }
                            }]
                        };

                        if (!this.map.getSource('boundary_kota')) {
                            this.map.addSource('boundary_kota', { type: 'geojson', data: maskData });
                            const isMaskVisible = document.getElementById(`spatial-toggle-mask-${this.id}`)?.checked ?? true;
                            this.map.addLayer({
                                id: 'layer_mask_kota',
                                type: 'fill',
                                source: 'boundary_kota',
                                layout: { visibility: isMaskVisible ? 'visible' : 'none' },
                                paint: {
                                    'fill-color': '#0f172a',
                                    'fill-opacity': 0.65
                                }
                            }, layerBefore);
                        }
                    } catch (e) {
                        console.warn('[LaravelMapping] Mask kota error:', e);
                    }
                }
            }

            // 2. KECAMATAN
            if (kecUrl) {
                const kecData = await fetchWithLocalCache(kecUrl, 'mapping_boundary_kecamatan_v1');
                if (kecData && !this.map.getSource('boundary_kecamatan')) {
                    this.map.addSource('boundary_kecamatan', { type: 'geojson', data: kecData });

                    const isDistFill = document.getElementById(`spatial-toggle-districts-fill-${this.id}`)?.checked ?? false;
                    const isDistLine = document.getElementById(`spatial-toggle-districts-${this.id}`)?.checked ?? true;
                    const isLabels = document.getElementById(`spatial-toggle-boundary-labels-${this.id}`)?.checked ?? true;

                    this.map.addLayer({
                        id: 'layer_fill_kecamatan',
                        type: 'fill',
                        source: 'boundary_kecamatan',
                        layout: { visibility: isDistFill ? 'visible' : 'none' },
                        paint: {
                            'fill-color': buildColorExpr(kecData, 'nm_kecamatan', DISTRICT_PALETTE),
                            'fill-opacity': this.currentBoundaryFillOpacity || 0.25
                        }
                    }, layerBefore);

                    this.map.addLayer({
                        id: 'layer_boundary_kecamatan',
                        type: 'line',
                        source: 'boundary_kecamatan',
                        layout: { visibility: isDistLine ? 'visible' : 'none' },
                        paint: {
                            'line-color': '#f59e0b',
                            'line-width': 2,
                            'line-opacity': 0.8
                        }
                    }, layerBefore);

                    const mapStyleKec = this.map.getStyle();
                    const hasGlyphsKec = (mapStyleKec && typeof mapStyleKec.glyphs === 'string' && mapStyleKec.glyphs.length > 0) || (this.map.style && this.map.style.glyphs);
                    if (hasGlyphsKec) {
                        try {
                            this.map.addLayer({
                                id: 'layer_label_kecamatan',
                                type: 'symbol',
                                source: 'boundary_kecamatan',
                                layout: {
                                    visibility: isLabels ? 'visible' : 'none',
                                    'text-field': ['to-string', ['coalesce', ['get', 'nm_kecamatan'], ['get', 'nama_kecamatan'], '']],
                                    'text-font': ['Open Sans Semibold'],
                                    'text-size': ['interpolate', ['linear'], ['zoom'], 10, 11, 12, 13, 14, 15, 17, 18],
                                    'text-transform': 'uppercase',
                                    'text-letter-spacing': 0.08,
                                    'text-max-width': 8,
                                    'text-justify': 'center'
                                },
                                paint: {
                                    'text-color': '#ffffff',
                                    'text-halo-color': 'rgba(15, 23, 42, 0.95)',
                                    'text-halo-width': 2.5
                                }
                            }, layerBefore);
                        } catch (e) {
                            console.warn('[LaravelMapping] layer_label_kecamatan skipped:', e);
                        }
                    }
                }
            }

            // 3. KELURAHAN
            if (kelUrl) {
                const kelData = await fetchWithLocalCache(kelUrl, 'mapping_boundary_kelurahan_v1');
                if (kelData && !this.map.getSource('boundary_kelurahan')) {
                    this.map.addSource('boundary_kelurahan', { type: 'geojson', data: kelData });

                    const isSubdistFill = document.getElementById(`spatial-toggle-subdistricts-fill-${this.id}`)?.checked ?? false;
                    const isSubdistLine = document.getElementById(`spatial-toggle-subdistricts-${this.id}`)?.checked ?? false;
                    const isLabels = document.getElementById(`spatial-toggle-boundary-labels-${this.id}`)?.checked ?? true;

                    this.map.addLayer({
                        id: 'layer_fill_kelurahan',
                        type: 'fill',
                        source: 'boundary_kelurahan',
                        layout: { visibility: isSubdistFill ? 'visible' : 'none' },
                        paint: {
                            'fill-color': buildColorExpr(kelData, 'nm_kelurahan', SUBDISTRICT_PALETTE),
                            'fill-opacity': this.currentBoundaryFillOpacity || 0.25
                        }
                    }, layerBefore);

                    this.map.addLayer({
                        id: 'layer_boundary_kelurahan',
                        type: 'line',
                        source: 'boundary_kelurahan',
                        layout: { visibility: isSubdistLine ? 'visible' : 'none' },
                        paint: {
                            'line-color': '#a855f7',
                            'line-width': 1.2,
                            'line-opacity': 0.7,
                            'line-dasharray': [2, 2]
                        }
                    }, layerBefore);

                    const mapStyleKel = this.map.getStyle();
                    const hasGlyphsKel = mapStyleKel && typeof mapStyleKel.glyphs === 'string' && mapStyleKel.glyphs.length > 0;
                    if (hasGlyphsKel) {
                        try {
                            this.map.addLayer({
                                id: 'layer_label_kelurahan',
                                type: 'symbol',
                                source: 'boundary_kelurahan',
                                minzoom: 12.5,
                                layout: {
                                    visibility: isLabels ? 'visible' : 'none',
                                    'text-field': ['to-string', ['coalesce', ['get', 'nm_kelurahan'], ['get', 'nama_kelurahan'], '']],
                                    'text-font': ['Open Sans Semibold'],
                                    'text-size': ['interpolate', ['linear'], ['zoom'], 12.5, 10, 14, 12, 17, 14],
                                    'text-transform': 'uppercase',
                                    'text-max-width': 7
                                },
                                paint: {
                                    'text-color': '#e2e8f0',
                                    'text-halo-color': 'rgba(15, 23, 42, 0.95)',
                                    'text-halo-width': 2
                                }
                            }, layerBefore);
                        } catch (e) {
                            console.warn('[LaravelMapping] layer_label_kelurahan skipped:', e);
                        }
                    }
                }
            }

            // Pastikan layer marker dan cluster selalu berada di paling atas boundary
            this.bringMarkerLayersToFront();
        }

        bringMarkerLayersToFront() {
            if (!this.map) return;
            const markerLayers = [
                'cluster_glow',
                'cluster_circles',
                'cluster_count',
                'unclustered_points_halo',
                'unclustered_points'
            ];
            markerLayers.forEach(id => {
                if (this.map.getLayer(id)) {
                    try {
                        this.map.moveLayer(id);
                    } catch (e) { }
                }
            });
        }

        fitGeoJsonBounds(geojson) {
            if (!this.map || !window.maplibregl || !geojson) return;

            const bounds = new window.maplibregl.LngLatBounds();
            let hasCoordinates = false;

            const extractCoords = (coords) => {
                if (!Array.isArray(coords)) return;
                if (coords.length >= 2 && typeof coords[0] === 'number' && typeof coords[1] === 'number') {
                    bounds.extend([coords[0], coords[1]]);
                    hasCoordinates = true;
                } else {
                    coords.forEach(extractCoords);
                }
            };

            geojson.features.forEach(f => {
                if (f.geometry && f.geometry.coordinates) {
                    extractCoords(f.geometry.coordinates);
                }
            });

            if (hasCoordinates) {
                this.map.fitBounds(bounds, { padding: 40, maxZoom: 15, duration: 800 });
            }
        }

        setupMarkersLayer() {
            if (!this.config.markers || !Array.isArray(this.config.markers)) return;

            const features = [];
            this.config.markers.forEach(m => {
                const lng = parseFloat(m.lng);
                const lat = parseFloat(m.lat);
                if (!isNaN(lng) && !isNaN(lat)) {
                    features.push({
                        type: 'Feature',
                        geometry: { type: 'Point', coordinates: [lng, lat] },
                        properties: Object.assign({}, m)
                    });
                }
            });

            if (!this.map.getSource('markers_source')) {
                this.map.addSource('markers_source', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features },
                    cluster: this.config.cluster !== false,
                    clusterMaxZoom: 15,
                    clusterRadius: 50
                });
            }

            // 1. Cluster Outer Glow Aura
            if (!this.map.getLayer('cluster_glow')) {
                this.map.addLayer({
                    id: 'cluster_glow',
                    type: 'circle',
                    source: 'markers_source',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': '#0284c7',
                        'circle-radius': [
                            'step',
                            ['get', 'point_count'],
                            22,
                            10, 28,
                            50, 36
                        ],
                        'circle-opacity': 0.25
                    }
                });
            }

            // 2. Cluster Solid Circles
            if (!this.map.getLayer('cluster_circles')) {
                this.map.addLayer({
                    id: 'cluster_circles',
                    type: 'circle',
                    source: 'markers_source',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                            'step',
                            ['get', 'point_count'],
                            '#0284c7', // < 10 points
                            10, '#0369a1', // 10-49 points
                            50, '#1e3a8a'  // 50+ points
                        ],
                        'circle-radius': [
                            'step',
                            ['get', 'point_count'],
                            16, // < 10
                            10, 22, // 10-49
                            50, 28  // 50+
                        ],
                        'circle-stroke-width': 2.5,
                        'circle-stroke-color': '#ffffff'
                    }
                });
            }

            // 3. Cluster Count Number Text
            if (!this.map.getLayer('cluster_count')) {
                const defaultGlyphsUrl = (window.location.origin || '') + '/vendor/laravel-mapping/fonts/{fontstack}/{range}.pbf';
                if (typeof this.map.setGlyphs === 'function' && !this.map.getGlyphs()) {
                    try {
                        this.map.setGlyphs(defaultGlyphsUrl);
                    } catch (e) { }
                }
                if (this.map.style && !this.map.style.glyphs) {
                    this.map.style.glyphs = defaultGlyphsUrl;
                }

                try {
                    this.map.addLayer({
                        id: 'cluster_count',
                        type: 'symbol',
                        source: 'markers_source',
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-font': ['Open Sans Semibold'],
                            'text-size': 12,
                            'text-allow-overlap': true,
                            'text-ignore-placement': true
                        },
                        paint: {
                            'text-color': '#ffffff',
                            'text-halo-color': 'rgba(15, 23, 42, 0.95)',
                            'text-halo-width': 1.5
                        }
                    });
                } catch (e) {
                    console.warn('[LaravelMapping] cluster_count text layer failed:', e);
                }
            }

            // 4. Unclustered Point Markers (Glowing Marker Dots with Category & Status Colors)
            const markerColorExpression = [
                'case',
                ['has', 'color'], ['get', 'color'],
                ['has', 'marker_color'], ['get', 'marker_color'],
                ['has', 'status_color'], [
                    'match', ['get', 'status_color'],
                    'emerald', '#10b981',
                    'green', '#10b981',
                    'amber', '#f59e0b',
                    'yellow', '#eab308',
                    'blue', '#2563eb',
                    'sky', '#0284c7',
                    'purple', '#8b5cf6',
                    'violet', '#7c3aed',
                    'red', '#ef4444',
                    'rose', '#f43f5e',
                    'slate', '#64748b',
                    'gray', '#64748b',
                    ['get', 'status_color']
                ],
                ['match', ['get', 'category'],
                    'DALAM_PERJALANAN', '#2563eb',
                    'SAMPAI_LOKASI', '#d97706',
                    'PROSES_PENGERJAAN', '#059669',
                    'DITUGASKAN', '#7c3aed',
                    'SIAGA', '#64748b',
                    'PEMDA', '#38bdf8',
                    'CCTV', '#10b981',
                    'PDAM', '#0ea5e9',
                    'KEPENDUDUKAN', '#a855f7',
                    '#0284c7'
                ]
            ];

            if (!this.map.getLayer('unclustered_points_halo')) {
                this.map.addLayer({
                    id: 'unclustered_points_halo',
                    type: 'circle',
                    source: 'markers_source',
                    filter: ['!', ['has', 'point_count']],
                    paint: {
                        'circle-color': markerColorExpression,
                        'circle-radius': 14,
                        'circle-opacity': 0.25
                    }
                });
            }

            if (!this.map.getLayer('unclustered_points')) {
                this.map.addLayer({
                    id: 'unclustered_points',
                    type: 'circle',
                    source: 'markers_source',
                    filter: ['!', ['has', 'point_count']],
                    paint: {
                        'circle-color': markerColorExpression,
                        'circle-radius': 9,
                        'circle-stroke-width': 2.5,
                        'circle-stroke-color': '#ffffff'
                    }
                });
            }

            // 5. Click Interaction on Cluster -> Smooth Zoom In
            let lastClusterClickTime = 0;
            const handleClusterClick = (e) => {
                const now = Date.now();
                if (now - lastClusterClickTime < 350) return;
                lastClusterClickTime = now;

                const targetLayers = ['cluster_count', 'cluster_circles', 'cluster_glow'].filter(l => this.map.getLayer(l));
                const features = (e.features && e.features.length)
                    ? e.features
                    : (this.map.queryRenderedFeatures(e.point, { layers: targetLayers }) || []);
                if (!features || !features.length) return;

                const feat = features[0];
                const clusterId = feat.properties ? feat.properties.cluster_id : null;
                const coords = (feat.geometry && feat.geometry.coordinates)
                    ? feat.geometry.coordinates.slice()
                    : (e.lngLat ? [e.lngLat.lng, e.lngLat.lat] : this.initialCenter);
                const source = this.map.getSource('markers_source');

                let didZoom = false;
                const triggerZoom = (targetZoom) => {
                    if (didZoom) return;
                    didZoom = true;
                    this.map.easeTo({
                        center: coords,
                        zoom: Math.min(targetZoom, 17.5),
                        duration: 650,
                        essential: true
                    });
                };

                if (source && clusterId != null && typeof source.getClusterExpansionZoom === 'function') {
                    source.getClusterExpansionZoom(clusterId, (err, zoom) => {
                        const currentZoom = this.map.getZoom();
                        const nextZoom = (!err && typeof zoom === 'number' && zoom > currentZoom)
                            ? zoom
                            : currentZoom + 2.5;
                        triggerZoom(nextZoom);
                    });

                    // Safeguard timeout jika callback lambat atau titik bertumpukan
                    setTimeout(() => {
                        triggerZoom(this.map.getZoom() + 2.5);
                    }, 120);
                } else {
                    triggerZoom(this.map.getZoom() + 2.5);
                }
            };

            ['cluster_circles', 'cluster_count', 'cluster_glow'].forEach(layerId => {
                if (this.map.getLayer(layerId)) {
                    this.map.on('click', layerId, handleClusterClick);
                    this.map.on('mouseenter', layerId, () => { this.map.getCanvas().style.cursor = 'pointer'; });
                    this.map.on('mouseleave', layerId, () => { this.map.getCanvas().style.cursor = ''; });
                }
            });

            this.map.on('click', 'unclustered_points', (e) => {
                const props = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                // 1. Pan / focus kamera ke marker
                this.map.easeTo({ center: coords, zoom: Math.max(this.map.getZoom(), 13.5), duration: 600 });

                // 2. Buka Rich Sidebar Detail Drawer (100% Identik CCTV & PDAM)
                this.openEntityDrawer(props);

                // 3. Tampilkan Popup Info Ringkas di atas Marker
                new window.maplibregl.Popup({ offset: 14, className: 'mapping-popup-container' })
                    .setLngLat(coords)
                    .setHTML(`
                        <div class="mapping-popup-content">
                            <h4 class="mapping-popup-title">${props.title || 'Entitas Peta'}</h4>
                            ${props.subtitle ? `<p class="mapping-popup-subtitle">${props.subtitle}</p>` : ''}
                            ${props.category ? `<span class="mapping-badge">${props.category}</span>` : ''}
                        </div>
                    `)
                    .addTo(this.map);
            });

            this.map.on('mouseenter', 'unclustered_points', () => { this.map.getCanvas().style.cursor = 'pointer'; });
            this.map.on('mouseleave', 'unclustered_points', () => { this.map.getCanvas().style.cursor = ''; });

            // Pastikan layer marker berada di paling atas
            this.bringMarkerLayersToFront();

            if (this.isTruckTracking()) {
                ['cluster_glow', 'cluster_circles', 'cluster_count', 'unclustered_points_halo', 'unclustered_points'].forEach(l => {
                    if (this.map.getLayer(l)) {
                        this.map.setLayoutProperty(l, 'visibility', 'none');
                    }
                });
                this.renderDomTruckMarkers();
            }
            this.renderEntityList();
        }

        // ─── 3. SPATIAL INSPECTOR (MOUSEMOVE TELEMETRY & PIP) ─────────────────
        setupMouseMoveInspector() {
            const latEl = document.getElementById(`mapping-inspector-lat-${this.id}`);
            const lngEl = document.getElementById(`mapping-inspector-lng-${this.id}`);
            const zoomEl = document.getElementById(`mapping-inspector-zoom-${this.id}`);
            const regionEl = document.getElementById(`mapping-inspector-region-${this.id}`);

            if (!latEl && !zoomEl) return;

            let lastUpdate = 0;
            this.map.on('mousemove', (e) => {
                const now = performance.now();
                if (now - lastUpdate < 30) return; // Throttle ~30fps
                lastUpdate = now;

                const lat = e.lngLat.lat;
                const lng = e.lngLat.lng;

                if (latEl) latEl.textContent = lat.toFixed(6);
                if (lngEl) lngEl.textContent = lng.toFixed(6);

                if (regionEl && this.boundaryGeoJson) {
                    const detected = this.findRegionAtLngLat(lng, lat);
                    regionEl.textContent = detected || (this.config.boundary?.name || 'Kota Pekanbaru');
                }
            });

            this.map.on('zoom', () => {
                if (zoomEl) {
                    zoomEl.textContent = `Zoom: ${this.map.getZoom().toFixed(1)}`;
                }
            });
        }

        findRegionAtLngLat(lng, lat) {
            if (!this.boundaryGeoJson || !this.boundaryGeoJson.features) return null;
            const pt = [lng, lat];

            for (const f of this.boundaryGeoJson.features) {
                if (!f.geometry) continue;
                const type = f.geometry.type;
                const coords = f.geometry.coordinates;

                if (type === 'Polygon') {
                    if (isPointInPolygon(pt, coords[0])) {
                        return f.properties?.nama_kecamatan || f.properties?.NAME || f.properties?.name || f.properties?.WADMKC || f.properties?.KECAMATAN;
                    }
                } else if (type === 'MultiPolygon') {
                    for (const poly of coords) {
                        if (isPointInPolygon(pt, poly[0])) {
                            return f.properties?.nama_kecamatan || f.properties?.NAME || f.properties?.name || f.properties?.WADMKC || f.properties?.KECAMATAN;
                        }
                    }
                }
            }
            return null;
        }

        // ─── 4. ROTATION & COMPASS TRACKER ─────────────────────────────────────
        setupRotationTracker() {
            const needle = document.getElementById(`mapping-compass-needle-${this.id}`);
            if (!needle) return;

            const updateNeedle = () => {
                if (!this.map) return;
                const bearing = this.map.getBearing() || 0;
                needle.style.transform = `rotate(${-bearing}deg)`;
            };

            this.map.on('rotate', updateNeedle);
            this.map.on('pitch', updateNeedle);
        }

        resetRotation() {
            if (!this.map) return;
            this.map.easeTo({ bearing: 0, pitch: 0, duration: 600 });
        }

        resetMapView() {
            if (!this.map) return;
            this.map.flyTo({
                center: this.initialCenter,
                zoom: this.initialZoom,
                bearing: 0,
                pitch: 0,
                duration: 1000
            });
        }

        zoomIn() {
            if (this.map) this.map.zoomIn({ duration: 300 });
        }

        zoomOut() {
            if (this.map) this.map.zoomOut({ duration: 300 });
        }

        locateUserGps() {
            if (!navigator.geolocation) {
                alert('Fitur Geolocation tidak didukung oleh browser Anda.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lng = pos.coords.longitude;
                    const lat = pos.coords.latitude;
                    if (this.map) {
                        this.map.flyTo({ center: [lng, lat], zoom: 16, duration: 1200 });

                        if (!this.userGpsMarker && window.maplibregl) {
                            const el = document.createElement('div');
                            el.className = 'mapping-gps-dot';
                            el.innerHTML = '<div style="width:18px;height:18px;border-radius:50%;background:#10b981;border:3px solid #ffffff;box-shadow:0 0 14px #10b981;"></div>';
                            this.userGpsMarker = new window.maplibregl.Marker({ element: el })
                                .setLngLat([lng, lat])
                                .addTo(this.map);
                        } else if (this.userGpsMarker) {
                            this.userGpsMarker.setLngLat([lng, lat]);
                        }
                    }
                },
                (err) => {
                    console.warn('[LaravelMapping] Geolocation error:', err.message);
                    alert('Tidak dapat mendeteksi lokasi GPS Anda: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // ─── 5. SEARCH & CATEGORY FILTERING ────────────────────────────────────
        filterCategory(cat) {
            this.activeCategory = cat;

            // Update UI pills
            const pills = document.querySelectorAll(`#mapping-category-pills-${this.id} .mapping-pill`);
            pills.forEach(p => {
                const pCat = p.getAttribute('data-category');
                if (pCat && pCat.toUpperCase() === cat.toUpperCase()) {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });

            this.updateMarkersDisplay();
        }

        filterSearch(query) {
            this.searchQuery = (query || '').toLowerCase().trim();
            this.updateMarkersDisplay();
        }

        scrollCategoryFilters(direction) {
            const container = document.getElementById(`mapping-category-pills-${this.id}`);
            if (!container) return;
            const amount = direction === 'left' ? -220 : 220;
            container.scrollBy({ left: amount, behavior: 'smooth' });
            setTimeout(() => this.updatePillsScrollButtons(), 300);
        }

        updatePillsScrollButtons() {
            const container = document.getElementById(`mapping-category-pills-${this.id}`);
            const btnLeft = document.getElementById(`mapping-pills-scroll-left-${this.id}`);
            const btnRight = document.getElementById(`mapping-pills-scroll-right-${this.id}`);
            if (!container) return;

            const maxScroll = container.scrollWidth - container.clientWidth;
            if (maxScroll <= 4) {
                if (btnLeft) btnLeft.style.display = 'none';
                if (btnRight) btnRight.style.display = 'none';
                return;
            }

            if (btnLeft) {
                btnLeft.style.display = 'flex';
                const atStart = container.scrollLeft <= 4;
                btnLeft.disabled = atStart;
                btnLeft.classList.toggle('disabled', atStart);
                btnLeft.style.opacity = atStart ? '0.2' : '1';
                btnLeft.style.pointerEvents = atStart ? 'none' : 'auto';
            }

            if (btnRight) {
                btnRight.style.display = 'flex';
                const atEnd = container.scrollLeft >= maxScroll - 4;
                btnRight.disabled = atEnd;
                btnRight.classList.toggle('disabled', atEnd);
                btnRight.style.opacity = atEnd ? '0.2' : '1';
                btnRight.style.pointerEvents = atEnd ? 'none' : 'auto';
            }
        }

        setupCategoryPills() {
            const container = document.getElementById(`mapping-category-pills-${this.id}`);
            if (!container) return;

            container.addEventListener('scroll', () => this.updatePillsScrollButtons(), { passive: true });
            window.addEventListener('resize', () => this.updatePillsScrollButtons(), { passive: true });

            // 1. Mouse wheel horizontal scrolling
            container.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0 && container.scrollWidth > container.clientWidth) {
                    e.preventDefault();
                    container.scrollLeft += e.deltaY;
                    this.updatePillsScrollButtons();
                }
            }, { passive: false });

            // 2. Drag-to-scroll support
            let isDown = false;
            let startX = 0;
            let scrollStart = 0;

            container.addEventListener('mousedown', (e) => {
                isDown = true;
                container.classList.add('is-dragging');
                startX = e.pageX - container.offsetLeft;
                scrollStart = container.scrollLeft;
            });

            window.addEventListener('mouseup', () => {
                if (isDown) {
                    isDown = false;
                    container.classList.remove('is-dragging');
                }
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX) * 1.5;
                container.scrollLeft = scrollStart - walk;
                this.updatePillsScrollButtons();
            });

            // Initial checks after DOM renders
            setTimeout(() => this.updatePillsScrollButtons(), 150);
            setTimeout(() => this.updatePillsScrollButtons(), 500);
            setTimeout(() => this.updatePillsScrollButtons(), 1200);
        }

        setupFilterResizer() {
            const resizer = document.getElementById(`mapping-filter-resizer-${this.id}`);
            const drawer = document.getElementById(`mapping-filter-sidebar-${this.id}`);
            const panel = document.getElementById(`mapping-filter-panel-${this.id}`);
            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
            if (!resizer || !drawer || !panel || !wrapper) return;

            // Pulihkan lebar tersimpan (default 420px)
            const savedWidth = localStorage.getItem(`mapping_filter_sidebar_width_${this.id}`);
            if (savedWidth && parseInt(savedWidth) >= 320 && parseInt(savedWidth) <= 720) {
                drawer.style.width = `${savedWidth}px`;
                panel.style.width = `${savedWidth}px`;
            }

            let isResizing = false;

            const onPointerDown = (e) => {
                isResizing = true;
                resizer.classList.add('is-resizing');
                document.body.style.userSelect = 'none';
                document.body.style.cursor = 'col-resize';
                e.preventDefault();
            };

            const onPointerMove = (e) => {
                if (!isResizing) return;
                const wrapperRect = wrapper.getBoundingClientRect();
                // Menghitung lebar dari sisi kiri (panel berada di sisi kiri layar)
                const newWidth = Math.max(320, Math.min(Math.min(720, window.innerWidth - 60), e.clientX - wrapperRect.left));
                drawer.style.width = `${newWidth}px`;
                panel.style.width = `${newWidth}px`;
                localStorage.setItem(`mapping_filter_sidebar_width_${this.id}`, newWidth);
            };

            const onPointerUp = () => {
                if (!isResizing) return;
                isResizing = false;
                resizer.classList.remove('is-resizing');
                document.body.style.userSelect = '';
                document.body.style.cursor = '';
            };

            resizer.addEventListener('pointerdown', onPointerDown);
            document.addEventListener('pointermove', onPointerMove);
            document.addEventListener('pointerup', onPointerUp);
        }

        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                    const searchInput = document.getElementById(`mapping-complex-search-input-${this.id}`);
                    const sidebar = document.getElementById(`mapping-filter-sidebar-${this.id}`);
                    if (sidebar && (sidebar.style.display === 'none' || !sidebar.style.display)) {
                        this.toggleFilterSidebar();
                    }
                    if (searchInput) {
                        e.preventDefault();
                        setTimeout(() => {
                            try {
                                searchInput.focus({ preventScroll: true });
                            } catch (err) {
                                searchInput.focus();
                            }
                            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
                            if (wrapper) wrapper.scrollLeft = 0;
                        }, 50);
                    }
                }
            });
        }

        toggleFilterSidebar() {
            const sidebar = document.getElementById(`mapping-filter-sidebar-${this.id}`);
            if (!sidebar) return;
            const isHidden = (sidebar.style.display === 'none' || !sidebar.style.display);
            sidebar.style.display = isHidden ? 'flex' : 'none';

            // Pastikan wrapper tidak mengalami horizontal scroll displacement
            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
            if (wrapper) wrapper.scrollLeft = 0;

            // Tutup entity drawer kanan jika filter sidebar dibuka agar layar fokus
            if (isHidden) {
                this.closeEntityDrawer();

                const input = document.getElementById(`mapping-complex-search-input-${this.id}`);
                if (input) {
                    setTimeout(() => {
                        try {
                            input.focus({ preventScroll: true });
                        } catch (err) {
                            input.focus();
                        }
                        if (wrapper) wrapper.scrollLeft = 0;
                    }, 50);
                }
            }
        }

        setSearchType(type, buttonEl) {
            this.activeSearchType = type;
            const pills = document.querySelectorAll(`#omni-search-pills-${this.id} .omni-pill`);
            pills.forEach(p => p.classList.remove('is-active'));
            if (buttonEl) buttonEl.classList.add('is-active');

            const input = document.getElementById(`mapping-complex-search-input-${this.id}`);
            if (input && buttonEl && buttonEl.dataset.placeholder) {
                input.placeholder = buttonEl.dataset.placeholder;
                input.focus();
            }
            this.applyComplexFilter();
        }

        onComplexSearchInput(val) {
            this.complexSearchQuery = val || '';
            clearTimeout(this._searchDebounceTimer);
            this._searchDebounceTimer = setTimeout(() => {
                this.applyComplexFilter();
            }, 150);
        }

        selectAllCategories(select) {
            const checkboxes = document.querySelectorAll(`input[name="complex_filter_cat_${this.id}[]"]`);
            checkboxes.forEach(cb => cb.checked = select);
            this.applyComplexFilter();
        }

        onFilterFieldChange() {
            this.applyComplexFilter();
        }

        applyComplexFilter() {
            if (!this.map || !this.map.getSource('markers_source')) return;

            const query = (this.complexSearchQuery || '').trim().toLowerCase();
            const searchType = this.activeSearchType || 'ALL';

            const checkedCatBoxes = document.querySelectorAll(`input[name="complex_filter_cat_${this.id}[]"]:checked`);
            const activeCategories = Array.from(checkedCatBoxes).map(cb => cb.value.toUpperCase());

            const checkedStatusBoxes = document.querySelectorAll(`input[name="complex_filter_status_${this.id}[]"]:checked`);
            const activeStatuses = Array.from(checkedStatusBoxes).map(cb => cb.value.toLowerCase());

            const districtSelect = document.getElementById(`complex-filter-district-${this.id}`);
            const selectedDistrict = districtSelect ? districtSelect.value : 'ALL';

            let filtered = (this.config.markers || []).filter(m => {
                // 1. Kategori Filter
                const cat = (m.category || '').toUpperCase();
                if (activeCategories.length > 0 && !activeCategories.includes(cat)) {
                    return false;
                }

                // 2. Status Filter
                const status = (m.status || 'aktif').toLowerCase();
                if (activeStatuses.length > 0 && !activeStatuses.includes(status)) {
                    return false;
                }

                // 3. Wilayah / District Filter
                if (selectedDistrict && selectedDistrict !== 'ALL') {
                    const district = (m.district || '').toLowerCase();
                    if (district !== selectedDistrict.toLowerCase()) {
                        return false;
                    }
                }

                // 4. Omni Search Query Filter
                if (query) {
                    const id = (m.id || '').toString().toLowerCase();
                    const title = (m.title || '').toLowerCase();
                    const sub = (m.subtitle || '').toLowerCase();
                    const district = (m.district || '').toLowerCase();

                    if (searchType === 'id') {
                        return id.includes(query);
                    } else if (searchType === 'title') {
                        return title.includes(query) || sub.includes(query);
                    } else if (searchType === 'district') {
                        return district.includes(query);
                    } else {
                        return id.includes(query) || title.includes(query) || sub.includes(query) || district.includes(query) || cat.toLowerCase().includes(query);
                    }
                }

                return true;
            });

            // Render markers ke WebGL Canvas
            const features = [];
            filtered.forEach(m => {
                const lng = parseFloat(m.lng);
                const lat = parseFloat(m.lat);
                if (!isNaN(lng) && !isNaN(lat)) {
                    features.push({
                        type: 'Feature',
                        geometry: { type: 'Point', coordinates: [lng, lat] },
                        properties: Object.assign({}, m)
                    });
                }
            });

            this.map.getSource('markers_source').setData({
                type: 'FeatureCollection',
                features
            });

            // Update Counter Temuan
            const countEl = document.getElementById(`mapping-filter-found-count-${this.id}`);
            if (countEl) countEl.textContent = filtered.length;

            // Update Badge Aktif di Header Command Bar
            const filterBadge = document.getElementById(`mapping-active-filter-badge-${this.id}`);
            if (filterBadge) {
                const totalMarkers = (this.config.markers || []).length;
                if (filtered.length < totalMarkers) {
                    filterBadge.textContent = `${filtered.length}/${totalMarkers}`;
                    filterBadge.style.display = 'inline-block';
                } else {
                    filterBadge.style.display = 'none';
                }
            }

            // Update Status Telemetri di Bottom-Center
            this.updateTelemetry(filtered);

            // Update DOM Truck Markers jika mode tracking armada
            if (this.isTruckTracking()) {
                this.renderDomTruckMarkers(filtered);
            }

            // Update Cat Count di Drawer
            this.updateFilterCounts(filtered);

            // FlyTo jika hanya 1 titik ditemukan via pencarian
            if (query && features.length === 1) {
                this.map.flyTo({ center: features[0].geometry.coordinates, zoom: 16, duration: 800 });
            }
        }

        resetComplexFilters() {
            this.activeSearchType = 'ALL';
            this.complexSearchQuery = '';

            const input = document.getElementById(`mapping-complex-search-input-${this.id}`);
            if (input) input.value = '';

            const pills = document.querySelectorAll(`#omni-search-pills-${this.id} .omni-pill`);
            pills.forEach(p => {
                p.classList.toggle('is-active', p.dataset.key === 'ALL');
            });

            const catBoxes = document.querySelectorAll(`input[name="complex_filter_cat_${this.id}[]"]`);
            catBoxes.forEach(cb => cb.checked = true);

            const statusBoxes = document.querySelectorAll(`input[name="complex_filter_status_${this.id}[]"]`);
            statusBoxes.forEach(cb => cb.checked = true);

            const districtSelect = document.getElementById(`complex-filter-district-${this.id}`);
            if (districtSelect) districtSelect.value = 'ALL';

            this.applyComplexFilter();
        }

        updateFilterCounts(items) {
            const list = items || this.config.markers || [];
            (this.config.categories || []).forEach(cat => {
                const cnt = list.filter(m => (m.category || '').toUpperCase() === cat.toUpperCase()).length;
                const el = document.getElementById(`complex-cat-count-${this.id}-${cat.toLowerCase()}`);
                if (el) el.textContent = cnt;

                const pillCount = document.getElementById(`mapping-pill-count-${this.id}-${cat.toLowerCase()}`);
                if (pillCount) pillCount.textContent = cnt;
            });

            const pillCountAll = document.getElementById(`mapping-pill-count-${this.id}-all`);
            if (pillCountAll) pillCountAll.textContent = list.length;

            // Update status counts
            const aktifEl = document.getElementById(`filter-status-aktif-${this.id}`);
            if (aktifEl) {
                aktifEl.textContent = list.filter(m => (m.status || 'aktif').toLowerCase() === 'aktif' || (m.status || 'aktif').toLowerCase() === 'online').length;
            }
            const siagaEl = document.getElementById(`filter-status-siaga-${this.id}`);
            if (siagaEl) {
                siagaEl.textContent = list.filter(m => (m.status || '').toLowerCase() === 'siaga' || (m.status || '').toLowerCase() === 'warning').length;
            }
            const offlineEl = document.getElementById(`filter-status-offline-${this.id}`);
            if (offlineEl) {
                offlineEl.textContent = list.filter(m => (m.status || '').toLowerCase() === 'offline' || (m.status || '').toLowerCase() === 'nonaktif').length;
            }

            const totalEl = document.getElementById(`mapping-filter-found-count-${this.id}`);
            if (totalEl) totalEl.textContent = list.length;

            this.updatePillsScrollButtons();
        }

        resetFilters() {
            this.resetComplexFilters();
            this.toggleFilterSidebar();
        }

        updateMarkersDisplay() {
            this.applyComplexFilter();
        }

        // ─── 6. THEMES & GLASSMORPHISM 2.0 (100% IDENTIK CCTV & PDAM) ─────────
        loadThemeConfig() {
            try {
                const saved = localStorage.getItem(`mapping_theme_config_${this.id}`) ||
                    localStorage.getItem('spatial_theme_config') ||
                    localStorage.getItem('cctv_theme_config');
                if (saved) {
                    this.currentTheme = Object.assign(this.currentTheme, JSON.parse(saved));
                    if (this.currentTheme.preset && themePresets[this.currentTheme.preset]) {
                        const p = themePresets[this.currentTheme.preset];
                        this.currentTheme.glassBgBase = p.glassBg;
                        this.currentTheme.glassLightBgBase = p.glassLightBg;
                        this.currentTheme.glassBorderColor = p.glassBorder;
                    } else if (this.currentTheme.preset === 'custom' && this.currentTheme.themeAccent) {
                        const derived = deriveGlassBase(this.currentTheme.themeAccent);
                        this.currentTheme.glassBgBase = derived.bg;
                        this.currentTheme.glassLightBgBase = derived.lightBg;
                        this.currentTheme.glassBorderColor = derived.border;
                    }
                }
            } catch (e) { }
            this.applyThemeToDOM();
        }

        saveThemeConfig() {
            try {
                const json = JSON.stringify(this.currentTheme);
                localStorage.setItem(`mapping_theme_config_${this.id}`, json);
                localStorage.setItem('spatial_theme_config', json);
                localStorage.setItem('cctv_theme_config', json);
            } catch (e) { }
        }

        applyThemeToDOM() {
            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`);
            const targets = [document.documentElement];
            if (wrapper) targets.push(wrapper);

            targets.forEach(el => {
                el.style.setProperty('--theme-accent', this.currentTheme.themeAccent);
                el.style.setProperty('--theme-accent-hover', this.currentTheme.themeAccentHover || this.currentTheme.themeAccent);
                el.style.setProperty('--theme-accent-light', this.currentTheme.themeAccentLight || this.currentTheme.themeAccent);
                el.style.setProperty('--theme-accent-rgb', this.currentTheme.themeAccentRgb || hexToRgb(this.currentTheme.themeAccent));
                el.style.setProperty('--glass-bg-base', this.currentTheme.glassBgBase || '8, 28, 52');
                el.style.setProperty('--glass-light-bg-base', this.currentTheme.glassLightBgBase || '14, 44, 78');
                el.style.setProperty('--glass-border-color', this.currentTheme.glassBorderColor || '30, 92, 150');
                el.style.setProperty('--glass-opacity', this.currentTheme.glassOpacity);
                el.style.setProperty('--glass-blur', this.currentTheme.glassBlur + 'px');
                el.style.setProperty('--glass-saturation', this.currentTheme.glassSaturation + '%');
                el.style.setProperty('--glass-border-opacity', this.currentTheme.borderOpacity);

                // Sinkronkan compatibility legacy variables
                el.style.setProperty('--mapping-accent', this.currentTheme.themeAccent);
                el.style.setProperty('--mapping-accent-hover', this.currentTheme.themeAccentHover || this.currentTheme.themeAccent);
                el.style.setProperty('--mapping-accent-light', this.currentTheme.themeAccentLight || this.currentTheme.themeAccent);
                el.style.setProperty('--mapping-glow', `rgba(${this.currentTheme.themeAccentRgb || hexToRgb(this.currentTheme.themeAccent)}, 0.25)`);
            });

            // Update wrapper class
            if (wrapper) {
                wrapper.className = wrapper.className.replace(/\btheme-\S+/g, '').trim();
                if (this.currentTheme.preset && this.currentTheme.preset !== 'custom') {
                    wrapper.classList.add(`theme-${this.currentTheme.preset}`);
                }
            }

            // Update Preset Buttons Active State di dalam Theme Modal
            const modal = document.getElementById(`spatial-theme-modal-${this.id}`);
            if (modal) {
                modal.querySelectorAll('.theme-preset-btn').forEach(btn => {
                    if (btn.getAttribute('data-preset') === this.currentTheme.preset) {
                        btn.classList.add('is-active');
                    } else {
                        btn.classList.remove('is-active');
                    }
                });
            }

            // Update nama preset aktif & custom hex
            const presetName = document.getElementById(`theme-active-preset-name-${this.id}`);
            if (presetName) presetName.textContent = themePresets[this.currentTheme.preset]?.name || 'Custom Color';

            const hexInput = document.getElementById(`theme-color-hex-${this.id}`);
            if (hexInput) hexInput.textContent = (this.currentTheme.themeAccent || '').toUpperCase();

            const colorPicker = document.getElementById(`theme-custom-color-${this.id}`);
            if (colorPicker && this.currentTheme.themeAccent.startsWith('#')) {
                colorPicker.value = this.currentTheme.themeAccent;
            }

            // Update nilai slider & teks persentase
            const opSlider = document.getElementById(`theme-slider-opacity-${this.id}`);
            if (opSlider) opSlider.value = Math.round(this.currentTheme.glassOpacity * 100);
            const opVal = document.getElementById(`theme-val-opacity-${this.id}`);
            if (opVal) opVal.textContent = Math.round(this.currentTheme.glassOpacity * 100) + '%';

            const blurSlider = document.getElementById(`theme-slider-blur-${this.id}`);
            if (blurSlider) blurSlider.value = this.currentTheme.glassBlur;
            const blurVal = document.getElementById(`theme-val-blur-${this.id}`);
            if (blurVal) blurVal.textContent = this.currentTheme.glassBlur + 'px';

            const satSlider = document.getElementById(`theme-slider-sat-${this.id}`);
            if (satSlider) satSlider.value = this.currentTheme.glassSaturation;
            const satVal = document.getElementById(`theme-val-sat-${this.id}`);
            if (satVal) satVal.textContent = this.currentTheme.glassSaturation + '%';

            const borderSlider = document.getElementById(`theme-slider-border-${this.id}`);
            if (borderSlider) borderSlider.value = Math.round(this.currentTheme.borderOpacity * 100);
            const borderVal = document.getElementById(`theme-val-border-${this.id}`);
            if (borderVal) borderVal.textContent = Math.round(this.currentTheme.borderOpacity * 100) + '%';

            const previewDot = document.getElementById(`theme-preview-dot-${this.id}`);
            if (previewDot) previewDot.style.backgroundColor = this.currentTheme.themeAccent;

            const previewBtn = document.getElementById(`theme-preview-btn-${this.id}`);
            if (previewBtn) previewBtn.style.backgroundColor = this.currentTheme.themeAccent;

            const saveBtn = document.getElementById(`theme-save-btn-${this.id}`);
            if (saveBtn) saveBtn.style.backgroundColor = this.currentTheme.themeAccent;
        }

        setThemePreset(presetKey) {
            const p = themePresets[presetKey];
            if (!p) return;
            this.currentTheme.preset = presetKey;
            this.currentTheme.themeAccent = p.accent;
            this.currentTheme.themeAccentHover = p.hover;
            this.currentTheme.themeAccentLight = p.light;
            this.currentTheme.themeAccentRgb = p.rgb;
            this.currentTheme.glassBgBase = p.glassBg;
            this.currentTheme.glassLightBgBase = p.glassLightBg;
            this.currentTheme.glassBorderColor = p.glassBorder;
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        setCustomThemeColor(hex) {
            this.currentTheme.preset = 'custom';
            this.currentTheme.themeAccent = hex;
            this.currentTheme.themeAccentHover = hex;
            this.currentTheme.themeAccentLight = hex;
            this.currentTheme.themeAccentRgb = hexToRgb(hex);
            const derived = deriveGlassBase(hex);
            this.currentTheme.glassBgBase = derived.bg;
            this.currentTheme.glassLightBgBase = derived.lightBg;
            this.currentTheme.glassBorderColor = derived.border;
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        setGlassOpacity(val) {
            this.currentTheme.glassOpacity = parseFloat(val) / 100;
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        setGlassBlur(val) {
            this.currentTheme.glassBlur = parseInt(val, 10);
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        setGlassSaturation(val) {
            this.currentTheme.glassSaturation = parseInt(val, 10);
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        setGlassBorder(val) {
            this.currentTheme.borderOpacity = parseFloat(val) / 100;
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        resetThemeDefaults() {
            const p = themePresets.sky;
            this.currentTheme = {
                preset: 'sky',
                themeAccent: p.accent,
                themeAccentHover: p.hover,
                themeAccentLight: p.light,
                themeAccentRgb: p.rgb,
                glassBgBase: p.glassBg,
                glassLightBgBase: p.glassLightBg,
                glassBorderColor: p.glassBorder,
                glassOpacity: 0.88,
                glassBlur: 16,
                glassSaturation: 180,
                borderOpacity: 0.35
            };
            this.applyThemeToDOM();
            this.saveThemeConfig();
        }

        openThemeModal() {
            const modal = document.getElementById(`spatial-theme-modal-${this.id}`) ||
                document.getElementById(`mapping-theme-modal-${this.id}`);
            if (modal) {
                modal.style.display = 'flex';
                this.applyThemeToDOM();
            }
        }

        closeThemeModal() {
            const modal = document.getElementById(`spatial-theme-modal-${this.id}`) ||
                document.getElementById(`mapping-theme-modal-${this.id}`);
            if (modal) modal.style.display = 'none';
        }

        openBasemapModal() {
            const modal = document.getElementById(`mapping-basemap-modal-${this.id}`);
            if (modal) modal.style.display = 'flex';
        }

        closeBasemapModal() {
            const modal = document.getElementById(`mapping-basemap-modal-${this.id}`);
            if (modal) modal.style.display = 'none';
        }

        openAboutModal() {
            const modal = document.getElementById(`mapping-about-modal-${this.id}`);
            if (modal) modal.style.display = 'flex';
        }

        closeAboutModal() {
            const modal = document.getElementById(`mapping-about-modal-${this.id}`);
            if (modal) modal.style.display = 'none';
        }

        // ─── 7. SIDEBAR DETAIL DRAWER & RESIZER (100% IDENTIK) ────────────────
        setupDrawerResizer() {
            const resizer = document.getElementById(`spatial-drawer-resizer-${this.id}`);
            const drawer = document.getElementById(`spatial-entity-drawer-${this.id}`);
            if (!resizer || !drawer || resizer._resizerInitialized) return;
            resizer._resizerInitialized = true;

            // Pulihkan lebar tersimpan dari localStorage
            try {
                const savedWidth = parseInt(localStorage.getItem('spatial_detail_drawer_width') || '440', 10);
                if (savedWidth && window.innerWidth >= 640) {
                    const maxW = Math.min(window.innerWidth * 0.75, 850);
                    const clamped = Math.min(Math.max(savedWidth, 340), maxW);
                    drawer.style.width = clamped + 'px';
                }
            } catch (e) { }

            let isResizing = false;
            let startX = 0;
            let startWidth = 440;

            const startResize = (e) => {
                if (window.innerWidth < 640) return;
                e.preventDefault();
                isResizing = true;
                startX = e.clientX;
                startWidth = drawer.offsetWidth;

                drawer.style.transition = 'none';
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';

                const onPointerMove = (ev) => {
                    if (!isResizing) return;
                    const deltaX = startX - ev.clientX;
                    let newWidth = startWidth + deltaX;

                    const minWidth = 340;
                    const maxAllowed = window.innerWidth - 120;
                    const maxWidth = Math.max(minWidth, Math.min(maxAllowed, 850));

                    if (newWidth < minWidth) newWidth = minWidth;
                    if (newWidth > maxWidth) newWidth = maxWidth;

                    drawer.style.width = Math.round(newWidth) + 'px';
                };

                const onPointerUp = () => {
                    if (!isResizing) return;
                    isResizing = false;
                    drawer.style.transition = 'transform 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';

                    window.removeEventListener('pointermove', onPointerMove);
                    window.removeEventListener('pointerup', onPointerUp);
                    window.removeEventListener('pointercancel', onPointerUp);

                    try {
                        const finalWidth = drawer.offsetWidth;
                        localStorage.setItem('spatial_detail_drawer_width', finalWidth);
                    } catch (err) { }

                    if (this.map) {
                        try { this.map.resize(); } catch (err) { }
                    }
                };

                window.addEventListener('pointermove', onPointerMove);
                window.addEventListener('pointerup', onPointerUp);
                window.addEventListener('pointercancel', onPointerUp);
            };

            resizer.addEventListener('pointerdown', startResize);

            window.addEventListener('resize', () => {
                if (window.innerWidth < 640) {
                    drawer.style.width = '';
                } else {
                    try {
                        const saved = parseInt(localStorage.getItem('spatial_detail_drawer_width') || '440', 10);
                        if (saved && !drawer.style.width) drawer.style.width = saved + 'px';
                    } catch (e) { }
                }
            });
        }

        openEntityDrawer(props) {
            this.activeEntity = props;
            const drawer = document.getElementById(`spatial-entity-drawer-${this.id}`);
            if (!drawer) return;

            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
            if (wrapper) wrapper.scrollLeft = 0;

            // Badges Status
            const statusBadge = document.getElementById(`drawer-status-badge-${this.id}`);
            if (statusBadge) {
                const statusUpper = (props.status || 'AKTIF').toUpperCase();
                statusBadge.textContent = props.status_label || statusUpper;
                let badgeClass = 'badge-active';
                if (props.status_color) {
                    badgeClass = `badge-${props.status_color}`;
                } else if (['DALAM_PERJALANAN', 'OTW', 'BLUE', 'PRIMARY'].includes(statusUpper)) {
                    badgeClass = 'badge-blue';
                } else if (['SAMPAI_LOKASI', 'TIBA', 'SIAGA', 'WARNING', 'AMBER', 'YELLOW'].includes(statusUpper)) {
                    badgeClass = 'badge-warning';
                } else if (['PROSES_PENGERJAAN', 'AKTIF', 'ONLINE', 'EMERALD', 'GREEN'].includes(statusUpper)) {
                    badgeClass = 'badge-active';
                } else if (['DITUGASKAN', 'PURPLE', 'VIOLET'].includes(statusUpper)) {
                    badgeClass = 'badge-purple';
                } else if (['OFFLINE', 'NONAKTIF', 'RUSAK', 'DANGER', 'RED', 'ROSE'].includes(statusUpper)) {
                    badgeClass = 'badge-danger';
                } else {
                    badgeClass = 'badge-slate';
                }
                statusBadge.className = `drawer-status-badge ${badgeClass}`;
            }

            // Category Badge
            const catBadge = document.getElementById(`drawer-category-badge-${this.id}`);
            if (catBadge) catBadge.textContent = props.category_label || props.category || 'UMUM';

            // Title & Subtitle
            const titleEl = document.getElementById(`drawer-entity-title-${this.id}`);
            if (titleEl) titleEl.textContent = props.title || 'Detail Entitas';

            const subtitleEl = document.getElementById(`drawer-entity-subtitle-${this.id}`);
            if (subtitleEl) subtitleEl.textContent = props.subtitle || props.address || 'Objek Spasial Terdata';

            // Google Maps Route Action
            const gmapsLink = document.getElementById(`drawer-gmaps-link-${this.id}`);
            if (gmapsLink && props.lat && props.lng) {
                gmapsLink.href = `https://www.google.com/maps?q=${props.lat},${props.lng}`;
            }

            // Media Documentation Box
            const imgEl = document.getElementById(`drawer-entity-image-${this.id}`);
            const placeholderEl = document.getElementById(`drawer-media-placeholder-${this.id}`);
            const imageUrl = props.thumbnail_url || props.image || props.thumbnail;
            if (imageUrl && imgEl) {
                imgEl.src = imageUrl;
                imgEl.style.display = 'block';
                if (placeholderEl) placeholderEl.style.display = 'none';
            } else if (imgEl) {
                imgEl.style.display = 'none';
                if (placeholderEl) placeholderEl.style.display = 'flex';
            }

            // Coordinates & Region Cards
            const coordsVal = document.getElementById(`drawer-coords-val-${this.id}`);
            if (coordsVal && props.lat && props.lng) {
                coordsVal.textContent = `${parseFloat(props.lat).toFixed(6)}, ${parseFloat(props.lng).toFixed(6)}`;
            }

            const regionVal = document.getElementById(`drawer-region-val-${this.id}`);
            if (regionVal) {
                regionVal.textContent = props.subdistrict || props.district || props.region || (this.config.boundary?.name || 'Kota Pekanbaru');
            }

            const catVal = document.getElementById(`drawer-category-val-${this.id}`);
            if (catVal) catVal.textContent = props.category_label || props.category || '-';

            const statusVal = document.getElementById(`drawer-status-val-${this.id}`);
            if (statusVal) statusVal.textContent = (props.status_label || props.status || 'AKTIF').toUpperCase();

            // Dynamic Extra Attributes (Supports Object/Array & Flat Properties)
            const extraContainer = document.getElementById(`drawer-extra-props-${this.id}`);
            if (extraContainer) {
                let rowsHtml = '';

                // 1. Structured attributes from SpatialEntityDTO
                if (props.attributes) {
                    let attrs = props.attributes;
                    if (typeof attrs === 'string') {
                        try { attrs = JSON.parse(attrs); } catch (e) { attrs = {}; }
                    }
                    if (typeof attrs === 'object' && attrs !== null) {
                        Object.entries(attrs).forEach(([k, v]) => {
                            if (v !== null && v !== undefined && typeof v !== 'object') {
                                rowsHtml += `
                                    <div class="drawer-prop-row">
                                        <span class="drawer-prop-key">${k.replace(/_/g, ' ')}</span>
                                        <span class="drawer-prop-val">${v}</span>
                                    </div>
                                `;
                            }
                        });
                    }
                }

                // 2. Extra flat properties
                const excluded = ['title', 'subtitle', 'category', 'category_label', 'status', 'status_label', 'status_color', 'color', 'marker_color', 'lat', 'lng', 'thumbnail', 'thumbnail_url', 'image', 'attributes', 'actions', 'badges', 'extra'];
                Object.keys(props).forEach(k => {
                    if (!excluded.includes(k) && props[k] !== null && props[k] !== undefined && typeof props[k] !== 'object') {
                        rowsHtml += `
                            <div class="drawer-prop-row">
                                <span class="drawer-prop-key">${k.replace(/_/g, ' ')}</span>
                                <span class="drawer-prop-val">${props[k]}</span>
                            </div>
                        `;
                    }
                });

                if (rowsHtml) {
                    extraContainer.style.display = 'flex';
                    extraContainer.innerHTML = rowsHtml;
                } else {
                    extraContainer.style.display = 'none';
                    extraContainer.innerHTML = '';
                }
            }

            // Custom Action Buttons
            const actionsContainer = document.getElementById(`drawer-custom-actions-${this.id}`);
            if (actionsContainer) {
                let actions = props.actions;
                if (typeof actions === 'string') {
                    try { actions = JSON.parse(actions); } catch (e) { actions = []; }
                }
                if (Array.isArray(actions) && actions.length > 0) {
                    actionsContainer.style.display = 'flex';
                    actionsContainer.innerHTML = actions.map(act => {
                        const href = act.payload || act.url || '#';
                        const target = act.target ? `target="${act.target}"` : '';
                        const btnClass = act.variant === 'secondary' ? 'btn-action-secondary' : 'btn-action-primary';
                        return `
                            <a href="${href}" ${target} class="drawer-action-btn ${btnClass}">
                                ${act.icon ? `<span>${act.icon}</span>` : ''}
                                <span>${act.label}</span>
                            </a>
                        `;
                    }).join('');
                } else {
                    actionsContainer.style.display = 'none';
                    actionsContainer.innerHTML = '';
                }
            }

            // Slide In Drawer Panel
            drawer.style.transform = 'translateX(0)';

            // Dispatch Custom Event
            window.dispatchEvent(new CustomEvent('mapping:entity-selected', { detail: { id: this.id, entity: props } }));
        }

        openEntityDrawerById(id) {
            const found = (this.config.markers || []).find(m => String(m.id) === String(id));
            if (found) {
                const lng = parseFloat(found.lng);
                const lat = parseFloat(found.lat);
                if (!isNaN(lng) && !isNaN(lat) && this.map) {
                    this.map.easeTo({ center: [lng, lat], zoom: Math.max(this.map.getZoom(), 14), duration: 600 });
                }
                this.openEntityDrawer(found);
            }
        }

        closeEntityDrawer() {
            const drawer = document.getElementById(`spatial-entity-drawer-${this.id}`);
            if (drawer) {
                drawer.style.transform = 'translateX(100%)';
            }
            this.activeEntity = null;
            const wrapper = document.getElementById(`mapping-wrapper-${this.id}`) || this.container;
            if (wrapper) wrapper.scrollLeft = 0;
            window.dispatchEvent(new CustomEvent('mapping:entity-deselected', { detail: { id: this.id } }));
        }

        copyCoordinates() {
            if (!this.activeEntity || !this.activeEntity.lat || !this.activeEntity.lng) return;
            const text = `${this.activeEntity.lat}, ${this.activeEntity.lng}`;
            navigator.clipboard.writeText(text).then(() => {
                alert(`Koordinat ${text} berhasil disalin ke clipboard!`);
            }).catch(() => {
                prompt('Salin koordinat:', text);
            });
        }

        // ─── 8. BOTTOM TELEMETRY BAR & TOOLTIPS (100% IDENTIK KEPENDUDUKAN) ───
        toggleTelemetryTooltip(key, event) {
            if (event) {
                event.stopPropagation();
            }
            const container = document.getElementById(`telemetry-pill-container-${this.id}`);
            if (!container) return;

            const targetItem = document.getElementById(`telemetry-item-${this.id}-${key}`);
            const wasActive = targetItem ? targetItem.classList.contains('is-active') : false;

            // Tutup semua popover yang sedang aktif
            container.querySelectorAll('.telemetry-metric-item').forEach(el => {
                el.classList.remove('is-active');
            });

            // Jika sebelumnya belum aktif, aktifkan popover target
            if (!wasActive && targetItem) {
                targetItem.classList.add('is-active');

                const closeHandler = (e) => {
                    if (!targetItem.contains(e.target)) {
                        targetItem.classList.remove('is-active');
                        document.removeEventListener('click', closeHandler);
                        document.removeEventListener('touchstart', closeHandler);
                    }
                };
                setTimeout(() => {
                    document.addEventListener('click', closeHandler);
                    document.addEventListener('touchstart', closeHandler);
                }, 10);
            }
        }

        updateTelemetry(items) {
            const totalCount = typeof items === 'number' ? items : (Array.isArray(items) ? items.length : 0);

            const totalCountEl = document.getElementById(`telemetry-total-count-${this.id}`);
            if (totalCountEl) totalCountEl.textContent = totalCount;

            const totalMobileEl = document.getElementById(`telemetry-total-count-mobile-${this.id}`);
            if (totalMobileEl) totalMobileEl.textContent = totalCount;

            if (Array.isArray(items)) {
                // Aktif / Online
                const aktifEl = document.getElementById(`telemetry-metric-${this.id}-aktif`) || document.getElementById(`telemetry-metric-aktif-${this.id}`);
                const aktifMobileEl = document.getElementById(`telemetry-metric-mobile-${this.id}-aktif`);
                const aktifCount = items.filter(m => {
                    const st = (m.status || 'aktif').toLowerCase();
                    return st === 'aktif' || st === 'online';
                }).length;
                if (aktifEl) aktifEl.textContent = aktifCount;
                if (aktifMobileEl) aktifMobileEl.textContent = aktifCount;

                // Siaga / Warning
                const siagaEl = document.getElementById(`telemetry-metric-${this.id}-siaga`) || document.getElementById(`telemetry-metric-siaga-${this.id}`);
                const siagaMobileEl = document.getElementById(`telemetry-metric-mobile-${this.id}-siaga`);
                const siagaCount = items.filter(m => {
                    const st = (m.status || '').toLowerCase();
                    return st === 'siaga' || st === 'warning';
                }).length;
                if (siagaEl) siagaEl.textContent = siagaCount;
                if (siagaMobileEl) siagaMobileEl.textContent = siagaCount;

                // Offline / Nonaktif
                const offlineEl = document.getElementById(`telemetry-metric-${this.id}-offline`) || document.getElementById(`telemetry-metric-offline-${this.id}`);
                const offlineMobileEl = document.getElementById(`telemetry-metric-mobile-${this.id}-offline`);
                const offlineCount = items.filter(m => {
                    const st = (m.status || '').toLowerCase();
                    return st === 'offline' || st === 'nonaktif';
                }).length;
                if (offlineEl) offlineEl.textContent = offlineCount;
                if (offlineMobileEl) offlineMobileEl.textContent = offlineCount;

                // Generic metrics if defined in custom telemetry
                (this.config.telemetryMetrics || []).forEach(m => {
                    const k = m.key;
                    if (k !== 'aktif' && k !== 'siaga' && k !== 'offline') {
                        const mEl = document.getElementById(`telemetry-metric-${this.id}-${k}`);
                        const mMobileEl = document.getElementById(`telemetry-metric-mobile-${this.id}-${k}`);
                        const cnt = items.filter(it => (it.status || '').toLowerCase() === k.toLowerCase()).length;
                        if (mEl) mEl.textContent = cnt;
                        if (mMobileEl) mMobileEl.textContent = cnt;
                    }
                });
            }
        }

        setMarkers(newMarkers) {
            this.config.markers = Array.isArray(newMarkers) ? newMarkers : [];
            this.applyComplexFilter();
            this.updateTelemetry(this.config.markers);
            this.updateFilterCounts();
            if (this.isTruckTracking()) {
                this.renderDomTruckMarkers();
            }
            this.renderEntityList();
        }

        isTruckTracking() {
            return this.config.markerType === 'truck' ||
                (this.config.markers && this.config.markers.some(m => m.marker_type === 'truck' || ['DALAM_PERJALANAN', 'SAMPAI_LOKASI', 'PROSES_PENGERJAAN', 'DITUGASKAN', 'SIAGA'].includes(m.category)));
        }

        renderDomTruckMarkers(customList) {
            if (!this.map || !window.maplibregl) return;
            this.domTruckMarkers = this.domTruckMarkers || {};

            const currentMarkers = customList || this.config.markers || [];
            const currentIds = new Set(currentMarkers.map(m => String(m.id)));

            // Hapus marker yang sudah tidak aktif atau terfilter
            Object.keys(this.domTruckMarkers).forEach(id => {
                if (!currentIds.has(id)) {
                    this.domTruckMarkers[id].remove();
                    delete this.domTruckMarkers[id];
                }
            });

            currentMarkers.forEach(m => {
                const lng = parseFloat(m.lng);
                const lat = parseFloat(m.lat);
                if (isNaN(lng) || isNaN(lat)) return;

                const id = String(m.id);
                const colorClass = m.status_color || 'blue';
                const markerColor = (m.extra && m.extra.color) || '#2563eb';
                const armadaCode = (m.extra && m.extra.armada_code) || (m.title.split(' ')[1] ? m.title.split(' ')[1] : m.title);

                if (this.domTruckMarkers[id]) {
                    this.domTruckMarkers[id].setLngLat([lng, lat]);
                    if (m.extra && m.extra.heading !== undefined && typeof this.domTruckMarkers[id].setRotation === 'function') {
                        this.domTruckMarkers[id].setRotation(m.extra.heading);
                    }
                    return;
                }

                const wrap = document.createElement('div');
                wrap.className = 'truck-marker-wrap';
                wrap.id = `truck-marker-${id}`;
                wrap.style.position = 'absolute';
                wrap.style.top = '0';
                wrap.style.left = '0';
                wrap.style.willChange = 'transform';

                const beacon = document.createElement('div');
                beacon.className = `truck-marker-beacon ${colorClass}`;
                if (m.extra && m.extra.color) beacon.style.backgroundColor = m.extra.color;
                wrap.appendChild(beacon);

                const pin = document.createElement('div');
                pin.className = 'truck-marker-pin';
                if (m.extra && m.extra.color) pin.style.backgroundColor = m.extra.color;
                pin.innerHTML = `
                    <svg viewBox="0 0 24 24">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-2 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>
                `;
                wrap.appendChild(pin);

                const label = document.createElement('div');
                label.className = 'truck-marker-label';
                label.id = `truck-label-${id}`;
                label.innerText = armadaCode;
                wrap.appendChild(label);

                wrap.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.focusEntity(m.id);
                });

                const marker = new window.maplibregl.Marker({
                    element: wrap,
                    anchor: 'center',
                    rotation: m.extra && m.extra.heading ? m.extra.heading : 0
                })
                    .setLngLat([lng, lat])
                    .addTo(this.map);

                this.domTruckMarkers[id] = marker;
            });
        }

        updateEntityLocation(entityId, lat, lng, speed, heading) {
            const id = String(entityId);
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            if (isNaN(lat) || isNaN(lng)) return;

            if (this.domTruckMarkers && this.domTruckMarkers[id]) {
                this.domTruckMarkers[id].setLngLat([lng, lat]);
                if (heading !== undefined && typeof this.domTruckMarkers[id].setRotation === 'function') {
                    this.domTruckMarkers[id].setRotation(heading);
                }
            }

            if (this.config.markers) {
                const item = this.config.markers.find(m => String(m.id) === id);
                if (item) {
                    item.lat = lat;
                    item.lng = lng;
                    if (item.attributes) {
                        if (speed !== undefined) item.attributes['Kecepatan GPS'] = Math.round(speed) + ' km/jam';
                        item.attributes['Pembaruan Terakhir'] = 'Baru saja';
                    }
                }
                this.updateMarkersData(this.config.markers);
            }

            const labelEl = document.getElementById(`truck-label-${id}`);
            if (labelEl && speed !== undefined) {
                const base = labelEl.innerText.split(' • ')[0];
                labelEl.innerText = speed > 0 ? `${base} • ${Math.round(speed)} km/h` : base;
            }
        }

        renderEntityList() {
            const container = document.getElementById(`entity-list-items-${this.id}`);
            const badgeCount = document.getElementById(`entity-list-badge-count-${this.id}`);
            if (!container) return;

            const markers = this.config.markers || [];
            if (badgeCount) badgeCount.textContent = markers.length;

            if (markers.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-slate-500 text-xs font-medium">
                        Tidak ada titik / armada aktif
                    </div>
                `;
                return;
            }

            let html = '';
            markers.forEach(m => {
                const color = (m.extra && m.extra.color) || '#2563eb';
                const statusLabel = m.status_label || m.status || m.category || 'AKTIF';
                const subtitle = m.subtitle || (m.attributes ? m.attributes['Operator / Supir'] : '') || '';
                const plate = m.attributes ? m.attributes['No. Plat Polisi'] : '';
                const speed = m.attributes ? m.attributes['Kecepatan GPS'] : '';

                html += `
                    <div class="entity-list-item-card" onclick="window.LaravelMapping.get('${this.id}')?.focusEntity('${m.id}')">
                        <div class="entity-card-left">
                            <div class="entity-card-icon" style="background: ${color}20; color: ${color}; border-color: ${color}45;">
                                🚛
                            </div>
                            <div class="entity-card-info">
                                <span class="entity-card-title">${m.title}</span>
                                <span class="entity-card-subtitle">${subtitle}</span>
                                ${speed || plate ? `<span class="entity-card-meta">${speed || ''}${speed && plate ? ' • ' : ''}${plate || ''}</span>` : ''}
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background: ${color}25; color: ${color}; border: 1px solid ${color}50;">
                            ${statusLabel}
                        </span>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        updateMarkersData(newMarkers) {
            this.setMarkers(newMarkers);
        }

        focusEntity(id) {
            this.openEntityDrawerById(id);
        }

        focusMarker(id) {
            this.openEntityDrawerById(id);
        }

        destroy() {
            if (this.map) {
                this.map.remove();
                this.map = null;
                this.isReady = false;
            }
        }
    }

    // ─── 7. AUTO INITIALIZER ────────────────────────────────────────────────
    function initAllMaps() {
        const mapElements = document.querySelectorAll('[data-mapping-instance]');
        mapElements.forEach(el => {
            const id = el.getAttribute('data-mapping-instance');
            if (window.LaravelMapping.get(id)) return;

            const rawConfig = el.getAttribute('data-mapping-config');
            let config = { id, containerId: el.id };
            if (rawConfig) {
                try {
                    config = Object.assign(config, JSON.parse(rawConfig));
                } catch (e) {
                    console.error('[LaravelMapping] Gagal parse config JSON:', e);
                }
            }
            new MappingEngine(config);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllMaps);
    } else {
        initAllMaps();
    }

    document.addEventListener('livewire:navigated', initAllMaps);
    document.addEventListener('livewire:load', initAllMaps);
    window.addEventListener('mapping:refresh-all', initAllMaps);

    window.LaravelMappingEngine = MappingEngine;

})(window, document);
