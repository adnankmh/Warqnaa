# Known Issues

## Blocking release verification

1. Flutter SDK is required to prove compilation locally. GitHub Actions is the authoritative build environment when Flutter is unavailable on a developer machine.
2. Do not run `flutter pub upgrade --major-versions` on the stabilization branch. Major plugin upgrades require a dedicated migration branch and regression testing.
3. GitHub Pages cannot host Laravel, databases or WebSocket services.

## Deployment prerequisites

- A staging API URL must be configured through the repository variable `WARQNA_API_URL`.
- Production must use HTTPS.
- CORS must allow the exact frontend origin.
- Android production signing secrets are required before Play Store delivery.
- Firebase and AdMob production identifiers must be supplied through repository variables or secrets.

## Operational risks to test before beta

- Reconnection during a live round.
- Duplicate challenge result submission.
- Wallet idempotency on retry or weak networks.
- Voice-room behavior on Android devices from multiple manufacturers.
- Store entitlement expiry across two logged-in devices.
