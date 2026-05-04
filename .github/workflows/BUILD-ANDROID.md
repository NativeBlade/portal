# GitHub Actions, Android build

The `build-android.yml` workflow produces a signed AAB (and APK) on every `v*` tag push, or on manual `workflow_dispatch`. With `upload_to_play` enabled, it also pushes to the Play Console internal track.

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

## Optional: Play Console auto upload

If you want the workflow to push to Play Console internal track on each tag, add one more secret.

### A. Create a service account in Google Cloud

1. Go to <https://console.cloud.google.com/iam-admin/serviceaccounts>
2. Create a project if you don't have one
3. Create service account, give it any name, no roles needed at this step
4. Click into the new account, go to **Keys** tab, **Add Key > Create new key > JSON**, download the file

### B. Grant Play Console access

1. Go to <https://play.google.com/console> > **Setup > API access**
2. Link the Google Cloud project from step A
3. Grant access to the service account, give it permission **Release apps to testing tracks**
4. Choose your app (`com.nativeblade.app`) under app permissions

### C. Add the JSON as a GitHub Secret

| Secret | Value |
|--------|-------|
| `PLAY_SERVICE_ACCOUNT_JSON` | Full contents of the JSON file from step A |

### D. Pre create the app in Play Console

The auto upload action requires the app to already exist in Play Console with the same package name (`com.nativeblade.app`). Create it manually first:

1. Go to <https://play.google.com/console> > **Create app**
2. App name: `Nativeblade`
3. Default language: any
4. Free or paid
5. Confirm declarations
6. Submit at least one manual upload first (the auto upload won't work as the very first release)

## Triggering a build

**Tag push** (recommended for releases):
```bash
git tag v1.0.0
git push origin v1.0.0
```

The workflow runs automatically and uploads to Play Console internal track if configured.

**Manual run** (Actions tab > Build Android > Run workflow):
- Toggle "Upload to Play Console" if you want it submitted; leave off for a download only AAB artifact.

## Cost

Linux runner is included in the free tier for public repos. For private repos, ubuntu builds count at 1x rate (vs 10x for macOS). Typical Android build is 5 to 8 minutes.

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `Keystore was tampered with, or password was incorrect` | Wrong password in secret, or keystore corrupted during base64 | Re-run `base64 -w 0`, paste the full string into the secret |
| `INSTALL_FAILED_INVALID_APK` on real device | Unsigned or wrong signature | Confirm `nativeblade:sign android` ran in CI logs and produced `keystore.properties` |
| `Failure when receiving data from the peer` on Play upload | Service account missing release permission | Re-check Play Console > API access > service account permissions |
| `Could not find android NDK` | NDK version mismatch | Adjust `packages: ndk;26.1.10909125` in workflow to a version available in `setup-android` |
| Build hangs at "Configure project" | Gradle download from a slow region | Re-run; usually transient |

## Renewing nothing

Unlike iOS, Android signing keys do not expire. Generate the keystore once, back it up, and reuse it for the lifetime of the app.
