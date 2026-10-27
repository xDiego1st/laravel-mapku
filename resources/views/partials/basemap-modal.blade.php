<div id="mapping-basemap-modal-{{ $id }}" class="mapping-basemap-modal" style="display: none;">
    <div class="mapping-glass-panel" style="max-width: 600px; width: 100%; padding: 24px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #f8fafc;">Pilih Lapisan Peta Dasar (Basemap)</h3>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">7 penyedia ubin peta satelit, jalan, dan elevasi</p>
            </div>
            <button type="button" onclick="document.getElementById('mapping-basemap-modal-{{ $id }}').style.display='none'"
                style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; padding: 4px 8px;">
                &times;
            </button>
        </div>

        <div class="mapping-basemap-grid">
            @foreach($tileLayers as $key => $layer)
                <div class="mapping-basemap-card {{ ($activeTile ?? 'google_satellite') === $key ? 'active' : '' }}"
                    id="tile-card-{{ $id }}-{{ $key }}"
                    onclick="window.LaravelMapping.get('{{ $id }}')?.switchBaseTile('{{ $key }}');
                             document.querySelectorAll('#mapping-basemap-modal-{{ $id }} .mapping-basemap-card').forEach(el => el.classList.remove('active'));
                             this.classList.add('active');
                             document.getElementById('mapping-basemap-modal-{{ $id }}').style.display='none';">
                    <div style="font-size: 20px; margin-bottom: 6px;">
                        @if(str_contains($key, 'sat') || str_contains($key, 'drone')) 🛰️
                        @elseif(str_contains($key, 'dark')) 🌑
                        @elseif(str_contains($key, 'topo')) 🏔️
                        @elseif(str_contains($key, 'hybrid')) 🌐
                        @else 🗺️
                        @endif
                    </div>
                    <div style="font-size: 11px; font-weight: 600; color: #f8fafc;">{{ $layer['name'] ?? ucfirst($key) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
