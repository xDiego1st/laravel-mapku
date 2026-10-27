<div id="mapping-legend-panel-{{ $id }}" class="mapping-legend-panel mapping-glass-panel">
    <!-- Header (Click to collapse/expand) -->
    <div class="legend-header" onclick="this.parentElement.classList.toggle('collapsed')">
        <div class="legend-title-wrap">
            <span class="legend-pulse-dot"></span>
            <span class="legend-title">Legenda Map</span>
        </div>
        <button type="button" class="legend-collapse-btn" title="Lipat / Buka Legenda">
            <svg class="w-3.5 h-3.5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <!-- Collapsible Content -->
    <div class="legend-content">
        @if(!empty($legendItems))
            <div class="legend-items-list">
                @foreach($legendItems as $item)
                    @php
                        $color = $item['color'] ?? 'sky';
                        $label = $item['label'] ?? 'Layer';
                        $shape = $item['shape'] ?? 'circle';
                        $count = $item['count'] ?? 0;
                        $catId = $item['id'] ?? strtolower($label);
                    @endphp
                    <div class="legend-item"
                        data-category="{{ $label }}"
                        onclick="window.LaravelMapping.get('{{ $id }}')?.filterCategory('{{ $label }}')"
                        title="Klik untuk filter: {{ $label }}">
                        <div class="legend-symbol {{ $shape }} color-{{ $color }}"></div>
                        <span class="legend-label">{{ $label }}</span>
                        <span class="legend-badge-count" id="legend-count-{{ $id }}-{{ $catId }}">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="legend-items-list">
                <div class="legend-item">
                    <div class="legend-symbol circle color-sky"></div>
                    <span class="legend-label">Batas Wilayah</span>
                </div>
                <div class="legend-item">
                    <div class="legend-symbol circle color-emerald"></div>
                    <span class="legend-label">Titik Spasial</span>
                </div>
            </div>
        @endif

        <div class="legend-hint">
            <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="legend-hint-text">Klik item legenda untuk memfilter atau klik marker untuk rincian.</span>
        </div>
    </div>
</div>
