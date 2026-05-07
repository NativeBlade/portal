<x-portal-shell title="My Apps" tab="apps">
    <div
        class="flex-1 overflow-y-auto py-5"
        x-data="portalApps()"
        x-init="init()"
    >
        {{-- Empty state --}}
        <template x-if="apps.length === 0">
            <div class="flex flex-col items-center justify-center text-center px-8 mt-16">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <x-nativeblade-icon name="folder" size="32" class="text-gray-400" />
                </div>
                <h2 class="text-base font-semibold text-gray-900 mb-1">No apps yet</h2>
                <p class="text-sm text-gray-500 max-w-xs">
                    Apps you load from the Load tab will appear here for quick access.
                </p>
            </div>
        </template>

        {{-- Favorites --}}
        <template x-if="favorites.length > 0">
            <div class="mb-4">
                <x-nb-mobile::list inset title="Favorites">
                    <template x-for="item in favorites" :key="item.url">
                        <x-nb-mobile::list-item>
                            <button
                                type="button"
                                @click="connect(item.url)"
                                class="flex-1 min-w-0 text-left"
                            >
                                <div class="text-[15px] text-gray-900 truncate" x-text="item.name || displayHost(item.url)"></div>
                                <div class="font-mono text-xs text-gray-500 truncate" x-text="item.url"></div>
                            </button>
                            <x-slot name="after">
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click.stop="toggleFavorite(item.url)"
                                        class="p-2 text-yellow-400 active:opacity-50"
                                        aria-label="Unfavorite"
                                    >
                                        <x-nativeblade-icon name="star-fill" size="20" />
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="confirmRemove(item)"
                                        class="p-2 text-gray-400 active:text-red-500"
                                        aria-label="Remove"
                                    >
                                        <x-nativeblade-icon name="trash" size="20" />
                                    </button>
                                </div>
                            </x-slot>
                        </x-nb-mobile::list-item>
                    </template>
                </x-nb-mobile::list>
            </div>
        </template>

        {{-- All apps (with "All" header when there are favorites above) --}}
        <template x-if="others.length > 0 && favorites.length > 0">
            <div>
                <x-nb-mobile::list inset title="All">
                    <template x-for="item in others" :key="item.url">
                        <x-nb-mobile::list-item>
                            <button
                                type="button"
                                @click="connect(item.url)"
                                class="flex-1 min-w-0 text-left"
                            >
                                <div class="text-[15px] text-gray-900 truncate" x-text="item.name || displayHost(item.url)"></div>
                                <div class="font-mono text-xs text-gray-500 truncate" x-text="item.url"></div>
                                <div class="text-[11px] text-gray-400 mt-0.5" x-text="lastAccessLabel(item.lastAccess)"></div>
                            </button>
                            <x-slot name="after">
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click.stop="toggleFavorite(item.url)"
                                        class="p-2 text-gray-300 active:opacity-50"
                                        aria-label="Favorite"
                                    >
                                        <x-nativeblade-icon name="star" size="20" />
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="confirmRemove(item)"
                                        class="p-2 text-gray-400 active:text-red-500"
                                        aria-label="Remove"
                                    >
                                        <x-nativeblade-icon name="trash" size="20" />
                                    </button>
                                </div>
                            </x-slot>
                        </x-nb-mobile::list-item>
                    </template>
                </x-nb-mobile::list>
            </div>
        </template>

        {{-- All apps (no header when there are no favorites above) --}}
        <template x-if="others.length > 0 && favorites.length === 0">
            <div>
                <x-nb-mobile::list inset>
                    <template x-for="item in others" :key="item.url">
                        <x-nb-mobile::list-item>
                            <button
                                type="button"
                                @click="connect(item.url)"
                                class="flex-1 min-w-0 text-left"
                            >
                                <div class="text-[15px] text-gray-900 truncate" x-text="item.name || displayHost(item.url)"></div>
                                <div class="font-mono text-xs text-gray-500 truncate" x-text="item.url"></div>
                                <div class="text-[11px] text-gray-400 mt-0.5" x-text="lastAccessLabel(item.lastAccess)"></div>
                            </button>
                            <x-slot name="after">
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click.stop="toggleFavorite(item.url)"
                                        class="p-2 text-gray-300 active:opacity-50"
                                        aria-label="Favorite"
                                    >
                                        <x-nativeblade-icon name="star" size="20" />
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="confirmRemove(item)"
                                        class="p-2 text-gray-400 active:text-red-500"
                                        aria-label="Remove"
                                    >
                                        <x-nativeblade-icon name="trash" size="20" />
                                    </button>
                                </div>
                            </x-slot>
                        </x-nb-mobile::list-item>
                    </template>
                </x-nb-mobile::list>
            </div>
        </template>
    </div>

    <script>
        if (!window.portalApps) {
            window.portalApps = function () {
                return {
                    apps: [],

                    init() {
                        this.refresh();
                    },

                    refresh() {
                        this.apps = window.nbPortal.list();
                    },

                    get favorites() {
                        return this.apps.filter((a) => a.favorite);
                    },

                    get others() {
                        return this.apps.filter((a) => !a.favorite);
                    },

                    displayHost(url) {
                        try { return new URL(url).host; } catch (e) { return url; }
                    },

                    lastAccessLabel(ts) {
                        if (!ts) return '';
                        const diff = Date.now() - ts;
                        const sec = Math.floor(diff / 1000);
                        if (sec < 60) return 'just now';
                        const min = Math.floor(sec / 60);
                        if (min < 60) return min + 'm ago';
                        const hr = Math.floor(min / 60);
                        if (hr < 24) return hr + 'h ago';
                        const day = Math.floor(hr / 24);
                        if (day < 30) return day + 'd ago';
                        const mo = Math.floor(day / 30);
                        return mo + 'mo ago';
                    },

                    toggleFavorite(url) {
                        window.nbPortal.toggleFavorite(url);
                        this.refresh();
                    },

                    confirmRemove(item) {
                        const label = item.name || this.displayHost(item.url);
                        if (window.confirm('Remove "' + label + '" from My Apps?')) {
                            window.nbPortal.remove(item.url);
                            this.refresh();
                        }
                    },

                    connect(url) {
                        window.nbPortal.connect(url);
                    },
                };
            };
        }
    </script>
</x-portal-shell>
