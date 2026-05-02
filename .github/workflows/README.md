# GitHub Actions — iOS build

The `build-ios.yml` workflow builds and (optionally) uploads `Nativeblade.ipa` to TestFlight on every `v*` tag push, or on manual `workflow_dispatch`.

## Required secrets

Set these in **Settings → Secrets and variables → Actions** of the GitHub repo.

| Secret | Where to get it | Format |
|--------|-----------------|--------|
| `IOS_DIST_CERT_BASE64` | Apple Developer → Certificates → "Apple Distribution" → download `.cer`, import into Keychain, export as `.p12` from Keychain Access. Then `base64 cert.p12 \| pbcopy`. | base64 string |
| `IOS_DIST_CERT_PASSWORD` | The password you set when exporting the `.p12`. | plain string |
| `IOS_PROFILE_BASE64` | Apple Developer → Profiles → create "App Store" profile for `com.nativeblade.app`, download `.mobileprovision`. Then `base64 profile.mobileprovision \| pbcopy`. | base64 string |
| `APPLE_TEAM_ID` | Apple Developer → Membership → Team ID (10-character string). | plain string |
| `APPLE_ID` | The Apple ID email for App Store Connect uploads. | email |
| `APPLE_APP_PASSWORD` | App-specific password from appleid.apple.com → Sign-In and Security → App-Specific Passwords. | plain string |

## How to encode the cert and profile

On macOS:

```bash
# Distribution certificate
base64 -i "Apple Distribution.p12" | pbcopy
# Paste into IOS_DIST_CERT_BASE64

# Provisioning profile
base64 -i "App_Store_com.nativeblade.app.mobileprovision" | pbcopy
# Paste into IOS_PROFILE_BASE64
```

On Linux:

```bash
base64 -w 0 cert.p12 > cert.b64
base64 -w 0 profile.mobileprovision > profile.b64
```

## How the build runs

1. **Tag push** (`git tag v1.0.1 && git push --tags`) — triggers automatically, builds + uploads to TestFlight.
2. **Manual** — Actions tab → "Build iOS" → "Run workflow" → optionally check "Upload to TestFlight".

## Cost note

`macos-14` runners cost ~10× more minutes than Linux on private repos. A typical build is 20-35 min. On free accounts, expect ~50-60 builds/month before hitting limits.

For public repos, macOS minutes are free. Make the repo public if budget is tight.

## Local first

Before relying on CI, validate the signing flow locally:

```bash
php artisan nativeblade:sign ios
# Open src-tauri/gen/apple/*.xcodeproj
# Signing & Capabilities → check "Automatically manage signing"
# Pick your team — Xcode creates the cert + profile
php artisan nativeblade:build ios
```

Once the local build passes, replicate the cert + profile in CI via the secrets above.
