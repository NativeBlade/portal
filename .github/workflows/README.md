# GitHub Actions — iOS build (no Mac required)

The `build-ios.yml` workflow builds and (optionally) uploads `Nativeblade.ipa` to TestFlight on every `v*` tag push, or on manual `workflow_dispatch`.

You can ship iOS apps from a **Windows or Linux machine** without ever owning a Mac. Apple's developer portal accepts certificate requests generated anywhere — only the actual build needs macOS, which the GitHub runner provides.

## One-time setup (≈ 35 minutes)

You'll generate a private key + Certificate Signing Request locally, exchange it for a distribution certificate at developer.apple.com, then convert and upload to GitHub.

> Use **Git Bash** on Windows for the OpenSSL commands. The OpenSSL bundled with Git Bash includes a working `openssl.cnf`, unlike the standalone Windows builds.

### 1. Generate a Certificate Signing Request

```bash
cd nativeblade-portal

openssl genrsa -out apple-private.key 2048

openssl req -new -key apple-private.key \
  -out CertificateSigningRequest.certSigningRequest \
  -subj "//emailAddress=you@example.com\CN=Your Name\C=US"
```

> The double slash and backslash separators (`//emailAddress=...\CN=...`) work around MSYS path mangling on Git Bash. On Linux/macOS use single slashes (`/emailAddress=.../CN=...`).

**Keep `apple-private.key` safe and out of git** — it's the only thing that lets you re-issue your certificate. The `.gitignore` in this project already excludes `*.key`.

### 2. Apple Developer Portal — Certificate

1. Go to <https://developer.apple.com/account/resources/certificates>
2. Click **+** → choose **Apple Distribution** → Continue
3. Upload `CertificateSigningRequest.certSigningRequest`
4. Download the resulting `distribution.cer`

### 3. Apple Developer Portal — App ID

1. Go to <https://developer.apple.com/account/resources/identifiers>
2. Click **+** → **App IDs** → **App** → Continue
3. Description: `Nativeblade`
4. Bundle ID: **Explicit** → `com.nativeblade.app`
5. Enable any capabilities the app needs (Push Notifications, etc.)
6. Continue → Register

### 4. Apple Developer Portal — Provisioning Profile

1. Go to <https://developer.apple.com/account/resources/profiles>
2. Click **+** → **App Store** (under Distribution) → Continue
3. Select the `com.nativeblade.app` App ID
4. Select the Distribution certificate you just created
5. Profile Name: `Nativeblade App Store`
6. Generate → Download `Nativeblade_App_Store.mobileprovision`

### 5. App Store Connect — Create the app record ⚠️

> **Don't skip this step.** Without an App Store Connect record, the build will succeed and produce a valid signed IPA, but the upload to TestFlight will fail at the very end with:
>
> ```
> ERROR: No suitable application records were found. Verify your bundle identifier
>        "com.nativeblade.app" is correct and that you are signed in with an Apple ID
>        that has access to the app in App Store Connect. (1190)
> ```
>
> By that point you've already paid for ~30 minutes of CI time. Create the record before pushing the first tag.

