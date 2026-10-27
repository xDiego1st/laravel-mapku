<div id="mapping-filter-sidebar-{{ $id }}" class="mapping-filter-sidebar mapping-filter-sidebar-left" style="display: none;">
    <!-- Backdrop Overlay for Mobile / Closing on Click Outside -->
    <div class="sidebar-backdrop" onclick="window.LaravelMapping.get('{{ $id }}')?.toggleFilterSidebar()"></div>

    <!-- Left Drawer Panel with Draggable Resizer Handle on Right Edge -->
    <div class="sidebar-panel mapping-glass-panel" id="mapping-filter-panel-{{ $id }}">
        <!-- Draggable Resizer Handle on Right Edge of Left Drawer Panel -->
        <div id="mapping-filter-resizer-{{ $id }}"
             class="mapping-filter-resizer-handle"
             title="Klik & geser untuk mengubah lebar sidebar filter">
            <div class="resizer-line"></div>
            <div class="resizer-grip">
                <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7l-5 5 5 5M16 7l5 5-5 5" />
                </svg>
            </div>
        </div>

        <!-- AREA 1: HEADER SIDEBAR (STICKY TOP) -->
        <div class="filter-sidebar-header">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-slate-800/90 border border-theme-subtle p-1 flex items-center justify-center flex-shrink-0 shadow-lg">
                        <span class="text-base">🔍</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h4 class="text-xs md:text-sm font-bold tracking-tight text-white uppercase truncate">
                            Pencarian & Filter
                        </h4>
                        <p class="text-[10px] text-slate-400 font-medium truncate">
                            {{ $brandTitle }} • Multi-Kriteria
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-[9px] font-bold text-emerald-400 tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        LIVE
                    </span>

                    <button type="button"
                        onclick="window.LaravelMapping.get('{{ $id }}')?.toggleFilterSidebar()"
                        class="flex items-center gap-1 px-2 py-1 text-slate-300 hover:text-white rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 transition active:scale-95 text-xs font-semibold cursor-pointer"
                        title="Tutup Filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-[11px]">Tutup</span>
                    </button>
                </div>
            </div>

            <!-- Sub-Header: Role & Quick Reset -->
            <div class="flex items-center justify-between text-[10px] px-2.5 py-1.5 rounded-lg bg-slate-950/60 border border-slate-800/80 mt-1">
                <div class="flex items-center gap-1.5 text-slate-400 truncate">
                    <span class="font-bold text-theme-accent font-mono">GIS ENGINE</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-300 truncate">Filter Terpadu</span>
                </div>
                <button type="button"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.resetComplexFilters()"
                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/25 border border-rose-500/30 hover:border-rose-500/60 rounded-md transition active:scale-95 cursor-pointer shadow-sm"
                    title="Reset Seluruh Filter ke Default">
                    <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        <!-- SCROLLABLE BODY (COMPLEX MULTI-CRITERIA FILTER SYSTEM) -->
        <div class="filter-sidebar-scrollable-body">
            <!-- AREA 2: OMNI SEARCH (PENCARIAN SPESIFIK BERDASARKAN KATEGORI) -->
            <div class="filter-section">
                <div class="flex items-center justify-between text-[10px] mb-1.5">
                    <span class="font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari Berdasarkan:
                    </span>
                    <span class="text-[9px] font-mono text-slate-400 bg-slate-800/80 px-1.5 py-0.5 rounded border border-slate-700">⌘K</span>
                </div>

                <!-- Category Search Pills -->
                <div class="omni-search-pills" id="omni-search-pills-{{ $id }}">
                    @foreach($searchCategories as $sc)
                        <button type="button"
                            class="omni-pill {{ ($sc['key'] ?? '') === 'ALL' ? 'is-active' : '' }}"
                            data-key="{{ $sc['key'] ?? 'ALL' }}"
                            data-placeholder="{{ $sc['placeholder'] ?? 'Ketik kata kunci pencarian...' }}"
                            onclick="window.LaravelMapping.get('{{ $id }}')?.setSearchType('{{ $sc['key'] ?? 'ALL' }}', this)">
                            <span>{{ $sc['icon'] ?? '•' }}</span>
                            <span>{{ $sc['label'] ?? $sc['key'] }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Omni Search Input Box -->
                <div class="omni-search-input-wrap">
                    <input type="text"
                        id="mapping-complex-search-input-{{ $id }}"
                        placeholder="Ketik kata kunci pencarian..."
                        oninput="window.LaravelMapping.get('{{ $id }}')?.onComplexSearchInput(this.value)"
                        class="omni-input"
                        autocomplete="off" />
                    <button type="button"
                        onclick="const input = document.getElementById('mapping-complex-search-input-{{ $id }}'); if(input) { input.value = ''; window.LaravelMapping.get('{{ $id }}')?.onComplexSearchInput(''); }"
                        class="omni-clear-btn"
                        title="Bersihkan Pencarian">
                        &times;
                    </button>
                </div>
            </div>

            <!-- AREA 2.5: DAFTAR ENTITAS / ARMADA AKTIF (CLICK TO FOCUS) -->
            <div class="filter-section">
                <div class="flex items-center justify-between text-[11px] mb-2 font-bold text-slate-300 uppercase tracking-wider">
                    <span class="flex items-center gap-1.5">
                        <span>🚛</span>
                        <span>Daftar Armada / Titik</span>
                    </span>
                    <span id="entity-list-badge-count-{{ $id }}" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">0</span>
                </div>
                <div id="entity-list-items-{{ $id }}" class="entity-list-container custom-scrollbar max-h-56 overflow-y-auto">
                    <!-- Populated dynamically by MappingEngine.renderEntityList() -->
                </div>
            </div>

            <!-- AREA 3: KATEGORI LAYER / ENTITAS -->
            @if(!empty($categories))
                <div class="filter-section">
                    <div class="flex items-center justify-between mb-2">
                        <label class="filter-section-title">Kategori Layer / Entitas</label>
                        <div class="flex items-center gap-1.5 text-[10px]">
                            <button type="button"
                                onclick="window.LaravelMapping.get('{{ $id }}')?.selectAllCategories(true)"
                                class="text-sky-400 hover:text-sky-300 font-semibold cursor-pointer">
                                Pilih Semua
                            </button>
                            <span class="text-slate-600">•</span>
                            <button type="button"
                                onclick="window.LaravelMapping.get('{{ $id }}')?.selectAllCategories(false)"
                                class="text-slate-400 hover:text-slate-300 font-semibold cursor-pointer">
                                Bersihkan
                            </button>
                        </div>
                    </div>

                    <div class="filter-categories-grid">
                        @foreach($categories as $cat)
                            @if(strtoupper($cat) !== 'ALL')
                                <label class="filter-cat-card cursor-pointer">
                                    <input type="checkbox"
                                        name="complex_filter_cat_{{ $id }}[]"
                                        value="{{ $cat }}"
                                        checked
                                        onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                        class="mapping-checkbox" />
                                    <span class="cat-card-indicator"></span>
                                    <span class="cat-card-name">{{ strtoupper($cat) }}</span>
                                    <span class="cat-card-count" id="complex-cat-count-{{ $id }}-{{ strtolower($cat) }}">0</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- AREA 4: STATUS OPERASIONAL (MULTI-SELECT) -->
            <div class="filter-section">
                <label class="filter-section-title">Status Operasional Entitas</label>
                <div class="filter-status-list">
                    <label class="filter-status-item cursor-pointer">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                name="complex_filter_status_{{ $id }}[]"
                                value="aktif"
                                checked
                                onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                class="mapping-checkbox text-emerald-500" />
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                            <span class="text-xs font-semibold text-slate-200">Online / Aktif</span>
                        </div>
                        <span class="status-count-badge text-emerald-400" id="filter-status-aktif-{{ $id }}">0</span>
                    </label>

                    <label class="filter-status-item cursor-pointer">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                name="complex_filter_status_{{ $id }}[]"
                                value="siaga"
                                checked
                                onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                class="mapping-checkbox text-amber-500" />
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_#f59e0b]"></span>
                            <span class="text-xs font-semibold text-slate-200">Siaga / Warning</span>
                        </div>
                        <span class="status-count-badge text-amber-400" id="filter-status-siaga-{{ $id }}">0</span>
                    </label>

                    <label class="filter-status-item cursor-pointer">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                name="complex_filter_status_{{ $id }}[]"
                                value="offline"
                                checked
                                onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                class="mapping-checkbox text-rose-500" />
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_#ef4444]"></span>
                            <span class="text-xs font-semibold text-slate-200">Offline / Putus</span>
                        </div>
                        <span class="status-count-badge text-rose-400" id="filter-status-offline-{{ $id }}">0</span>
                    </label>
                </div>
            </div>

            <!-- AREA 5: WILAYAH ADMINISTRATIF (KECAMATAN / DISTRIK) -->
            @if(!empty($districts))
                <div class="filter-section">
                    <label class="filter-section-title">Wilayah Administratif</label>
                    <div class="filter-select-wrap">
                        <select id="complex-filter-district-{{ $id }}"
                            onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                            class="filter-select">
                            <option value="ALL">Semua Wilayah Administratif</option>
                            @foreach($districts as $district)
                                <option value="{{ $district }}">{{ $district }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <!-- AREA 6: CUSTOMIZABLE FILTER FIELDS & SLOTS (KONSUMEN DAPAT MEMPERLUAS) -->
            @if(!empty($filterFields))
                <div class="filter-section custom-fields-section">
                    <label class="filter-section-title">Kriteria Tambahan Kustom</label>
                    <div class="flex flex-col gap-2.5">
                        @foreach($filterFields as $fKey => $fConfig)
                            <div class="custom-filter-field">
                                <label class="text-[11px] font-medium text-slate-300 block mb-1">
                                    {{ $fConfig['label'] ?? ucfirst($fKey) }}
                                </label>
                                @if(($fConfig['type'] ?? 'select') === 'select')
                                    <select name="custom_filter_{{ $id }}_{{ $fKey }}"
                                        onchange="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                        class="filter-select">
                                        <option value="ALL">Semua {{ $fConfig['label'] ?? '' }}</option>
                                        @foreach(($fConfig['options'] ?? []) as $optKey => $optVal)
                                            <option value="{{ $optKey }}">{{ $optVal }}</option>
                                        @endforeach
                                    </select>
                                @elseif(($fConfig['type'] ?? '') === 'range')
                                    <div class="flex items-center gap-2">
                                        <input type="range"
                                            name="custom_filter_{{ $id }}_{{ $fKey }}"
                                            min="{{ $fConfig['min'] ?? 0 }}"
                                            max="{{ $fConfig['max'] ?? 100 }}"
                                            value="{{ $fConfig['default'] ?? $fConfig['max'] ?? 100 }}"
                                            oninput="window.LaravelMapping.get('{{ $id }}')?.onFilterFieldChange()"
                                            class="w-full">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Slot untuk View Kustom Konsumen -->
            @if(!empty($customFilterView))
                <div class="filter-section custom-view-wrapper">
                    @include($customFilterView)
                </div>
            @endif

            {{ $custom_filters ?? '' }}
        </div>

        <!-- AREA 7: STICKY FOOTER ACTIONS (APPLY & SUMMARY) -->
        <div class="filter-sidebar-footer">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] text-slate-400">Total Temuan:</span>
                <span class="text-xs font-mono font-bold text-white flex items-center gap-1">
                    <span id="mapping-filter-found-count-{{ $id }}" class="text-theme-accent">0</span>
                    <span class="text-slate-400">titik</span>
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.resetComplexFilters()"
                    class="filter-footer-btn secondary cursor-pointer">
                    Reset
                </button>
                <button type="button"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.applyComplexFilter(); window.LaravelMapping.get('{{ $id }}')?.toggleFilterSidebar();"
                    class="filter-footer-btn primary cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>
