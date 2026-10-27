<div id="spatial-entity-drawer-{{ $id }}" class="mapping-entity-drawer" style="transform: translateX(100%);">
    <!-- RESIZER HANDLE UNTUK SIDEBAR DETAIL (LEFT EDGE) -->
    <div id="spatial-drawer-resizer-{{ $id }}" class="mapping-drawer-resizer" title="Klik & geser untuk mengubah lebar sidebar detail">
        <div class="resizer-line"></div>
        <div class="resizer-grip">
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7l-5 5 5 5M16 7l5 5-5 5" />
            </svg>
        </div>
    </div>

    <!-- DRAWER HEADER (100% IDENTIK CCTV / PDAM / KEPENDUDUKAN) -->
    <div class="drawer-header">
        <div class="drawer-header-left">
            <div class="drawer-badges">
                <span id="drawer-status-badge-{{ $id }}" class="drawer-status-badge badge-active">AKTIF</span>
                <span id="drawer-category-badge-{{ $id }}" class="drawer-category-badge">UMUM</span>
            </div>
            <h2 id="drawer-entity-title-{{ $id }}" class="drawer-title">Detail Entitas</h2>
            <div id="drawer-entity-subtitle-{{ $id }}" class="drawer-subtitle">Lokasi & Deskripsi Objek Spasial</div>
        </div>

        <!-- QUICK ACTION BUTTONS -->
        <div class="drawer-actions">
            <!-- Google Maps Route Link -->
            <a id="drawer-gmaps-link-{{ $id }}" href="#" target="_blank" rel="noopener noreferrer"
                class="drawer-icon-btn route" title="Buka rute ke lokasi ini di Google Maps">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
            </a>

            <!-- Copy Coords Button -->
            <button type="button" id="drawer-btn-copy-coords-{{ $id }}"
                onclick="window.LaravelMapping.get('{{ $id }}')?.copyCoordinates()"
                class="drawer-icon-btn copy" title="Salin Koordinat (Lat, Lng)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
            </button>

            <!-- Close Drawer Button -->
            <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.closeEntityDrawer()"
                class="drawer-icon-btn close" title="Tutup Detail">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- DRAWER SCROLLABLE BODY -->
    <div class="drawer-body custom-scrollbar">
        <!-- Media / Visual Documentation Box -->
        <div id="drawer-media-box-{{ $id }}" class="drawer-media-box">
            <div id="drawer-media-placeholder-{{ $id }}" class="media-placeholder">
                <span class="media-icon">📍</span>
                <span class="media-hint">Geospasial Smart GIS</span>
            </div>
            <img id="drawer-entity-image-{{ $id }}" src="" alt="Dokumentasi Visual" class="drawer-image" style="display: none;" />
        </div>

        <!-- Information Cards Grid -->
        <div class="drawer-cards-grid">
            <!-- Koordinat Card -->
            <div class="drawer-card">
                <div class="card-label">KOORDINAT SPASIAL (WGS84)</div>
                <div class="card-value font-mono" id="drawer-coords-val-{{ $id }}">-</div>
            </div>

            <!-- Wilayah / Administrasi Card -->
            <div class="drawer-card">
                <div class="card-label">WILAYAH ADMINISTRATIF</div>
                <div class="card-value" id="drawer-region-val-{{ $id }}">{{ $boundary['name'] ?? 'Kota Pekanbaru' }}</div>
            </div>

            <!-- Kategori Card -->
            <div class="drawer-card">
                <div class="card-label">KATEGORI LAYER</div>
                <div class="card-value" id="drawer-category-val-{{ $id }}">-</div>
            </div>

            <!-- Status Operasional Card -->
            <div class="drawer-card">
                <div class="card-label">STATUS OPERASIONAL</div>
                <div class="card-value" id="drawer-status-val-{{ $id }}">AKTIF</div>
            </div>
        </div>

        <!-- Extra Attributes Container (Dinamis dari properti marker) -->
        <div id="drawer-extra-props-{{ $id }}" class="drawer-extra-props"></div>

        <!-- Custom Action Buttons Container (Call, Details, etc.) -->
        <div id="drawer-custom-actions-{{ $id }}" class="drawer-custom-actions" style="display: none;"></div>
    </div>
</div>
