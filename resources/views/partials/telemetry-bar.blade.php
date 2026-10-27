<div id="spatial-bottom-telemetry-{{ $id }}" class="mapping-bottom-telemetry pointer-events-none">
    <div class="pointer-events-auto mapping-glass-panel telemetry-pill-container" id="telemetry-pill-container-{{ $id }}">
        <!-- 1. Total Entitas / Titik Marker -->
        <div class="telemetry-metric-item theme-amber group cursor-pointer"
             id="telemetry-item-{{ $id }}-total"
             onclick="window.LaravelMapping.get('{{ $id }}')?.toggleTelemetryTooltip('total', event)"
             title="{{ $telemetryTotalLabel ?? 'Total Marker' }}">
            
            <!-- Icon Box (Always visible, prominent on mobile) -->
            <div class="metric-icon-box bg-amber-500/15 border border-amber-500/30 text-amber-400">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <!-- Label & Angka (Hidden di mobile, hanya terlihat di desktop sm:flex) -->
            <div class="telemetry-label-group hidden sm:flex items-center gap-1.5 font-sans">
                <span class="text-xs font-semibold text-slate-300 whitespace-nowrap">{{ $telemetryTotalLabel ?? 'Total' }}</span>
                <span id="telemetry-total-count-{{ $id }}" class="text-sm font-extrabold text-amber-400 font-mono">
                    {{ count($markers ?? []) }}
                </span>
            </div>

            <!-- Tooltip / Mobile Popover Kependudukan Style -->
            <div class="telemetry-tooltip-kependudukan border border-amber-500/40" id="telemetry-tooltip-{{ $id }}-total">
                <div class="tooltip-header border-b border-slate-800 pb-1 mb-1">
                    <div class="flex items-center gap-1.5 font-bold text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_#f59e0b] animate-pulse"></span>
                        <span>{{ $telemetryTotalLabel ?? 'Total Marker' }}</span>
                    </div>
                    <span class="sm:hidden text-xs font-mono font-extrabold text-amber-400" id="telemetry-total-count-mobile-{{ $id }}">
                        {{ count($markers ?? []) }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-300">{{ $telemetryTotalTooltip ?? 'Total keseluruhan data spasial terdaftar' }}</div>
                <div class="tooltip-pointer border-t-amber-500/50"></div>
            </div>
        </div>

        @php
            $defaultMetrics = !empty($telemetryMetrics) ? $telemetryMetrics : [
                ['key' => 'aktif', 'label' => 'Aktif / Online', 'color' => 'emerald', 'tooltip' => 'Entitas beroperasi normal'],
                ['key' => 'siaga', 'label' => 'Siaga / Warning', 'color' => 'amber', 'tooltip' => 'Entitas dalam status waspada'],
                ['key' => 'offline', 'label' => 'Nonaktif / Offline', 'color' => 'rose', 'tooltip' => 'Entitas terputus atau tidak merespons'],
            ];
        @endphp

        @foreach($defaultMetrics as $metric)
            @php
                $mKey = $metric['key'];
                $mLabel = $metric['label'] ?? ucfirst($mKey);
                $mColor = $metric['color'] ?? 'emerald';
                $mTooltip = $metric['tooltip'] ?? $mLabel;
                
                $boxStyle = match($mColor) {
                    'emerald' => 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
                    'amber' => 'bg-amber-500/15 border-amber-500/30 text-amber-400',
                    'rose', 'red' => 'bg-rose-500/15 border-rose-500/30 text-rose-400',
                    'sky', 'blue' => 'bg-sky-500/15 border-sky-500/30 text-sky-400',
                    'purple', 'violet' => 'bg-purple-500/15 border-purple-500/30 text-purple-400',
                    default => 'bg-slate-500/15 border-slate-500/30 text-slate-300',
                };
                $dotClass = match($mColor) {
                    'emerald' => 'bg-emerald-400 shadow-[0_0_8px_#10b981]',
                    'amber' => 'bg-amber-400 shadow-[0_0_8px_#f59e0b]',
                    'rose', 'red' => 'bg-rose-400 shadow-[0_0_8px_#f43f5e]',
                    'sky', 'blue' => 'bg-sky-400 shadow-[0_0_8px_#0ea5e9]',
                    'purple', 'violet' => 'bg-purple-400 shadow-[0_0_8px_#a855f7]',
                    default => 'bg-slate-400 shadow-[0_0_8px_#94a3b8]',
                };
                $textClass = match($mColor) {
                    'emerald' => 'text-emerald-400',
                    'amber' => 'text-amber-400',
                    'rose', 'red' => 'text-rose-400',
                    'sky', 'blue' => 'text-sky-400',
                    'purple', 'violet' => 'text-purple-400',
                    default => 'text-slate-300',
                };
                $borderClass = match($mColor) {
                    'emerald' => 'border-emerald-500/40',
                    'amber' => 'border-amber-500/40',
                    'rose', 'red' => 'border-rose-500/40',
                    'sky', 'blue' => 'border-sky-500/40',
                    'purple', 'violet' => 'border-purple-500/40',
                    default => 'border-slate-500/40',
                };
                $pointerClass = match($mColor) {
                    'emerald' => 'border-t-emerald-500/50',
                    'amber' => 'border-t-amber-500/50',
                    'rose', 'red' => 'border-t-rose-500/50',
                    'sky', 'blue' => 'border-t-sky-500/50',
                    'purple', 'violet' => 'border-t-purple-500/50',
                    default => 'border-t-slate-500/50',
                };
                
                $val = $metric['value'] ?? count(array_filter($markers ?? [], function($m) use ($mKey) {
                    return strtolower($m['status'] ?? 'aktif') === strtolower($mKey);
                }));
                if ($val === 0 && strtolower($mKey) === 'aktif' && empty($metric['value'])) {
                    $val = count($markers ?? []);
                }
            @endphp

            <!-- Separator -->
            <div class="telemetry-separator w-px h-4 md:h-5 bg-slate-700/80 flex-shrink-0"></div>

            <div class="telemetry-metric-item theme-{{ $mColor }} group cursor-pointer"
                 id="telemetry-item-{{ $id }}-{{ $mKey }}"
                 onclick="window.LaravelMapping.get('{{ $id }}')?.toggleTelemetryTooltip('{{ $mKey }}', event)"
                 title="{{ $mTooltip }}">
                
                <!-- Icon Box -->
                <div class="metric-icon-box {{ $boxStyle }} border">
                    @if(isset($metric['icon']))
                        {!! $metric['icon'] !!}
                    @elseif($mKey === 'aktif' || $mKey === 'online')
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }} animate-pulse"></span>
                    @elseif($mKey === 'siaga' || $mKey === 'warning')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @elseif($mKey === 'offline')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.121-7.07" />
                        </svg>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }} animate-pulse"></span>
                    @endif
                </div>

                <!-- Label & Angka (Hidden di mobile, hanya terlihat di desktop sm:flex) -->
                <div class="telemetry-label-group hidden sm:flex items-center gap-1.5 font-sans">
                    <span class="text-xs font-semibold text-slate-300 whitespace-nowrap">{{ $mLabel }}</span>
                    <span id="telemetry-metric-{{ $id }}-{{ $mKey }}" class="text-sm font-extrabold {{ $textClass }} font-mono">
                        {{ $val }}
                    </span>
                </div>

                <!-- Tooltip / Mobile Popover Kependudukan Style -->
                <div class="telemetry-tooltip-kependudukan border {{ $borderClass }}" id="telemetry-tooltip-{{ $id }}-{{ $mKey }}">
                    <div class="tooltip-header border-b border-slate-800 pb-1 mb-1">
                        <div class="flex items-center gap-1.5 font-bold {{ $textClass }}">
                            <span class="w-2 h-2 rounded-full {{ $dotClass }} animate-pulse"></span>
                            <span>{{ $mLabel }}</span>
                        </div>
                        <span class="sm:hidden text-xs font-mono font-extrabold {{ $textClass }}" id="telemetry-metric-mobile-{{ $id }}-{{ $mKey }}">
                            {{ $val }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-300">{{ $mTooltip }}</div>
                    <div class="tooltip-pointer {{ $pointerClass }}"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
