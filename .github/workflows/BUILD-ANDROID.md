# GitHub Actions, Android build

The `build-android.yml` workflow produces a signed AAB (and APK) on every `v*` tag push, or on manual `workflow_dispatch`. Artifacts are uploaded to the run page; download and upload to Play Console manually.

Runs on `ubuntu-latest` (cheap, ~6 minutes Linux time per build on free tier).

## One time setup (~ 15 minutes)

You'll generate a release keystore locally, encode it to base64, and add four GitHub Secrets.

### 1. Generate a release keystore (local, only once)

If you already ran `php artisan nativeblade:sign android` and have `src-tauri/gen/android/upload-keystore.jks`, skip to step 2. Otherwise:

```bash
cd nativeblade-portal
php artisan nativeblade:sign android
```

It prompts for a password (use only letters and numbers, no special chars), name, organization, and country. Output is `src-tauri/gen/android/upload-keystore.jks`.

**Back up this keystore.** Losing it means you can never publish updates to the Play Store, you'd have to ship as a new app. Copy it to a password manager or encrypted backup along with the password.

### 2. Encode the keystore to base64

On Git Bash (Windows) or any Linux/macOS terminal:

```bash
base64 -w 0 src-tauri/gen/android/upload-keystore.jks > keystore.b64
```

The `-w 0` is important; without it, line breaks corrupt the secret.

### 3. Add GitHub Secrets

Repository **Settings > Secrets and variables > Actions > New repository secret**:

| Secret | Value |
|--------|-------|
| `ANDROID_KEYSTORE_BASE64` | Contents of `keystore.b64` |
| `ANDROID_KEYSTORE_PASSWORD` | The password you used in step 1 |
| `ANDROID_KEY_ALIAS` | Your alias (default `upload`) |
| `ANDROID_KEY_PASSWORD` | Same as keystore password unless you set a different one |

### 4. Clean up local sensitive files

```bash
rm keystore.b64
```

Keep `upload-keystore.jks` in your encrypted backup; the framework gitignores it from the repo automatically.

## Triggering a build

**Tag push** (recommended for releases):
```bash
git tag v1.0.0
git push origin v1.0.0
```

**Manual run**: Actions tab > Build Android > Run workflow.

In both cases, the AAB and APK appear as downloadable artifacts on the run page.

## Cost

Linux runner is included in the free tier for public repos. For private repos, ubuntu builds count at 1x rate (vs 10x for macOS). Typical Android build is 5 to 8 minutes.

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `Keystore was tampered with, or password was incorrect` | Wrong password in secret, or keystore corrupted during base64 | Re-run `base64 -w 0`, paste the full string into the secret |
| `INSTALL_FAILED_INVALID_APK` on real device | Unsigned or wrong signature | Confirm `nativeblade:sign android` ran in CI logs and produced `keystore.properties` |
| `Could not find android NDK` | NDK version mismatch | Adjust `packages: ndk;26.1.10909125` in workflow to a version available in `setup-android` |
| Build hangs at "Configure project" | Gradle download from a slow region | Re-run; usually transient |

## Renewing nothing

Unlike iOS, Android signing keys do not expire. Generate the keystore once, back it up, and reuse it for the lifetime of the app.
