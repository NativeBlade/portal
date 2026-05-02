# GitHub Actions — iOS build (no Mac required)

The `build-ios.yml` workflow builds and (optionally) uploads `Nativeblade.ipa` to TestFlight on every `v*` tag push, or on manual `workflow_dispatch`.

You can ship iOS apps from a **Windows or Linux machine** without ever owning a Mac. Apple's developer portal accepts certificate requests generated anywhere — only the actual build needs macOS, which the GitHub runner provides.

## One-time setup (≈ 30 minutes)

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

### 5. Convert `.cer` to `.p12` (Git Bash)

```bash
# Convert Apple's binary cert to PEM
openssl x509 -in distribution.cer -inform DER -out distribution.pem -outform PEM

# Combine private key + cert into a password-protected .p12
openssl pkcs12 -export \
  -inkey apple-private.key \
  -in distribution.pem \
  -out distribution.p12 \
  -password pass:YourStrongPassword123
```

Replace `YourStrongPassword123` with your own — you'll add it to GitHub Secrets next.

### 6. Encode certificate and profile to base64

```bash
base64 -w 0 distribution.p12 > cert.b64
base64 -w 0 Nativeblade_App_Store.mobileprovision > profile.b64
```

### 7. Add GitHub Secrets

Repository **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Value |
|--------|-------|
| `IOS_DIST_CERT_BASE64` | Contents of `cert.b64` |
| `IOS_DIST_CERT_PASSWORD` | The password you used in step 5 |
| `IOS_PROFILE_BASE64` | Contents of `profile.b64` |
| `APPLE_TEAM_ID` | 10-character Team ID from <https://developer.apple.com/account/membership> |
| `APPLE_ID` | Your Apple ID email |
| `APPLE_APP_PASSWORD` | App-specific password from <https://appleid.apple.com> → Sign-In and Security → App-Specific Passwords |

### 8. Clean up local sensitive files

```bash
rm distribution.cer distribution.pem distribution.p12 cert.b64 profile.b64
rm Nativeblade_App_Store.mobileprovision
# Keep apple-private.key and CertificateSigningRequest.certSigningRequest
# in a password manager / encrypted backup — you'll need them next year
# when the cert expires.
```

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
