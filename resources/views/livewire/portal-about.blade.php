<x-portal-shell title="About" tab="about">
    <div class="flex-1 overflow-y-auto py-5">

        {{-- Hero --}}
        <div class="flex flex-col items-center text-center px-6 mb-6">
            <x-nativeblade-image asset="logo_nb.png" class="w-20 h-20 rounded-2xl mb-4" />
            <h1 class="text-2xl font-bold text-gray-900 mb-1">NativeBlade Portal</h1>
            <p class="text-sm text-gray-500">Version {{ $version }}</p>
        </div>

        {{-- What it is --}}
        <x-nb-mobile::block inset>
            <h2 class="text-base font-semibold text-gray-900 mb-2">What is this?</h2>
            <p class="text-sm text-gray-600 leading-relaxed">
                NativeBlade Portal is a developer companion app for testing
                <button type="button"
                        onclick="__nbBridge('open_url', {url: 'https://github.com/NativeBlade/NativeBlade'})"
                        class="text-blue-500 underline">NativeBlade</button>
                applications on this device without building a separate native binary
                for each project.
            </p>
            <p class="text-sm text-gray-600 leading-relaxed mt-3">
                NativeBlade is an open-source framework that lets developers build
                desktop and mobile apps using PHP, Laravel and Livewire. Your full
                Laravel app runs inside a PHP WebAssembly runtime wrapped in a Tauri
                shell.
            </p>
        </x-nb-mobile::block>

        {{-- How to use --}}
        <x-nb-mobile::list inset title="How to use">
            <x-nb-mobile::list-item>
                <div class="flex gap-3 py-1">
                    <div class="shrink-0 w-7 h-7 rounded-full bg-blue-500 text-white text-sm font-semibold flex items-center justify-center">1</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[15px] text-gray-900">Start the dev server</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            On your computer, run
                            <code class="font-mono text-[11px] bg-gray-100 px-1 py-0.5 rounded">php artisan nativeblade:dev</code>
                            with the appropriate platform flag.
                        </div>
                    </div>
                </div>
            </x-nb-mobile::list-item>
            <x-nb-mobile::list-item>
                <div class="flex gap-3 py-1">
                    <div class="shrink-0 w-7 h-7 rounded-full bg-blue-500 text-white text-sm font-semibold flex items-center justify-center">2</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[15px] text-gray-900">Enter the URL or scan the QR</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            Go to the Load tab. Paste the URL printed in the terminal,
                            or tap the QR icon to scan.
                        </div>
                    </div>
                </div>
            </x-nb-mobile::list-item>
            <x-nb-mobile::list-item>
                <div class="flex gap-3 py-1">
                    <div class="shrink-0 w-7 h-7 rounded-full bg-blue-500 text-white text-sm font-semibold flex items-center justify-center">3</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[15px] text-gray-900">Test your app</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            Your Laravel app loads inside the portal. Press
                            <kbd class="font-mono text-[11px] bg-gray-100 px-1 py-0.5 rounded">Shift + Esc</kbd>
                            to come back here.
                        </div>
                    </div>
                </div>
            </x-nb-mobile::list-item>
        </x-nb-mobile::list>

        {{-- Requirements --}}
        <x-nb-mobile::list inset title="Requirements">
            <x-nb-mobile::list-item title="Local network" subtitle="Your phone and computer must be on the same Wi-Fi" />
            <x-nb-mobile::list-item title="HTTP only" subtitle="The dev server uses plain HTTP for local testing" />
        </x-nb-mobile::list>

        {{-- Links --}}
        <x-nb-mobile::list inset title="Resources">
            <x-nb-mobile::list-item
                title="Source code"
                subtitle="github.com/NativeBlade/NativeBlade"
                onclick="__nbBridge('open_url', {url: 'https://github.com/NativeBlade/NativeBlade'})"
                role="button"
                tabindex="0"
                class="cursor-pointer"
            >
                <x-slot name="after">
                    <x-nativeblade-icon name="arrow-square-out" size="18" class="text-gray-400" />
                </x-slot>
            </x-nb-mobile::list-item>
            <x-nb-mobile::list-item
                title="Documentation"
                subtitle="Guides, components, plugins"
                onclick="__nbBridge('open_url', {url: 'https://github.com/NativeBlade/NativeBlade#readme'})"
                role="button"
                tabindex="0"
                class="cursor-pointer"
            >
                <x-slot name="after">
                    <x-nativeblade-icon name="arrow-square-out" size="18" class="text-gray-400" />
                </x-slot>
            </x-nb-mobile::list-item>
            <x-nb-mobile::list-item
                title="Privacy policy"
                subtitle="What data this app uses"
                onclick="__nbBridge('open_url', {url: 'https://github.com/NativeBlade/nativeblade-portal/blob/main/PRIVACY.md'})"
                role="button"
                tabindex="0"
                class="cursor-pointer"
            >
                <x-slot name="after">
                    <x-nativeblade-icon name="arrow-square-out" size="18" class="text-gray-400" />
                </x-slot>
            </x-nb-mobile::list-item>
        </x-nb-mobile::list>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 mt-8 mb-4 px-6">
            <p>Built with Laravel, Livewire, Tauri and PHP WebAssembly.</p>
            <p class="mt-1">MIT License. Free and open source.</p>
        </div>
    </div>
</x-portal-shell>
