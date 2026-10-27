<div id="spatial-theme-modal-{{ $id }}" class="mapping-theme-modal" style="display: none;"
    onclick="if(event.target === this) window.LaravelMapping.get('{{ $id }}')?.closeThemeModal()">

    <!-- MODAL CARD CONTAINER (100% IDENTIK DENGAN CCTV / PDAM / KEPENDUDUKAN) -->
    <div class="relative w-full max-w-xl sm:max-w-2xl mapping-glass-panel rounded-3xl p-5 sm:p-7 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] flex flex-col gap-4 overflow-hidden my-auto border border-slate-700/60"
        onclick="event.stopPropagation()">

        <!-- AMBIENT GLOW ACCENTS -->
        <div class="absolute -top-12 -left-12 w-52 h-52 bg-theme-ambient rounded-full blur-3xl pointer-events-none opacity-40"></div>
        <div class="absolute -bottom-12 -right-12 w-52 h-52 bg-violet-500/20 rounded-full blur-3xl pointer-events-none opacity-40"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-theme-ambient rounded-full blur-3xl pointer-events-none opacity-20"></div>

        <!-- HEADER -->
        <div class="relative flex items-start justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-3.5">
                <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-theme-subtle via-slate-900/90 to-theme-subtle border border-slate-700/80 p-3 shadow-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-full h-full text-theme-accent filter drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5V5a2 2 0 114 0v7.5A5 5 0 0113 17a4 4 0 01-4 4zm0 0h10a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5" />
                    </svg>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-theme-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-theme-accent border-2 border-slate-950"></span>
                    </span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-white tracking-tight leading-tight">
                            Kustomisasi Tema & Tampilan UI
                        </h2>
                        <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-widest bg-theme-subtle text-theme-accent border border-theme-subtle rounded-full">
                            v2.0 THEME
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold text-slate-300 mt-0.5">Personalisasi Visual Command Center</p>
                    <p class="text-[11px] text-slate-400">Pemerintah Kota Pekanbaru — Riau</p>
                </div>
            </div>

            <!-- Close Button -->
            <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.closeThemeModal()"
                class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800/80 border border-transparent hover:border-slate-700 transition flex-shrink-0 cursor-pointer"
                title="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- DESCRIPTION CARD (LIKE CCTV) -->
        <div class="text-xs sm:text-[13px] text-slate-300 leading-relaxed mapping-glass-panel-light rounded-2xl p-3 sm:p-3.5 border border-theme-subtle">
            Atur palet warna aksen sistem, transparansi kaca (<span class="text-theme-accent font-semibold">opacity</span>), kekuatan <span class="text-theme-accent font-semibold">blur</span>, serta saturasi visual interface. Seluruh panel kaca (<span class="text-theme-accent font-semibold">glass panel</span>) otomatis berganti warna mengikuti tema yang dipilih.
        </div>

        <!-- SECTION 1: PALET WARNA AKSEN (PRESETS + CUSTOM) -->
        <div class="relative mapping-glass-panel rounded-2xl p-3.5 sm:p-4 border border-theme-subtle shadow-lg flex flex-col gap-2.5">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <span class="text-[10px] uppercase font-bold tracking-widest text-slate-300 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-theme-accent animate-pulse"></span>
                    Palet Warna Aksen & Glass Tint
                </span>
                <span id="theme-active-preset-name-{{ $id }}"
                    class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-theme-subtle text-theme-accent border border-theme-subtle">
                    Cyber Sky
                </span>
            </div>

            <!-- 6 Preset Chips Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                <!-- Sky -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('sky')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="sky" title="Cyber Sky">
                    <span class="w-5 h-5 rounded-full bg-sky-500 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Sky</span>
                </button>
                <!-- Emerald -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('emerald')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="emerald" title="Emerald Matrix">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Emerald</span>
                </button>
                <!-- Violet -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('violet')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="violet" title="Electric Violet">
                    <span class="w-5 h-5 rounded-full bg-violet-500 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Violet</span>
                </button>
                <!-- Amber -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('amber')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="amber" title="Sunset Amber">
                    <span class="w-5 h-5 rounded-full bg-amber-500 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Amber</span>
                </button>
                <!-- Rose -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('rose')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="rose" title="Crimson Rose">
                    <span class="w-5 h-5 rounded-full bg-rose-500 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Rose</span>
                </button>
                <!-- Slate -->
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.setThemePreset('slate')"
                    class="theme-preset-btn mapping-glass-panel-light hover:bg-slate-800 rounded-xl p-2 flex flex-col items-center gap-1 text-slate-200 transition group border border-slate-700/60"
                    data-preset="slate" title="Stealth Slate">
                    <span class="w-5 h-5 rounded-full bg-slate-400 shadow-md group-hover:scale-110 transition-transform"></span>
                    <span class="text-[10px] font-semibold">Slate</span>
                </button>
            </div>

            <!-- Custom Color Picker Row -->
            <div class="flex items-center justify-between p-2 rounded-xl mapping-glass-panel-light border border-theme-subtle">
                <span class="text-[11px] text-slate-300 font-medium flex items-center gap-1.5">
                    <span>🎯</span> Warna Bebas & Custom Tint
                </span>
                <div class="flex items-center gap-2">
                    <input id="theme-custom-color-{{ $id }}" type="color" value="#0284c7"
                        oninput="window.LaravelMapping.get('{{ $id }}')?.setCustomThemeColor(this.value)"
                        class="w-7 h-7 rounded-lg cursor-pointer bg-transparent border-0">
                    <span id="theme-color-hex-{{ $id }}" class="font-mono text-xs text-slate-300 font-bold">#0284C7</span>
                </div>
            </div>
        </div>

        <!-- SECTION 2: SLIDERS GLASSMORPHISM (COMPACT GRID CARD) -->
        <div class="mapping-glass-panel-light rounded-2xl p-3.5 sm:p-4 border border-theme-subtle flex flex-col gap-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <span class="text-[10px] uppercase font-bold tracking-widest text-slate-300 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-theme-accent animate-pulse"></span>
                    Parameter Efek Kaca (Glassmorphism)
                </span>
                <span class="text-[9px] text-slate-400 font-mono">Backdrop Filter</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <!-- 1. Opacity Slider -->
                <div class="flex flex-col gap-1 mapping-glass-panel-light p-2.5 rounded-xl border border-theme-subtle">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium text-[11px]">Transparansi Latar</span>
                        <span id="theme-val-opacity-{{ $id }}" class="font-mono text-theme-accent font-bold text-[11px]">82%</span>
                    </div>
                    <input id="theme-slider-opacity-{{ $id }}" type="range" min="30" max="98" value="82"
                        oninput="window.LaravelMapping.get('{{ $id }}')?.setGlassOpacity(this.value)"
                        class="w-full h-1.5 bg-slate-800 rounded-lg cursor-pointer">
                    <div class="flex justify-between text-[9px] text-slate-500 font-mono">
                        <span>Transparan (30%)</span>
                        <span>Solid (98%)</span>
                    </div>
                </div>

                <!-- 2. Blur Slider -->
                <div class="flex flex-col gap-1 mapping-glass-panel-light p-2.5 rounded-xl border border-theme-subtle">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium text-[11px]">Kekuatan Blur</span>
                        <span id="theme-val-blur-{{ $id }}" class="font-mono text-theme-accent font-bold text-[11px]">16px</span>
                    </div>
                    <input id="theme-slider-blur-{{ $id }}" type="range" min="0" max="32" value="16"
                        oninput="window.LaravelMapping.get('{{ $id }}')?.setGlassBlur(this.value)"
                        class="w-full h-1.5 bg-slate-800 rounded-lg cursor-pointer">
                    <div class="flex justify-between text-[9px] text-slate-500 font-mono">
                        <span>Bening (0px)</span>
                        <span>Blur Tebal (32px)</span>
                    </div>
                </div>

                <!-- 3. Saturation Slider -->
                <div class="flex flex-col gap-1 mapping-glass-panel-light p-2.5 rounded-xl border border-theme-subtle">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium text-[11px]">Saturasi Kaca</span>
                        <span id="theme-val-sat-{{ $id }}" class="font-mono text-theme-accent font-bold text-[11px]">180%</span>
                    </div>
                    <input id="theme-slider-sat-{{ $id }}" type="range" min="100" max="240" value="180"
                        oninput="window.LaravelMapping.get('{{ $id }}')?.setGlassSaturation(this.value)"
                        class="w-full h-1.5 bg-slate-800 rounded-lg cursor-pointer">
                    <div class="flex justify-between text-[9px] text-slate-500 font-mono">
                        <span>Alami (100%)</span>
                        <span>Vibrant (240%)</span>
                    </div>
                </div>

                <!-- 4. Border Contrast Slider -->
                <div class="flex flex-col gap-1 mapping-glass-panel-light p-2.5 rounded-xl border border-theme-subtle">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium text-[11px]">Kontras Garis Tepi</span>
                        <span id="theme-val-border-{{ $id }}" class="font-mono text-theme-accent font-bold text-[11px]">45%</span>
                    </div>
                    <input id="theme-slider-border-{{ $id }}" type="range" min="10" max="85" value="45"
                        oninput="window.LaravelMapping.get('{{ $id }}')?.setGlassBorder(this.value)"
                        class="w-full h-1.5 bg-slate-800 rounded-lg cursor-pointer">
                    <div class="flex justify-between text-[9px] text-slate-500 font-mono">
                        <span>Halus (10%)</span>
                        <span>Tegas (85%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: LIVE PREVIEW WIDGET -->
        <div class="p-3 rounded-2xl mapping-glass-panel-light border border-theme-subtle flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-theme-accent shadow-md" id="theme-preview-dot-{{ $id }}"></span>
                <span class="text-xs font-semibold text-white">Live Mockup Preview</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded font-mono bg-emerald-500/20 text-emerald-400">REALTIME</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="px-2.5 py-1 rounded-lg text-[11px] font-medium text-slate-200 mapping-glass-panel border border-theme-subtle shadow-sm flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-theme-accent"></span> Glass Surface
                </div>
                <button type="button" id="theme-preview-btn-{{ $id }}"
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-theme-accent shadow transition">
                    Aksen UI
                </button>
            </div>
        </div>

        <!-- FOOTER ACTIONS & ATTRIBUTION -->
        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 flex-wrap gap-2">
            <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.resetThemeDefaults()"
                class="px-3.5 py-1.5 rounded-xl mapping-glass-panel hover:bg-theme-subtle text-xs font-semibold text-slate-300 border border-theme-subtle transition cursor-pointer">
                Reset ke Standar
            </button>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-500 hidden sm:inline">Tersimpan di Browser LocalStorage</span>
                <button type="button" onclick="window.LaravelMapping.get('{{ $id }}')?.closeThemeModal()"
                    id="theme-save-btn-{{ $id }}"
                    class="px-4 py-2 rounded-xl bg-theme-accent hover:opacity-90 active:scale-95 text-white font-bold text-xs shadow-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terapkan & Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>
