@props(['title', 'tab'])

<x-nb-mobile::page>
@once
    <script>
        // Shared store for the portal: list of apps, favorites, last access,
        // and the connect-to-bundle action. All three tabs read from / write
        // to the same localStorage entry (nb:apps), with a one-time migration
        // from the old nb:recentBundles (array of strings) format.
        if (!window.nbPortal) {
            window.nbPortal = (function () {
                const KEY_APPS = 'nb:apps';
                const KEY_LEGACY = 'nb:recentBundles';
                const KEY_BUNDLE = 'nb:bundleBase';

                function readApps() {
                    try {
                        const raw = localStorage.getItem(KEY_APPS);
                        if (raw) {
                            const arr = JSON.parse(raw);
                            return Array.isArray(arr) ? arr : [];
                        }
                        // Migrate old format (array of strings) to new schema.
                        const legacy = localStorage.getItem(KEY_LEGACY);
                        if (legacy) {
                            const list = JSON.parse(legacy);
                            if (Array.isArray(list)) {
                                const now = Date.now();
                                return list.map((url) => ({
                                    url,
                                    name: '',
                                    favorite: false,
                                    lastAccess: now,
                                    firstSeen: now,
                                }));
                            }
                        }
                    } catch (e) {}
                    return [];
                }

                function writeApps(apps) {
                    const stores = [];
                    try { stores.push(localStorage); } catch (e) {}
                    try {
                        if (window.top && window.top !== window) stores.push(window.top.localStorage);
                    } catch (e) {}
                    for (const s of stores) {
                        try { s.setItem(KEY_APPS, JSON.stringify(apps)); } catch (e) {}
                    }
                }

                function normalizeUrl(input) {
                    let u = (input || '').trim();
                    if (!u) return null;
                    if (!/^https?:\/\//i.test(u)) u = 'http://' + u;
                    try {
                        new URL(u);
                    } catch (e) {
                        return null;
                    }
                    return u.replace(/\/+$/, '');
                }

                function rememberApp(url) {
                    const apps = readApps();
                    const now = Date.now();
                    const idx = apps.findIndex((a) => a.url === url);
                    if (idx >= 0) {
                        apps[idx].lastAccess = now;
                        // Move to front (most recent first).
                        const [item] = apps.splice(idx, 1);
                        apps.unshift(item);
                    } else {
                        apps.unshift({
                            url,
                            name: '',
                            favorite: false,
                            lastAccess: now,
                            firstSeen: now,
                        });
                    }
                    writeApps(apps);
                    return apps;
                }

                function setBundleAndReload(url) {
                    const stores = [];
                    try { stores.push(localStorage); } catch (e) {}
                    try {
                        if (window.top && window.top !== window) stores.push(window.top.localStorage);
                    } catch (e) {}
                    for (const s of stores) {
                        try { s.setItem(KEY_BUNDLE, url); } catch (e) {}
                    }
                    setTimeout(() => {
                        try {
                            (window.top || window).location.reload();
                        } catch (e) {
                            window.location.reload();
                        }
                    }, 250);
                }

                return {
                    list() { return readApps(); },
                    save(apps) { writeApps(apps); },
                    normalizeUrl,
                    remember: rememberApp,
                    // Pulls name + icon from the dev server (/__app_meta). Best
                    // effort: offline servers keep whatever was cached last.
                    async refreshMeta(url) {
                        try {
                            const res = await fetch(url.replace(/\/+$/, '') + '/__app_meta', { signal: AbortSignal.timeout(2500) });
                            if (!res.ok) return null;
                            const meta = await res.json();
                            this.setMeta(url, meta);
                            return meta;
                        } catch (e) { return null; }
                    },
                    setMeta(url, meta) {
                        if (!meta) return;
                        const apps = readApps();
                        const idx = apps.findIndex((a) => a.url === url);
                        if (idx < 0) return;
                        if (meta.name && !apps[idx].name) apps[idx].name = meta.name; // manual rename wins
                        if (meta.icon) apps[idx].icon = meta.icon;
                        writeApps(apps);
                    },
                    clearHistory() {
                        const apps = readApps().filter((a) => a.favorite);
                        writeApps(apps);
                        return apps;
                    },
                    remove(url) {
                        const apps = readApps().filter((a) => a.url !== url);
                        writeApps(apps);
                        return apps;
                    },
                    toggleFavorite(url) {
                        const apps = readApps();
                        const idx = apps.findIndex((a) => a.url === url);
                        if (idx >= 0) {
                            apps[idx].favorite = !apps[idx].favorite;
                            writeApps(apps);
                        }
                        return apps;
                    },
                    rename(url, name) {
                        const apps = readApps();
                        const idx = apps.findIndex((a) => a.url === url);
                        if (idx >= 0) {
                            apps[idx].name = (name || '').trim();
                            writeApps(apps);
                        }
                        return apps;
                    },
                    async connect(url) {
                        const u = normalizeUrl(url);
                        if (!u) return false;
                        rememberApp(u);
                        // Give the meta fetch a short head start so name/icon are
                        // saved before the reload; a slow server doesn't hold the
                        // connection hostage.
                        await Promise.race([
                            this.refreshMeta(u),
                            new Promise((r) => setTimeout(r, 900)),
                        ]);
                        setBundleAndReload(u);
                        return true;
                    },
                };
            })();
        }
    </script>
@endonce

    <x-nb-mobile::navbar :title="$title" />

    <main class="flex-1 relative min-h-0 flex flex-col">
        {{ $slot }}
    </main>

    <x-nb-mobile::tabbar>
        <x-nb-mobile::tabbar-item href="/" :active="$tab === 'home'" label="Load">
            <x-slot name="icon">
                <x-nativeblade-icon :name="$tab === 'home' ? 'house-fill' : 'house'" size="22" />
            </x-slot>
        </x-nb-mobile::tabbar-item>

        <x-nb-mobile::tabbar-item href="/apps" :active="$tab === 'apps'" label="My Apps">
            <x-slot name="icon">
                <x-nativeblade-icon :name="$tab === 'apps' ? 'folder-fill' : 'folder'" size="22" />
            </x-slot>
        </x-nb-mobile::tabbar-item>

        <x-nb-mobile::tabbar-item href="/about" :active="$tab === 'about'" label="About">
            <x-slot name="icon">
                <x-nativeblade-icon :name="$tab === 'about' ? 'info-fill' : 'info'" size="22" />
            </x-slot>
        </x-nb-mobile::tabbar-item>
    </x-nb-mobile::tabbar>
</x-nb-mobile::page>
