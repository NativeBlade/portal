<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NativeBlade\Config\AndroidConfig;
use NativeBlade\Config\DesktopConfig;
use NativeBlade\Config\IosConfig;
use NativeBlade\Config\Permission;
use NativeBlade\Config\Plugin;
use NativeBlade\Config\PrivacyApi;
use NativeBlade\Facades\NativeBladeConfig;

class AppServiceProvider extends ServiceProvider
{
    CONST VERSION = '1.8.3';
    const VERSION_NUMBER = 1008003;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Portal mode: NO plugins() declaration on purpose.
        // All Tauri plugins are bundled so any user-loaded Laravel bundle
        // can call any NativeBlade API. Trade-off: every permission text
        // below is reviewed by Apple, so we explain the portal nature
        // explicitly in each one.

        NativeBladeConfig::bundlePush(
            url: 'https://nativeblade.github.io/portal-updates/version.json',
        );

        NativeBladeConfig::name('Nativeblade');

        NativeBladeConfig::desktop(function (DesktopConfig $config) {
            $config->identifier('com.nativeblade.app')
                ->version(self::VERSION, self::VERSION_NUMBER)
                ->size(1200, 800)
                ->icon('src-tauri/icons/logo.png')
                ->minSize(800, 600)
                ->resizable()
                ->center()
                ->splashBackground('#0a0a0a');
        });

        NativeBladeConfig::android(function (AndroidConfig $config) {
            $config->identifier('com.nativeblade.app')
                ->version(self::VERSION, self::VERSION_NUMBER)
                ->minSdk(28)
                ->targetSdk(35)
                ->orientation('portrait')
                ->statusBar(style: 'dark')
                ->splashBackground('#0a0a0a')
                ->permissions([
                    Permission::CAMERA => 'Loaded apps may use the camera. Permission is requested only when an app actively invokes it.',
                    Permission::LOCATION => 'Loaded apps may access location. Permission is requested only when an app actively invokes it.',
                    Permission::PHOTOS => 'Loaded apps may access the photo library. Permission is requested only when an app actively invokes it.',
                    Permission::NOTIFICATIONS => 'Receive notifications from the loaded app. Permission is requested only when an app subscribes to push.',
                    Permission::MICROPHONE => 'Loaded apps may record audio. Permission is requested only when an app actively invokes it.',
                    Permission::BIOMETRIC => 'Loaded apps may use Fingerprint authentication.',
                    Permission::NFC => 'Loaded apps may read NFC tags.',
                    Permission::VIBRATE => 'Loaded apps may trigger haptic feedback.',
                ]);
        });

        NativeBladeConfig::ios(function (IosConfig $config) {
            $config->identifier('com.nativeblade.app')
                ->version(self::VERSION, self::VERSION_NUMBER)
                ->minIosVersion('15.0')
                ->orientation('portrait')
                ->statusBar(style: 'dark')
                ->splashBackground('#0a0a0a')
                ->permissions([
                    Permission::CAMERA => 'Nativeblade is a runtime that loads Laravel-based apps. The camera is used only when a loaded app actively invokes it.',
                    Permission::LOCATION => 'Nativeblade is a runtime that loads Laravel-based apps. Your location is used only when a loaded app actively invokes it.',
                    Permission::LOCATION_ALWAYS => 'Nativeblade is a runtime that loads Laravel-based apps. Background location is used only when a loaded app actively invokes it.',
                    Permission::PHOTOS => 'Nativeblade is a runtime that loads Laravel-based apps. Your photo library is accessed only when a loaded app actively invokes it.',
                    Permission::PHOTOS_ADD => 'Nativeblade is a runtime that loads Laravel-based apps. Photos are saved only when a loaded app actively invokes it.',
                    Permission::MICROPHONE => 'Nativeblade is a runtime that loads Laravel-based apps. The microphone is used only when a loaded app actively invokes it.',
                    Permission::BIOMETRIC => 'Authenticate using Face ID when a loaded app requests biometric verification.',
                    Permission::NFC => 'Nativeblade is a runtime that loads Laravel-based apps. NFC is used only when a loaded app actively reads tags.',
                    Permission::CONTACTS => 'Nativeblade is a runtime that loads Laravel-based apps. Contacts are accessed only when a loaded app actively invokes it.',
                    Permission::CALENDAR => 'Nativeblade is a runtime that loads Laravel-based apps. The calendar is accessed only when a loaded app actively invokes it.',
                    Permission::BLUETOOTH => 'Nativeblade is a runtime that loads Laravel-based apps. Bluetooth is used only when a loaded app actively invokes it.',
                ])
                ->privacyManifest([
                    PrivacyApi::USER_DEFAULTS => PrivacyApi::USER_DEFAULTS_APP,
                    PrivacyApi::FILE_TIMESTAMP => PrivacyApi::FILE_TIMESTAMP_THIRD_PARTY,
                    PrivacyApi::SYSTEM_BOOT_TIME => PrivacyApi::BOOT_TIME_ELAPSED,
                    PrivacyApi::DISK_SPACE => PrivacyApi::DISK_SPACE_WRITE_CHECK,
                ]);
        });

        NativeBladeConfig::plugins([
            Plugin::MEDIA,
            Plugin::PUSH,
            Plugin::IN_APP_REVIEW,
            Plugin::SECURE_STORAGE,
            Plugin::SHARING,
            Plugin::GEOLOCATION,
            Plugin::BIOMETRIC,
            Plugin::BARCODE_SCANNER,
            Plugin::NFC,
            Plugin::HAPTICS,
            Plugin::CLIPBOARD,
            Plugin::HTTP,
            Plugin::UPLOAD,
            Plugin::DEEP_LINK,
        ]);

        NativeBladeConfig::transition('none');
    }
}
