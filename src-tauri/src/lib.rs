#[cfg_attr(mobile, tauri::mobile_entry_point)]
pub fn run() {
    let builder = nativeblade::build();

    // nativeblade:plugins:start

    #[cfg(all(any(target_os = "android", target_os = "ios"), feature = "media"))]
    let builder = builder.plugin(tauri_plugin_nativeblade_media::init());

    #[cfg(all(any(target_os = "android", target_os = "ios"), feature = "push"))]
    let builder = builder.plugin(tauri_plugin_nativeblade_push::init());

    #[cfg(feature = "geolocation")]
    let builder = builder.plugin(tauri_plugin_geolocation::init());

    #[cfg(all(any(target_os = "android", target_os = "ios"), feature = "biometric"))]
    let builder = builder.plugin(tauri_plugin_biometric::init());

    #[cfg(all(any(target_os = "android", target_os = "ios"), feature = "barcode_scanner"))]
    let builder = builder.plugin(tauri_plugin_barcode_scanner::init());

    #[cfg(all(any(target_os = "android", target_os = "ios"), feature = "nfc"))]
    let builder = builder.plugin(tauri_plugin_nfc::init());

    #[cfg(feature = "haptics")]
    let builder = builder.plugin(tauri_plugin_haptics::init());

    #[cfg(feature = "clipboard")]
    let builder = builder.plugin(tauri_plugin_clipboard_manager::init());

    #[cfg(feature = "upload")]
    let builder = builder.plugin(tauri_plugin_upload::init());

    #[cfg(feature = "notification")]
    let builder = builder.plugin(tauri_plugin_notification::init());

    #[cfg(feature = "http")]
    let builder = builder.plugin(tauri_plugin_http::init());

    #[cfg(feature = "deep_link")]
    let builder = builder.plugin(tauri_plugin_deep_link::init());

    #[cfg(feature = "shell")]
    let builder = builder.plugin(tauri_plugin_shell::init());
    // nativeblade:plugins:end

    builder
        .run(tauri::generate_context!())
        .expect("error while running NativeBlade");
}