1. Go to <https://appstoreconnect.apple.com>
2. **My Apps** → **+** → **New App**
3. Fill in:
   - **Platforms:** iOS
   - **Name:** `Nativeblade` (or your app's name — this is what users see in the App Store)
   - **Primary Language:** English (US) — or your locale
   - **Bundle ID:** `com.nativeblade.app` (must match the App ID from step 3)
   - **SKU:** any unique string for your records, e.g. `nativeblade-portal-001`
   - **User Access:** Full Access
4. Click **Create**

You don't need to fill in the store listing, screenshots, descriptions, or pricing yet — just the record. Those go in before submitting for App Store review, but TestFlight uploads only need the record to exist.

### 6. Convert `.cer` to `.p12` (Git Bash)

```bash
# Convert Apple's binary cert to PEM
openssl x509 -in distribution.cer -inform DER -out distribution.pem -outform PEM

# Combine private key + cert into a password-protected .p12
# The -legacy flag is REQUIRED — without it OpenSSL 3 uses AES/SHA-256
# which macOS's `security` tool can't import (you'll get "MAC verification
# failed (wrong password?)" in CI).
openssl pkcs12 -export -legacy \
  -inkey apple-private.key \
  -in distribution.pem \
  -out distribution.p12 \
  -password pass:YourStrongPassword123
```

If your OpenSSL doesn't support `-legacy` (OpenSSL 1.x), specify the legacy ciphers manually:

```bash
openssl pkcs12 -export \
  -keypbe PBE-SHA1-3DES \
  -certpbe PBE-SHA1-3DES \
  -macalg SHA1 \
  -inkey apple-private.key \
  -in distribution.pem \
  -out distribution.p12 \
  -password pass:YourStrongPassword123
```

Replace `YourStrongPassword123` with your own — you'll add it to GitHub Secrets next.

### 7. Encode certificate and profile to base64

```bash
base64 -w 0 distribution.p12 > cert.b64
base64 -w 0 Nativeblade_App_Store.mobileprovision > profile.b64
```

### 8. Add GitHub Secrets

Repository **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Value |
|--------|-------|
| `IOS_DIST_CERT_BASE64` | Contents of `cert.b64` |
| `IOS_DIST_CERT_PASSWORD` | The password you used in step 6 |
| `IOS_PROFILE_BASE64` | Contents of `profile.b64` |
| `APPLE_TEAM_ID` | 10-character Team ID from <https://developer.apple.com/account/membership> |
| `APPLE_ID` | Your Apple ID email |
| `APPLE_APP_PASSWORD` | App-specific password from <https://appleid.apple.com> → Sign-In and Security → App-Specific Passwords |

### 9. Clean up local sensitive files

```bash
rm distribution.cer distribution.pem distribution.p12 cert.b64 profile.b64
rm Nativeblade_App_Store.mobileprovision
# Keep apple-private.key and CertificateSigningRequest.certSigningRequest
# in a password manager / encrypted backup — you'll need them next year
# when the cert expires.
```

## `.env.deploy` — the runtime config that ships

The build needs an `.env` file at runtime. Your local `.env` is gitignored (correctly — it has dev URLs, debug flags, secrets, etc.) so CI can't use it.

The convention: commit `.env.deploy` to the repo with **only the values needed at runtime**, no secrets. The workflow copies it to `.env` before building. If `.env.deploy` is missing, it falls back to `.env.example`, then to an empty file.

Typical `.env.deploy` for a NativeBlade app:

```env
APP_NAME=YourApp
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=app://localhost

DB_CONNECTION=sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_LEVEL=error
```

`APP_KEY` stays blank — `php artisan key:generate --force` runs before the build and fills it in. That generated key is per-build, which is fine because NativeBlade clients use it only for cookie/session encryption inside their own SQLite.

Anything secret (API keys, third-party tokens) should NOT go in `.env.deploy`. Either inject via GitHub Secrets at build time (env vars → templated into `.env` via `sed` in the workflow), or fetch at runtime from your Laravel backend after authentication.

## Triggering a build

**Tag push** (recommended for releases):
```bash
git tag v1.0.0
git push origin v1.0.0
```

The workflow runs automatically and uploads to TestFlight.

**Manual run** (Actions tab → Build iOS → Run workflow):
- Toggle "Upload to TestFlight" if you want it submitted; leave off for a download-only IPA artifact.

## Watching the build

GitHub → Actions tab → click the running workflow. Build takes ~25-35 minutes the first time (Rust cold-compile of Tauri); ~10-15 minutes after that with the cargo cache warm.

## After the upload

- TestFlight: <https://appstoreconnect.apple.com> → My Apps → Nativeblade → TestFlight tab. The build appears in 5-15 minutes after upload (Apple's processing).
- App Store: same place → App Store tab → submit for review when ready.

## Cost

| Repo type | macOS minutes |
|-----------|---------------|
| Public | Free |
| Private | Counted at 10× rate (~$0.08/min) |

A typical build is ~25-35 min. On a free private repo (2,000 Linux min/month → ~200 macOS min), expect roughly 5-7 builds before hitting limits.

For frequent releases, make the repo public, or pay for additional minutes.

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `code signing is required for product type 'Application'` | Profile/cert not imported | Verify base64 secrets are correct (no line breaks). Re-encode with `base64 -w 0`. |
| `MAC verification failed during PKCS12 import (wrong password?)` | `.p12` generated with OpenSSL 3 default ciphers | Re-export with `openssl pkcs12 -export -legacy ...`, re-base64, update secret |
| `No matching profiles found` | Bundle ID mismatch | App ID at developer.apple.com must be exactly `com.nativeblade.app` |
| `altool: unable to upload — Invalid Team ID` | Wrong `APPLE_TEAM_ID` | Get the 10-char ID from Membership page, not Team Name |
| `Invalid email or password` on TestFlight upload | Using main Apple ID password | Generate an app-specific password, never the account password |
| Builds work but TestFlight rejects with "missing privacy manifest" | Missing `IosConfig::privacyManifest()` declarations | Add to `AppServiceProvider`, run `php artisan nativeblade:config`, rebuild |

## Renewing the certificate (every year)

Distribution certificates expire after 1 year. When that happens:

1. Repeat steps 2 and 5 (you can reuse the same `apple-private.key`)
2. Update `IOS_DIST_CERT_BASE64` and `IOS_DIST_CERT_PASSWORD` in GitHub Secrets
3. Provisioning profile auto-refreshes if you regenerate it at developer.apple.com after the new cert is in place

You don't need to bump the app version or change anything in the code.
