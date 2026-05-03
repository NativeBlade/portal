# Privacy Policy

**Last updated: May 3, 2026**

This Privacy Policy describes how Nativeblade Portal (the "App", "we", "our") handles information when you use the application. By installing or using Nativeblade Portal, you agree to this policy.

## What Nativeblade Portal Is

Nativeblade Portal is a runtime that loads third party Laravel based applications inside an isolated sandbox on your device. The Portal itself does not collect personal data. Loaded applications run independently and may request their own permissions, which you can grant or deny on a case by case basis.

## Information We Do Not Collect

The Portal does not collect, store, or transmit:

* Personal identifiers (name, email, phone number)
* Location data
* Contacts, calendar entries, photos, or files from your device
* Analytics, telemetry, or crash reports tied to your identity
* Advertising identifiers

## Information Loaded Applications May Request

Each application you load through the Portal runs in its own sandbox with its own database, storage, and session state. A loaded application may request access to:

* Camera
* Microphone
* Photo library (read or save)
* Location (foreground or background)
* Contacts
* Calendar
* Bluetooth
* NFC
* Biometric authentication (Face ID, Touch ID)
* Push notifications

Permission prompts are shown by iOS only when a loaded app actively invokes the corresponding API. You can review and revoke these permissions at any time from iOS Settings, under Privacy and Security.

The Portal does not have access to the data inside a loaded application's sandbox. Each loaded app is responsible for its own data handling and should provide its own privacy policy.

## Local Storage

Loaded applications store their data locally on your device using SQLite, file system, or session storage, all scoped to the loaded app's sandbox. This data never leaves your device unless the loaded application itself transmits it to its own backend.

If you uninstall the Portal from your device, all loaded apps and their local data are removed by iOS along with the app.

## Third Party Services

The Portal itself does not integrate with analytics, advertising, or tracking SDKs. Loaded applications may communicate with their own backends or third party services. Such communication is governed by the loaded application's own privacy policy and is outside our control.

## Children's Privacy

Nativeblade Portal does not knowingly collect personal data from anyone, including children under 13. The App is intended for general audiences and does not contain advertising or in app purchases targeted at minors.

## Data Retention

The Portal stores only the list of loaded apps and their cached bundles, locally on your device. We do not maintain server side records of your activity. To remove all data, uninstall the App from your device.

## Your Rights

Because the Portal does not transmit personal data to us, there is no server side record to access, correct, or delete. All data lives on your device and is under your control.

## Changes to This Policy

We may update this policy from time to time. The "Last updated" date at the top reflects the most recent revision. Continued use of the App after changes are posted constitutes acceptance of the updated policy.

## Contact

For privacy questions or requests, contact:

**Email:** jeffleyd@gmail.com
