# Release Checklist

## Source control

- [ ] Release branch created from the latest stable `develop`.
- [ ] No unresolved merge markers.
- [ ] Version and build are consistent in all metadata.
- [ ] Changelog updated.
- [ ] No `.env`, credentials, certificates or keystores committed.

## Quality gates

- [ ] Local source quality gate passes.
- [ ] Backend CI passes.
- [ ] Flutter Web CI passes.
- [ ] Android APK/AAB workflow passes.
- [ ] Production release gate passes.

## Functional smoke test

- [ ] Registration and login.
- [ ] Profile and settings persistence.
- [ ] Create/join room.
- [ ] Complete at least one supported game.
- [ ] Chat and voice status.
- [ ] Store purchase and inventory.
- [ ] Challenge progress and reward.
- [ ] Admin token grant audit.

## Deployment

- [ ] Database backup completed.
- [ ] Migration reviewed for rollback impact.
- [ ] Staging deployed and tested.
- [ ] Production variables and secrets verified.
- [ ] Health endpoint passes.
- [ ] Crash monitoring reviewed after rollout.
