<div class="mapping-right-gis-controls" id="mapping-right-gis-controls-{{ $id }}">
    @if($enableResetRotation)
        <!-- Reset Rotasi Peta (Kompas Dinamis) -->
        <button type="button"
            id="mapping-reset-rotation-btn-{{ $id }}"
            onclick="window.LaravelMapping.get('{{ $id }}')?.resetRotation()"
            class="gis-btn mapping-glass-panel"
            title="Reset Rotasi Peta (Menghadap Utara Tegak Lurus)">
            <svg id="mapping-compass-needle-{{ $id }}" class="compass-svg" viewBox="0 0 24 24" fill="none">
                <!-- Jarum Merah (Utara / N) -->
                <polygon points="12,2 16,12 12,10" fill="#ef4444" />
                <polygon points="12,2 8,12 12,10" fill="#f87171" />
                <!-- Jarum Putih / Abu (Selatan / S) -->
                <polygon points="12,22 16,12 12,14" fill="#94a3b8" />
                <polygon points="12,22 8,12 12,14" fill="#cbd5e1" />
                <!-- Center Pivot -->
                <circle cx="12" cy="12" r="2" fill="#0f172a" stroke="#ffffff" stroke-width="1" />
            </svg>
            <span class="gis-tooltip">Reset Rotasi (Utara)</span>
        </button>
    @endif

    @if($enableThemeSwitcher)
        <!-- Kustomisasi Tema -->
        <button type="button"
            id="mapping-theme-btn-{{ $id }}"
            onclick="window.LaravelMapping.get('{{ $id }}')?.openThemeModal()"
            class="gis-btn mapping-glass-panel"
            title="Kustomisasi Tema & Tampilan UI">
            <svg class="theme-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5V5a2 2 0 114 0v7.5A5 5 0 0113 17a4 4 0 01-4 4zm0 0h10a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5" />
            </svg>
            <span class="gis-tooltip">Kustomisasi Tema</span>
        </button>
    @endif

    <div class="gis-btn-divider"></div>

    <!-- Zoom In -->
    <button type="button"
        id="mapping-zoom-in-btn-{{ $id }}"
        onclick="window.LaravelMapping.get('{{ $id }}')?.zoomIn()"
        class="gis-btn mapping-glass-panel"
        title="Perbesar Peta">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        <span class="gis-tooltip">Perbesar (+)</span>
    </button>

    <!-- Zoom Out -->
    <button type="button"
        id="mapping-zoom-out-btn-{{ $id }}"
        onclick="window.LaravelMapping.get('{{ $id }}')?.zoomOut()"
        class="gis-btn mapping-glass-panel"
        title="Perkecil Peta">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
        </svg>
        <span class="gis-tooltip">Perkecil (-)</span>
    </button>

    <div class="gis-btn-divider"></div>

    @if($enableResetView)
        <!-- Reset View (Pusatkan Peta) -->
        <button type="button"
            id="mapping-reset-view-btn-{{ $id }}"
            onclick="window.LaravelMapping.get('{{ $id }}')?.resetMapView()"
            class="gis-btn mapping-glass-panel"
            title="Pusatkan Peta ke Posisi Awal">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="gis-tooltip">Pusatkan Kamera</span>
        </button>
    @endif

    @if($enableGps)
        <!-- Lacak Lokasi GPS -->
        <button type="button"
            id="mapping-gps-btn-{{ $id }}"
            onclick="window.LaravelMapping.get('{{ $id }}')?.locateUserGps()"
            class="gis-btn mapping-glass-panel"
            title="Lacak Lokasi GPS Saya">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
            </svg>
            <span class="gis-tooltip">Lacak GPS Saya</span>
        </button>
    @endif
</div>
