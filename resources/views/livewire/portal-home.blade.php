<x-portal-shell title="Load App" tab="home">
    <div
        class="flex-1 overflow-y-auto"
        x-data="portalHome()"
        x-init="init()"
    >
        {{-- Hero card: dark gradient with NB red glow --}}
        <div class="px-4 pt-4 pb-6">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-900 to-black p-6 shadow-xl">
                {{-- Red glow --}}
                <div class="pointer-events-none absolute -top-20 -right-20 w-48 h-48 rounded-full bg-[#c0392b] blur-[80px] opacity-50"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 w-56 h-56 rounded-full bg-[#8B0000] blur-[90px] opacity-30"></div>

                <div class="relative flex items-center gap-4">
                    <div class="relative shrink-0">
                        <x-nativeblade-image asset="logo_nb.png" class="w-14 h-14 rounded-2xl drop-shadow-[0_0_12px_rgba(231,76,60,0.6)]" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl font-black tracking-tight text-white leading-tight">
                            Native<span class="text-[#e74c3c]">Blade</span>
                        </h1>
                        <p class="text-[11px] font-mono uppercase tracking-widest text-zinc-400 mt-0.5">Portal</p>
                    </div>
                </div>

                <p class="relative text-sm text-zinc-300 mt-5 leading-relaxed">
                    Paste the URL from
                    <code class="font-mono text-xs bg-white/5 text-[#e74c3c] px-1.5 py-0.5 rounded border border-white/5">nativeblade:dev</code>
                    or scan the QR.
                </p>
            </div>
        </div>

        {{-- URL input card --}}
        <div class="px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden">
                <div class="flex items-stretch">
                    <input
                        type="url"
                        x-model="url"
                        @keydown.enter="submit()"
                        placeholder="http://192.168.1.42:1420"
                        autocomplete="off"
                        autocapitalize="off"
                        autocorrect="off"
                        spellcheck="false"
                        inputmode="url"
                        :disabled="connecting"
                        class="flex-1 min-w-0 bg-transparent border-0 px-4 py-4 font-mono text-[15px] text-gray-900 placeholder-gray-400 focus:outline-none disabled:opacity-50"
                    />
                    @if($canScan)
                        <button
                            type="button"
                            @click="openScanner()"
                            :disabled="connecting"
                            aria-label="Scan QR code"
                            class="shrink-0 px-4 flex items-center justify-center text-gray-500 active:text-[#c0392b] border-l border-gray-200 disabled:opacity-30 transition-colors"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                <path d="M14 14h3v3h-3zM20 14h1M14 20h3v1h-3zM20 17v4"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            <p x-show="error" x-cloak class="text-red-500 text-xs mt-2 px-2" x-text="error"></p>
        </div>

        {{-- Connect button --}}
        <div class="px-4 mt-3">
            <button
                type="button"
                @click="submit()"
                :disabled="connecting"
                class="w-full py-4 rounded-2xl font-black text-sm uppercase tracking-wider bg-[#c0392b] text-white shadow-[0_4px_0_rgba(139,0,0,0.7),0_8px_20px_rgba(231,76,60,0.35)] active:translate-y-[2px] active:shadow-[0_2px_0_rgba(139,0,0,0.7)] transition-all flex items-center justify-center gap-2 disabled:opacity-75"
            >
                <span x-show="!connecting" class="flex items-center gap-2">
                    <x-nativeblade-icon name="link" size="18" />
                    Connect
                </span>
                <span x-show="connecting" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.3" stroke-width="3"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    Connecting...
                </span>
            </button>
        </div>

        {{-- Recent --}}
        <template x-if="recents.length > 0">
            <div class="mt-7">
                <div class="px-5 mb-2">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Recent</p>
                </div>
                <div class="px-4 space-y-2">
                    <template x-for="item in recents" :key="item.url">
                        <button
                            type="button"
                            @click="connect(item.url)"
                            class="w-full flex items-center gap-3 bg-white rounded-2xl px-4 py-3 border border-gray-200/60 active:bg-gray-50 transition-colors text-left"
                        >
                            <div class="shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-zinc-800 to-black flex items-center justify-center">
                                <x-nativeblade-icon name="globe" size="16" class="text-[#e74c3c]" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[15px] font-medium text-gray-900 truncate" x-text="item.name || displayHost(item.url)"></div>
                                <div class="font-mono text-[11px] text-gray-500 truncate" x-text="item.url"></div>
                            </div>
                            <x-nativeblade-icon name="caret-right-bold" size="14" class="text-gray-300 shrink-0" />
                        </button>
                    </template>
                </div>
            </div>
        </template>

        {{-- Footer hint --}}
        <p class="text-[11px] text-gray-400 text-center mt-8 mb-6 px-6">
            Press <kbd class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-[10px] text-gray-600">Shift</kbd>
            +
            <kbd class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-[10px] text-gray-600">Esc</kbd>
            to return here anytime.
        </p>

        {{-- HTML5 QR scanner overlay (uses WebView's BarcodeDetector + getUserMedia,
             not the Tauri barcode plugin — that one renders a native fullscreen
             camera with no cancel button on Android). --}}
        <div
            x-show="scanning"
            x-cloak
            @click.self="closeScanner()"
            class="fixed inset-0 z-[60] bg-black flex flex-col"
        >
            <video x-ref="scannerVideo" autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover"></video>

            {{-- Scan frame guide --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-64 h-64 border-2 border-white/80 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
            </div>

            {{-- Top bar with cancel --}}
            <div class="absolute top-0 left-0 right-0 flex items-center justify-between px-4 pt-20 pb-4 bg-gradient-to-b from-black/70 to-transparent">
                <p class="text-white text-sm font-semibold">Scan QR code</p>
                <button
                    type="button"
                    @click="closeScanner()"
                    class="w-10 h-10 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center active:bg-white/30"
                    aria-label="Close scanner"
                >
                    <x-nativeblade-icon name="x" size="20" />
                </button>
            </div>

            <p x-show="scannerError" x-cloak class="absolute bottom-12 left-4 right-4 text-center text-sm text-red-300" x-text="scannerError"></p>
        </div>
    </div>

    {{-- Connecting overlay --}}
    <div
        x-data="{ open: false, target: '' }"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        @nb-portal-connecting.window="open = true; target = $event.detail.url"
        class="fixed inset-0 bg-black/85 backdrop-blur-md flex flex-col items-center justify-center z-50 gap-4 px-6"
    >
        <div class="relative">
            <div class="absolute inset-0 bg-[#c0392b] blur-2xl opacity-50 rounded-full"></div>
            <svg class="relative animate-spin w-12 h-12 text-[#e74c3c]" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"></circle>
                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
            </svg>
        </div>
        <p class="text-[10px] uppercase font-black tracking-widest text-zinc-400">Connecting</p>
        <p class="text-xs font-mono text-zinc-200 max-w-full text-center break-all" x-text="target"></p>
    </div>

    <script>
        if (!window.portalHome) {
            window.portalHome = function () {
                return {
                    url: '',
                    error: '',
                    connecting: false,
                    recents: [],
                    scanning: false,
                    scannerError: '',
                    _scanStream: null,
                    _scanLoop: null,
                    _scanDetector: null,

                    init() {
                        this.refreshRecents();
                    },

                    async openScanner() {
                        if (this.scanning) return;
                        this.scannerError = '';

                        if (!('BarcodeDetector' in window)) {
                            this.scannerError = 'QR scanning not supported on this device.';
                            return;
                        }
                        if (!navigator.mediaDevices?.getUserMedia) {
                            this.scannerError = 'Camera access not supported.';
                            return;
                        }

                        this.scanning = true;
                        await this.$nextTick();

                        try {
                            this._scanDetector = new BarcodeDetector({ formats: ['qr_code'] });
                            this._scanStream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: 'environment' },
                                audio: false,
                            });
                            const video = this.$refs.scannerVideo;
                            video.srcObject = this._scanStream;
                            await video.play().catch(() => {});
                            this._tick();
                        } catch (e) {
                            this.scannerError = 'Camera permission denied or unavailable.';
                            console.warn('[portal scan]', e);
                            setTimeout(() => this.closeScanner(), 1500);
                        }
                    },

                    async _tick() {
                        if (!this.scanning) return;
                        const video = this.$refs.scannerVideo;
                        if (video && video.readyState >= 2) {
                            try {
                                const codes = await this._scanDetector.detect(video);
                                if (codes && codes.length > 0) {
                                    const value = (codes[0].rawValue || '').trim();
                                    if (value) {
                                        this.closeScanner();
                                        this.url = value;
                                        this.submit();
                                        return;
                                    }
                                }
                            } catch (e) {
                                // Detect can throw on transient frames — keep ticking.
                            }
                        }
                        this._scanLoop = requestAnimationFrame(() => this._tick());
                    },

                    closeScanner() {
                        this.scanning = false;
                        if (this._scanLoop) {
                            cancelAnimationFrame(this._scanLoop);
                            this._scanLoop = null;
                        }
                        if (this._scanStream) {
                            this._scanStream.getTracks().forEach(t => t.stop());
                            this._scanStream = null;
                        }
                        this._scanDetector = null;
                    },

                    refreshRecents() {
                        this.recents = window.nbPortal.list().slice(0, 5);
                    },

                    displayHost(url) {
                        try { return new URL(url).host; } catch (e) { return url; }
                    },

                    submit() {
                        if (this.connecting) return;
                        this.error = '';
                        const u = window.nbPortal.normalizeUrl(this.url);
                        if (!u) {
                            this.error = 'Paste the dev server URL (e.g. http://192.168.1.42:1420)';
                            return;
                        }
                        this.connect(u);
                    },

                    connect(url) {
                        if (!url || this.connecting) return;
                        this.connecting = true;
                        window.dispatchEvent(new CustomEvent('nb-portal-connecting', { detail: { url } }));
                        window.nbPortal.connect(url);
                    },
                };
            };
        }
    </script>
</x-portal-shell>
