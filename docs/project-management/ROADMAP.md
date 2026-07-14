# Warqna Product Roadmap

## Release policy

- `main`: stable and releasable only.
- `develop`: integration branch for completed, reviewed features.
- `feature/*`: one feature or bounded change per branch.
- `hotfix/*`: urgent production fixes.
- `release/*`: release hardening only; no unrelated features.

## Current baseline

- Product version: `0.2.5`
- Build: `181`
- Goal: establish a reproducible, testable baseline before adding more large features.

## V0.2.5 — Stabilization gate

Status: **In progress**

Acceptance criteria:

- Flutter dependencies resolve with the pinned compatibility set.
- `flutter analyze` passes in GitHub Actions.
- Flutter tests pass.
- Web release builds and deploys to GitHub Pages.
- Android APK and AAB build successfully.
- Laravel migrations and test suite pass.
- Login, profile, wallet, friends and rooms work against a staging backend.
- No real `.env`, credentials or signing keys are committed.

## V0.2.6 — Gameplay reliability

- Server-authoritative card dealing and move validation.
- Absence and reconnection lifecycle.
- Three-round automatic ejection.
- Three voluntary exits prevent re-entry to the same match.
- Bot decision quality and deterministic tests.
- Per-game regression tests for Tarneeb, Baloot, Hand, Basra and Jackaroo.

## V0.2.7 — Challenge journey and rewards

- Fixed game selection per journey.
- 10, 12 and 15-stage journeys.
- Five attempts with server-side enforcement.
- Matchmaking against eligible online players with bot fallback.
- Idempotent result submission and reward claiming.
- Auditable level-up rewards through level 100.

## V0.2.8 — Store, themes and no-code designer

- Unified store taxonomy and previews.
- Theme, font and accessibility controls available globally.
- 3D interaction system for primary store and bidding actions.
- Inventory expiry and entitlement reconciliation.
- Universal designer restricted to the primary Adnan administrator.

## V0.2.9 — Clubs and competitions

- Club roles and delegated moderators.
- Join requests and announcements.
- Scheduled competitions and prize settlement.
- Abuse reporting and moderation audit log.

## V0.3.0 — Public beta

- Staging load tests.
- Crash reporting and analytics.
- Economy balancing and fraud limits.
- Closed beta, then staged public rollout.
