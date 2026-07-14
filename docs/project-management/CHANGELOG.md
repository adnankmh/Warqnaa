# Changelog

All notable changes to Warqna are recorded here. Entries should describe user-visible behavior, migrations, compatibility changes and known limitations.

## [0.2.5] - Build 181

### Added

- Challenge journeys with 10, 12 or 15 stages and five attempts.
- Level transition rewards.
- Localized Arabic and English bot names.
- Persistent global appearance controls.
- Administrator tools for token grants and friend requests.

### Changed

- Card dealing uses stronger randomness and bounded hand balancing.
- Store tabs, previews and primary actions use a unified premium presentation.
- Portrait layout is the default unless the player changes orientation.

### Fixed

- Controller reference regressions in game-room pages.
- XP helper references in additive patch modules.
- Optional premium-list action compatibility.
- Firebase Messaging dependency pinned away from the broken 16.4.2 release.

### Compatibility pins

- `google_mobile_ads: 7.0.0`
- `flutter_webrtc: 1.4.0`
- `firebase_core: 4.11.0`
- `firebase_messaging: 16.4.1`

### Known limitation

A real online deployment still requires a public HTTPS Laravel backend, database, Redis/WebSocket services and correct CORS configuration. GitHub Pages serves only Flutter Web static files.
