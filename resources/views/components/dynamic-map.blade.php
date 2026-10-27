<div class="relative w-screen h-screen overflow-hidden bg-slate-950 font-sans select-none mapping-wrapper" id="spatial-map-wrapper-{{ $mapInstanceId }}">

    <!-- 1. MAP CANVAS CONTAINER -->
    <div id="spatial-map-canvas-{{ $mapInstanceId }}" wire:ignore wire:ignore.self class="absolute inset-0 w-full h-full z-0 bg-slate-950"
        style="width:100%;height:100%;top:0;left:0;position:absolute;">
    </div>

    <!-- 2. TOP FLOATING BAR -->
    <div class="mapping-top-bar" id="spatial-top-header-{{ $mapInstanceId }}">
        <!-- Brand & Title -->
        <div class="flex items-center gap-3">
            <div class="mapping-brand-badge cursor-pointer" onclick="document.getElementById('mapping-about-modal-{{ $mapInstanceId }}').style.display='flex'">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="Logo" class="w-5 h-5 object-contain" />
                @else
                    <span class="text-sm">🗺️</span>
                @endif
                <span class="font-bold">{{ $brandTitle }}</span>
                <span class="badge-tag">{{ $brandBadge }}</span>
            </div>
        </div>

        <!-- Search Bar or Custom Search Slot -->
        @if($searchSlotView)
            @include($searchSlotView)
        @elseif($enableSearch)
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full mapping-glass-panel border border-sky-500/30 w-72">
                <span class="text-slate-400 text-xs">🔍</span>
                <input type="text"
                    placeholder="{{ $searchPlaceholder }}"
                    wire:model.live.debounce.300ms="searchQuery"
                    class="bg-transparent border-none text-xs text-white placeholder-slate-400 focus:outline-none w-full" />
            </div>
        @endif

        <!-- Controls / Custom Header Slot -->
        <div class="flex items-center gap-2">
            @if($headerControlsView)
                @include($headerControlsView)
            @endif

            <!-- Basemap Switcher Button -->
            <button type="button"
                onclick="document.getElementById('mapping-basemap-modal-{{ $mapInstanceId }}').style.display='flex'"
                class="px-3 py-1.5 rounded-xl mapping-glass-panel border border-sky-500/30 text-xs font-semibold text-slate-200 hover:text-white flex items-center gap-1.5 transition active:scale-95 cursor-pointer"
                title="Ganti Lapisan Basemap">
                <span>🛰️</span>
                <span class="hidden sm:inline">Basemap</span>
            </button>

            <!-- Fullscreen Toggle Button -->
            <button type="button"
                onclick="const el = document.getElementById('spatial-map-wrapper-{{ $mapInstanceId }}');
                         if (!document.fullscreenElement) { el.requestFullscreen(); } else { document.exitFullscreen(); }"
                class="p-2 rounded-xl mapping-glass-panel border border-sky-500/30 text-xs font-semibold text-slate-200 hover:text-white flex items-center justify-center transition active:scale-95 cursor-pointer"
                title="Mode Layar Penuh">
                ⛶
            </button>
        </div>
    </div>

    <!-- 3. BOTTOM TELEMETRY BAR -->
    @include('mapping::partials.telemetry-bar', [
        'id' => $mapInstanceId,
        'items' => $items,
        'telemetryTotalLabel' => $telemetryTotalLabel,
        'telemetryMetrics' => $telemetryMetrics,
    ])

    <!-- 4. CUSTOM PARTIAL INJECTIONS (Slots) -->
    @if($legendExtraView)
        @include($legendExtraView)
    @endif

    @if($bottomToolsView)
        @include($bottomToolsView)
    @endif

    @if($customDrawerView)
        @include($customDrawerView)
    @endif

    <!-- 5. MODALS -->
    @include('mapping::partials.basemap-modal', [
        'id' => $mapInstanceId,
        'tileLayers' => $tileLayers,
        'activeTile' => 'google_satellite'
    ])

    @include('mapping::partials.about-modal', [
        'id' => $mapInstanceId,
        'aboutVersion' => $aboutVersion,
        'aboutTitle' => $aboutTitle,
        'aboutSubtitle' => $aboutSubtitle,
        'aboutDescription' => $aboutDescription,
        'highlightCapabilities' => $highlightCapabilities,
        'authorInitials' => $authorInitials,
        'authorName' => $authorName,
        'authorRole' => $authorRole,
    ])

    <!-- 6. INITIALIZATION SCRIPT -->
    @push('scripts')
    <script>
        (function() {
            function bootInstance() {
                if (!window.LaravelMappingEngine) {
                    setTimeout(bootInstance, 100);
                    return;
                }

                if (window.LaravelMapping.get('{{ $mapInstanceId }}')) return;

                const engineConfig = {
                    id: '{{ $mapInstanceId }}',
                    containerId: 'spatial-map-canvas-{{ $mapInstanceId }}',
                    center: {!! json_encode($mapConfig['center'] ?? [101.447779, 0.507068]) !!},
                    zoom: {{ $mapConfig['default_zoom'] ?? 12 }},
                    minZoom: {{ $mapConfig['min_zoom'] ?? 8 }},
                    maxZoom: {{ $mapConfig['max_zoom'] ?? 22 }},
                    boundary: {!! $boundaryConfigJson !!},
                    tileLayers: {!! $tileLayersJson !!},
                    markers: {!! $initialItemsJson !!},
                    activeTile: 'google_satellite'
                };

                const instance = new window.LaravelMappingEngine(engineConfig);
                window.{{ $jsNamespace }}Instance = instance;
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootInstance);
            } else {
                bootInstance();
            }
        })();
    </script>
    @endpush
</div>
