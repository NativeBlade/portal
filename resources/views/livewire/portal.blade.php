<div
    class="min-h-screen w-full bg-[#0a0a0a] text-white flex flex-col items-center justify-center px-6 relative overflow-hidden"
    x-data="nbConnect()"
    x-init="init()"
>

    {{-- Red glow background --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[300px] h-[300px] bg-[#8B0000] rounded-full blur-[120px] opacity-30 pointer-events-none"></div>

    <div class="w-full max-w-sm flex flex-col items-center z-10">

        {{-- Logo --}}
        <div nb-animation="fadeInUp" class="flex flex-col items-center mb-8">
            <div class="relative mb-6">
                <x-nativeblade-image asset="logo_nb.png" class="w-20 h-20 rounded-2xl drop-shadow-[0_0_15px_rgba(231,76,60,0.8)]" />
                <div class="absolute inset-0 bg-[#e74c3c] blur-2xl opacity-40 rounded-full"></div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter mb-2 text-center uppercase">
                Native<span class="text-[#c0392b]">Blade</span>
            </h1>
            <p class="text-[#9ca3af] text-center text-xs px-4">
                Paste the URL from <code class="text-[#c0392b] font-mono">nativeblade:dev</code>
            </p>
        </div>

        {{-- URL input --}}
        <div nb-animation="fadeInUp" nb-animation-delay="150ms" class="w-full mb-3">
            <div class="flex items-stretch w-full bg-[#111111] border border-[#2a2a2a] rounded-xl focus-within:border-[#c0392b] focus-within:ring-1 focus-within:ring-[#c0392b] transition-colors overflow-hidden">
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
                    class="flex-1 min-w-0 bg-transparent border-0 px-4 py-4 font-mono text-sm text-white placeholder-[#4b5563] focus:outline-none disabled:opacity-50"
                />
                @if($canScan)
                <button
                    type="button"
                    wire:click="scanQr"
                    :disabled="connecting"
                    nb-feedback
                    aria-label="Scan QR code"
                    title="Scan QR code"
                    class="shrink-0 px-4 flex items-center justify-center text-[#9ca3af] hover:text-white border-l border-[#2a2a2a] disabled:opacity-50 transition-colors"
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
            <p x-show="error" x-cloak class="text-[#ef4444] text-xs mt-2 px-1" x-text="error"></p>
        </div>

        {{-- Connect button --}}
        <div nb-animation="fadeInUp" nb-animation-delay="200ms" class="w-full mb-6">
            <button
                type="button"
                @click="submit()"
                :disabled="connecting"
                nb-feedback
                class="w-full py-4 rounded-xl font-black text-sm uppercase tracking-wider bg-[#c0392b] border-2 border-[#e74c3c] text-white shadow-[0_4px_0_rgba(139,0,0,1),0_0_20px_rgba(231,76,60,0.5)] active:translate-y-[2px] active:shadow-[0_2px_0_rgba(139,0,0,1)] transition-all flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-wait">

                <span x-show="!connecting" class="flex items-center gap-2">
                    <x-nativeblade-icon name="link" size="18" />
                    Connect
                </span>

                <span x-show="connecting" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    Connecting...
                </span>
            </button>
        </div>

        {{-- Recent --}}
        <div x-show="recents.length > 0 && !connecting" x-cloak nb-animation="fadeInUp" nb-animation-delay="300ms" class="w-full">
            <p class="text-[10px] uppercase font-black text-[#6b7280] tracking-widest mb-2 px-1">Recent</p>
            <div class="space-y-2">
                <template x-for="(item, i) in recents" :key="item">
                    <div class="flex items-center gap-2 bg-[#111111] border border-[#2a2a2a] rounded-xl overflow-hidden">
                        <button
                            type="button"
                            @click="connect(item)"
                            class="flex-1 text-left px-4 py-3 font-mono text-xs text-[#9ca3af] hover:text-white truncate"
                            x-text="item"
                        ></button>
                        <button
                            type="button"
                            @click="forget(i)"
                            class="px-3 py-3 text-[#6b7280] hover:text-[#ef4444] transition-colors"
                            aria-label="Remove"
                        >
                            <x-nativeblade-icon name="x" size="14" />
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Footer hint --}}
        <p class="text-[10px] text-[#4b5563] text-center mt-10 max-w-xs">
            Press Shift + Esc anytime to return to this screen.
        </p>
    </div>

    {{-- Full-screen connecting overlay --}}
    <div
        x-show="connecting"
        x-cloak
        x-transition.opacity.duration.150ms
        class="fixed inset-0 bg-black/90 backdrop-blur-sm flex flex-col items-center justify-center z-50 gap-4"
    >
        <svg class="animate-spin w-10 h-10 text-[#c0392b]" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"></circle>
            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
        </svg>
        <p class="text-[10px] uppercase font-black tracking-widest text-[#6b7280]">Connecting</p>
        <p class="text-xs font-mono text-[#9ca3af] max-w-[80%] text-center break-all" x-text="target"></p>
    </div>

    <script>
        if (!window.nbConnect) {
            window.nbConnect = function () {
                return {
                    url: '',
                    error: '',
                    target: '',
                    connecting: false,
                    recents: [],

                    init() {
                        try {
                            const raw = localStorage.getItem('nb:recentBundles');
                            this.recents = raw ? JSON.parse(raw) : [];
                            if (!Array.isArray(this.recents)) this.recents = [];
                        } catch (e) {
                            this.recents = [];
                        }

                        // Receive QR scan result from Livewire #[On('nb:scan-result')]
                        // → forwarded as 'nb:portal-scanned' so we don't accidentally pick
                        // up scan results from other components.
                        const onScanned = (payload) => {
                            const u = (payload?.url || '').toString().trim();
                            if (!u) return;
                            this.url = u;
                            this.submit();
                        };
                        const wireReady = () => {
                            try { window.Livewire?.on?.('nb:portal-scanned', onScanned); } catch (_) {}
                        };
                        if (window.Livewire) {
                            wireReady();
                        } else {
                            window.addEventListener('livewire:initialized', wireReady, { once: true });
                        }
                    },

                    submit() {
                        if (this.connecting) return;
                        this.error = '';

                        let u = (this.url || '').trim();
                        if (!u) {
                            this.error = 'Paste the dev server URL (e.g. http://192.168.1.42:1420)';
                            return;
                        }
                        if (!/^https?:\/\//i.test(u)) {
                            u = 'http://' + u;
                        }
                        try {
                            // throws on bad URLs
                            new URL(u);
                        } catch (e) {
                            this.error = 'Invalid URL';
                            return;
                        }
                        this.connect(u.replace(/\/+$/, ''));
                    },

                    connect(url) {
                        if (!url || this.connecting) return;
                        this.target = url;
                        this.connecting = true;
                        // Mirror to top-level localStorage too — this page lives inside
                        // the app iframe, but the bundle loader runs in the parent.
                        const stores = [];
                        try { stores.push(localStorage); } catch (e) {}
                        try { if (window.top && window.top !== window) stores.push(window.top.localStorage); } catch (e) {}
                        for (const store of stores) {
                            try {
                                store.setItem('nb:bundleBase', url);
                                const raw = store.getItem('nb:recentBundles');
                                const prev = raw ? JSON.parse(raw) : [];
                                const list = (Array.isArray(prev) ? prev : []).filter((u) => u !== url);
                                list.unshift(url);
                                store.setItem('nb:recentBundles', JSON.stringify(list.slice(0, 5)));
                            } catch (e) {}
                        }
                        // Reload the TOP frame so nativeblade.js re-runs and picks up
                        // the new bundle URL. Reloading this iframe alone leaves the
                        // parent bundle loader untouched.
                        setTimeout(() => {
                            try {
                                (window.top || window).location.reload();
                            } catch (e) {
                                window.location.reload();
                            }
                        }, 250);
                    },

                    forget(i) {
                        this.recents.splice(i, 1);
                        try {
                            localStorage.setItem('nb:recentBundles', JSON.stringify(this.recents));
                        } catch (e) {}
                    },
                };
            };
        }
    </script>
</div>
