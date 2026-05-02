<p align="center">
  <strong>NativeBlade Portal</strong>
</p>

<p align="center">
  Test your NativeBlade apps on a real device — without ever installing the toolchain on it.
</p>

<p align="center">
  <em>Like Expo Go, but for Laravel.</em>
</p>

---

## What is this?

The Portal is a **pre-built host app** for [NativeBlade](https://github.com/nativeblade/nativeblade). Install it once on your phone, paste the URL of your local dev server (or scan a QR), and your Laravel + Livewire app loads instantly — with full access to camera, push notifications, biometric, NFC, and every other plugin NativeBlade ships.

No Xcode. No Android Studio. No Rust. No build pipeline. Just install the Portal, point it at your computer, and ship.

## Who is this for?

- **Developers** who want to test NativeBlade apps on iPhone / Android without setting up the native toolchain
- **Product folks** who want to preview the next release on a real device
- **Demo days** where you need to show the app on stage without `nativeblade:dev` running on the projector
- **Cross-platform testing** — same Portal app handles iOS, Android, and desktop

## How it works

```
┌────────────────┐                        ┌────────────────┐
│ Your laptop    │                        │ Your phone     │
│                │                        │                │
│ nativeblade:dev│ ◄── HTTP, same wifi ──►│ Portal app     │
│ → vite serves  │                        │ → loads URL    │
│   bundle.gz    │                        │ → renders      │
└────────────────┘                        └────────────────┘
```

1. On your laptop, run `php artisan nativeblade:dev --host=192.168.1.42` in your NativeBlade project
2. Open the Portal on your phone (must be on the same wifi)
3. Paste `http://192.168.1.42:1420` or scan the QR
4. Boom — your app is running natively

The Portal has every plugin pre-bundled, so anything your app does (`NativeBlade::camera()`, `NativeBlade::scan()`, push notifications, fingerprint, file picker, etc.) just works.

## Installing the Portal

| Platform | How |
|----------|-----|
| **Android** | [Play Store](#) (or sideload the APK from Releases) |
| **iOS** | [TestFlight](#) (App Store coming) |
| **Desktop** | [Releases](https://github.com/nativeblade/portal/releases) — `.msi`, `.dmg`, `.AppImage` |

## Using the Portal — for app developers

Once installed, the Portal opens to a single screen:

- **URL field** — paste your dev server URL
- **Scan QR** — on mobile, tap to open the camera and scan a QR encoding the URL
- **Recent** — past URLs you've connected to, one tap to reconnect

That's the whole UX. After you connect, the screen disappears and your Laravel app takes over.

To go back to the Portal (e.g. switch projects), press **Shift+Esc** on desktop or use the gesture documented in your build.

## Generating a QR code on your laptop

Any QR generator works. The simplest:

```bash
# macOS / Linux — needs `qrencode`
echo "http://$(ipconfig getifaddr en0):1420" | qrencode -t ANSIUTF8

# Windows — paste the URL into qr.io or any web QR generator
```

Show that QR on screen, scan with the Portal — connected.

## Building the Portal yourself

This repo is the source for the Portal app itself, built with NativeBlade. To build a custom-branded Portal (your company's logo, your colors, your bundle URL pre-filled):

```bash
git clone https://github.com/nativeblade/portal nativeblade-portal
cd nativeblade-portal
composer install
npm install

# Make your changes (logo, copy, etc.)

php artisan nativeblade:dev          # preview locally
php artisan nativeblade:build android
php artisan nativeblade:build ios    # macOS only — or use GitHub Actions
```

For iOS without a Mac, see [`.github/workflows/README.md`](.github/workflows/README.md) — it walks through building and shipping iOS entirely from Windows/Linux + GitHub Actions.

## Why "Portal"?

Because that's what it is — a portal you walk through to your app. The host app is the wrapper; your Laravel code is the world on the other side.

## License

MIT.

## Links

- [NativeBlade framework](https://github.com/nativeblade/nativeblade)
- [Documentation](https://github.com/nativeblade/nativeblade/blob/main/README.md)
- [Discord](https://discord.gg/Vzpach5J2h)
