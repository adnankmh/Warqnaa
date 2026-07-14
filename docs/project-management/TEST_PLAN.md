# Warqna Test Plan

## 1. Required automated gates

### Flutter

- Resolve pinned dependencies.
- Verify `pubspec.lock` compatibility pins.
- Run release contract tests.
- Run `flutter analyze`.
- Run `flutter test`.
- Build Flutter Web release.
- Build Android APK and AAB.

### Laravel

- Validate Composer metadata.
- Install dependencies from lock data when available.
- Create a fresh SQLite test database.
- Run migrations and seeders.
- Lint every PHP file.
- Run the Laravel test suite.
- Run production preflight.

## 2. Critical manual scenarios

### Authentication

- Register a new player.
- Log in and log out.
- Restore session after browser refresh.
- Reject incorrect credentials without leaking details.

### Gameplay

- Start a room with human players.
- Verify unique cards and correct hand sizes.
- Reject an illegal card move.
- Complete a round and verify score settlement.
- Disconnect and reconnect before ejection.
- Confirm ejection after three missed rounds.

### Economy

- Award tokens once after a valid event.
- Retry the same request and verify no duplicate credit.
- Purchase an item and confirm wallet and inventory atomically.
- Verify timed inventory expiry.

### Challenges

- Select each supported journey length.
- Verify game type remains fixed.
- Win advances exactly one stage.
- Loss removes exactly one attempt.
- Reward claim is idempotent.

### Localization and accessibility

- Arabic RTL layout.
- English and every enabled non-Arabic language LTR layout.
- No visible untranslated keys.
- Font scaling does not clip controls.
- Portrait and landscape layouts do not cover cards or chat.

## 3. Release evidence

For each release retain:

- Git commit SHA.
- Successful GitHub Actions URLs.
- APK/AAB SHA-256 values.
- Backend migration result.
- Manual smoke-test record.
- Known issues accepted for release.
