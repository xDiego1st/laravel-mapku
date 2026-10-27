<div id="mapping-about-modal-{{ $id }}" class="mapping-basemap-modal" style="display: none;">
    <div class="mapping-glass-panel" style="max-width: 500px; width: 100%; padding: 24px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <div>
                <span class="mapping-badge" style="margin-bottom: 6px;">{{ $aboutVersion ?? 'v1.0 GIS' }}</span>
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #f8fafc;">{{ $aboutTitle ?? 'Smart Spatial GIS' }}</h3>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">{{ $aboutSubtitle ?? 'Command Center Geospasial' }}</p>
            </div>
            <button type="button" onclick="document.getElementById('mapping-about-modal-{{ $id }}').style.display='none'"
                style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">
                &times;
            </button>
        </div>

        <p style="font-size: 13px; line-height: 1.6; color: #cbd5e1; margin-bottom: 20px;">
            {{ $aboutDescription ?? 'Platform pemetaan spasial modular berbasis Geographic Information System (GIS).' }}
        </p>

        @if(!empty($highlightCapabilities))
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                @foreach($highlightCapabilities as $cap)
                    <div style="background: rgba(30, 41, 59, 0.5); padding: 10px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.05);">
                        <div style="font-size: 16px; margin-bottom: 4px;">{{ $cap['icon'] ?? '✨' }}</div>
                        <div style="font-size: 11px; font-weight: 700; color: #f8fafc;">{{ $cap['title'] ?? '' }}</div>
                        <div style="font-size: 10px; color: #94a3b8;">{{ $cap['desc'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 14px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11px; color: #fff;">
                    {{ $authorInitials ?? 'GIS' }}
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 600; color: #f8fafc;">{{ $authorName ?? 'GIS Team' }}</div>
                    <div style="font-size: 10px; color: #94a3b8;">{{ $authorRole ?? 'GIS Maintainer' }}</div>
                </div>
            </div>
            <span style="font-size: 10px; color: #64748b;">Powered by MapLibre</span>
        </div>
    </div>
</div>
