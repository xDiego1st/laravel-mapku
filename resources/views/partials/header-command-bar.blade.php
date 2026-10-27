<div class="mapping-top-command-bar" id="mapping-top-command-bar-{{ $id }}">
    <!-- SISI KIRI: Brand, Tombol Pencarian & Filter Saja, dan Category Pills -->
    <div class="mapping-command-left">
        @if(!empty($backUrl))
            <!-- Tombol Kembali ke Admin / Dashboard -->
            <a href="{{ $backUrl }}"
                id="mapping-back-btn-{{ $id }}"
                class="mapping-top-btn mapping-glass-panel cursor-pointer text-slate-200 hover:text-white flex items-center gap-1.5 transition active:scale-95 text-decoration-none"
                style="text-decoration: none;"
                title="{{ $backLabel ?? 'Kembali' }}">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline text-[11px] font-semibold">{{ $backLabel ?? 'Kembali' }}</span>
            </a>
        @endif

        @if($enableBrand)
            <!-- Brand Badge & About Modal Trigger -->
            <button type="button"
                id="mapping-brand-btn-{{ $id }}"
                onclick="window.LaravelMapping.get('{{ $id }}')?.openAboutModal()"
                class="mapping-brand-badge cursor-pointer"
                title="Tentang {{ $brandTitle }} — {{ $aboutSubtitle }}">
                <div class="brand-avatar">
                    @if($brandLogo)
                        <img src="{{ $brandLogo }}" alt="{{ $brandTitle }}" class="brand-img" />
                    @else
                        <span class="brand-icon">🗺️</span>
                    @endif
                    <span class="brand-status-dot"></span>
                </div>
                <div class="brand-text-col hidden sm:flex">
                    <span class="brand-name">{{ $brandTitle }}</span>
                    <span class="brand-badge-tag">{{ $brandBadge }}</span>
                </div>
            </button>
        @endif

        @if($enableSearch || $enableFilter)
            <!-- Tombol Pencarian & Filter Saja (Membuka Sidebar Filter Kiri Kompleks) -->
            <button type="button"
                id="mapping-search-filter-btn-{{ $id }}"
                onclick="window.LaravelMapping.get('{{ $id }}')?.toggleFilterSidebar()"
                class="mapping-top-filter-btn mapping-glass-panel cursor-pointer"
                title="Buka Pencarian & Filter Data Spasial">
                <div class="filter-icon-box bg-theme-subtle text-theme-accent">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <span class="filter-btn-text hidden sm:inline">Pencarian & Filter</span>
                <span class="filter-count-badge" id="mapping-active-filter-badge-{{ $id }}" style="display: none;">0</span>
            </button>
        @endif

        @if($enableFilter && !empty($categories))
            <!-- Scrollable Category Filter Pills (Auto Full-Width Expanding to Right Controls) -->
            <div class="mapping-category-pills-wrap">
                <button type="button" class="pills-scroll-btn left"
                    id="mapping-pills-scroll-left-{{ $id }}"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.scrollCategoryFilters('left')"
                    title="Geser filter ke kiri">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <div class="mapping-category-pills" id="mapping-category-pills-{{ $id }}">
                    <button type="button" class="mapping-pill active" data-category="ALL"
                        id="mapping-pill-{{ $id }}-all"
                        onclick="window.LaravelMapping.get('{{ $id }}')?.filterCategory('ALL')"
                        title="Tampilkan Semua Titik">
                        <span class="pill-dot bg-theme-accent"></span>
                        <span class="pill-label hidden sm:inline">SEMUA</span>
                        <span class="pill-count" id="mapping-pill-count-{{ $id }}-all">0</span>
                    </button>
                    @foreach($categories as $cat)
                        @if(strtoupper($cat) !== 'ALL')
                            @php
                                $catLower = strtolower($cat);
                                $dotStyle = match(true) {
                                    str_contains($catLower, 'cctv') => 'background: #38bdf8; box-shadow: 0 0 6px #0284c7;',
                                    str_contains($catLower, 'pdam') => 'background: #34d399; box-shadow: 0 0 6px #10b981;',
                                    str_contains($catLower, 'kependudukan') => 'background: #c084fc; box-shadow: 0 0 6px #a855f7;',
                                    str_contains($catLower, 'pemda') => 'background: #fbbf24; box-shadow: 0 0 6px #d97706;',
                                    default => 'background: #94a3b8;',
                                };
                            @endphp
                            <button type="button" class="mapping-pill" data-category="{{ $cat }}"
                                id="mapping-pill-{{ $id }}-{{ strtolower($cat) }}"
                                onclick="window.LaravelMapping.get('{{ $id }}')?.filterCategory('{{ $cat }}')"
                                title="Filter Kategori {{ strtoupper($cat) }}">
                                <span class="pill-dot" style="{{ $dotStyle }}"></span>
                                <span class="pill-label hidden sm:inline">{{ strtoupper($cat) }}</span>
                                <span class="pill-count" id="mapping-pill-count-{{ $id }}-{{ strtolower($cat) }}">0</span>
                            </button>
                        @endif
                    @endforeach
                </div>

                <button type="button" class="pills-scroll-btn right"
                    id="mapping-pills-scroll-right-{{ $id }}"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.scrollCategoryFilters('right')"
                    title="Geser filter ke kanan">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- SISI KANAN ATAS: Pemilih Lapisan Peta (Basemap Dropdown 100% Identik CCTV/PDAM) & Layar Penuh -->
    <div class="mapping-command-right">
        <!-- Basemap & Boundary Dropdown Menu -->
        <div class="mapping-basemap-dropdown-wrapper" id="spatial-basemap-dropdown-wrapper-{{ $id }}">
            <button type="button"
                id="spatial-basemap-dropdown-btn-{{ $id }}"
                onclick="window.LaravelMapping.get('{{ $id }}')?.toggleBasemapDropdown(event)"
                class="mapping-top-btn mapping-glass-panel cursor-pointer"
                title="Pilih Lapisan Peta & Batas Wilayah">
                <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-theme-accent flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span class="hidden sm:inline text-[11px] font-semibold"
                    id="spatial-current-basemap-name-{{ $id }}">
                    {{ $tileLayers[$activeTile ?? 'google_satellite']['name'] ?? 'Peta' }}
                </span>
                <svg class="w-3 h-3 text-slate-400 chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Panel (100% Identik CCTV, PDAM, Kependudukan) -->
            <div id="spatial-basemap-dropdown-{{ $id }}"
                class="mapping-basemap-dropdown-panel mapping-glass-panel"
                style="display: none;">
                <div class="dropdown-header-title">
                    <span>Lapisan Peta Dasar</span>
                </div>
                <div class="dropdown-basemap-list">
                    @foreach ($tileLayers as $key => $tile)
                        <button type="button"
                            onclick="window.LaravelMapping.get('{{ $id }}')?.switchBaseTile('{{ $key }}'); window.LaravelMapping.get('{{ $id }}')?.closeBasemapDropdown();"
                            class="spatial-basemap-item {{ ($activeTile ?? 'google_satellite') === $key ? 'is-active' : '' }}"
                            data-layer="{{ $key }}">
                            <span class="item-name">{{ $tile['name'] ?? ucfirst($key) }}</span>
                            <span class="spatial-basemap-indicator bg-theme-accent {{ ($activeTile ?? 'google_satellite') === $key ? '' : 'hidden' }}"></span>
                        </button>
                    @endforeach
                </div>

                <div class="dropdown-header-title divider-top">
                    <span>Batas Administrasi</span>
                </div>
                <div class="dropdown-boundary-options">
                    <!-- 1. Topeng Luar Kota -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan topeng bayangan di luar batas kota">
                        <span class="toggle-label">
                            <span class="color-dot bg-slate-500"></span>
                            <span>Topeng Luar Kota</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-mask-{{ $id }}" checked
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleCityMask(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 2. Warna Wilayah Kecamatan -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan warna poligon berbeda untuk tiap wilayah kecamatan">
                        <span class="toggle-label">
                            <span class="color-dot color-dot-gradient-districts"></span>
                            <span>Warna Wilayah Kecamatan</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-districts-fill-{{ $id }}"
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleDistrictsFill(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 3. Garis Batas Kecamatan -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan garis kontur batas wilayah kecamatan">
                        <span class="toggle-label">
                            <span class="color-dot bg-amber-500"></span>
                            <span>Garis Batas Kecamatan</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-districts-{{ $id }}" checked
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleDistrictsLayer(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 4. Warna Wilayah Kelurahan -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan warna poligon berbeda untuk tiap wilayah kelurahan">
                        <span class="toggle-label">
                            <span class="color-dot color-dot-gradient-subdistricts"></span>
                            <span>Warna Wilayah Kelurahan</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-subdistricts-fill-{{ $id }}"
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleSubdistrictsFill(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 5. Garis Batas Kelurahan -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan garis kontur batas wilayah kelurahan">
                        <span class="toggle-label">
                            <span class="color-dot bg-purple-500"></span>
                            <span>Garis Batas Kelurahan</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-subdistricts-{{ $id }}"
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleSubdistrictsLayer(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 6. Nama Wilayah di Peta -->
                    <label class="boundary-toggle-item cursor-pointer" title="Tampilkan teks nama kecamatan/kelurahan di tengah wilayah peta">
                        <span class="toggle-label">
                            <svg class="w-3.5 h-3.5 text-sky-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <span>Nama Wilayah di Peta</span>
                        </span>
                        <input type="checkbox" id="spatial-toggle-boundary-labels-{{ $id }}" checked
                            onchange="window.LaravelMapping.get('{{ $id }}')?.toggleBoundaryLabels(this.checked)"
                            class="mapping-checkbox">
                    </label>

                    <!-- 7. Slider Transparansi Warna -->
                    <div class="boundary-slider-wrap">
                        <div class="slider-header">
                            <span>Transparansi Warna</span>
                            <span id="spatial-boundary-fill-opacity-val-{{ $id }}" class="text-theme-accent font-bold font-mono">25%</span>
                        </div>
                        <input type="range" id="spatial-slider-boundary-opacity-{{ $id }}" min="10" max="70" value="25"
                            oninput="window.LaravelMapping.get('{{ $id }}')?.setBoundaryFillOpacity(this.value / 100)"
                            class="mapping-range-slider w-full">
                    </div>
                </div>
            </div>
        </div>

        @if($enableFullscreen)
            <!-- Tombol Layar Penuh (100% Identik CCTV/PDAM/Kependudukan) -->
            <button type="button"
                id="spatial-fullscreen-btn-{{ $id }}"
                onclick="window.LaravelMapping.get('{{ $id }}')?.toggleFullscreen()"
                class="mapping-top-btn mapping-glass-panel cursor-pointer"
                title="Mode Layar Penuh">
                <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-theme-accent flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span class="hidden md:inline text-[11px] font-semibold">Layar Penuh</span>
            </button>
        @endif
    </div>
</div>
