<div id="mapping-spatial-inspector-{{ $id }}" class="mapping-spatial-inspector mapping-glass-panel">
    <div class="inspector-top-row">
        <div class="inspector-status">
            <span class="inspector-live-dot"></span>
            <span class="inspector-title">Inspektor Spasial</span>
        </div>
        <span id="mapping-inspector-zoom-{{ $id }}" class="inspector-zoom-tag">
            Zoom: {{ $zoom ?? 12 }}
        </span>
    </div>
    
    <div class="inspector-coords-row">
        <span class="coord-item">Lat: <strong id="mapping-inspector-lat-{{ $id }}">{{ number_format($center['lat'] ?? 0.507068, 6) }}</strong></span>
        <span class="coord-sep">|</span>
        <span class="coord-item">Lng: <strong id="mapping-inspector-lng-{{ $id }}">{{ number_format($center['lng'] ?? 101.447779, 6) }}</strong></span>
    </div>

    <div class="inspector-region-row">
        <svg class="region-pin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        </svg>
        <span id="mapping-inspector-region-{{ $id }}" class="inspector-region-name">
            {{ $boundary['name'] ?? 'Kota Pekanbaru' }}
        </span>
    </div>
</div>
