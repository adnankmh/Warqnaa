# Warqna v86 Docs Applied Pro

Built on v85 and focused on applying the remaining Word-file requirements logically while preserving the current project structure.

## Main fixes
- Safer room creation with database-column compatibility and clearer create-room errors instead of a generic unexpected message.
- Theme and language selection now persists through a quick preference endpoint and applies instantly on the page.
- Expanded UI translation dictionary for Arabic, English, French, Turkish, German and Spanish for common navigation, room, store, profile and tournament labels.
- Admin GUI extended with site builder controls: announcement, hero subtitle, nav labels JSON, homepage cards JSON, custom CSS and layout controls.
- Profile modal tightened to stay inside the screen, with edit profile button, country flag/name, XP remaining for next level and active cosmetics.
- Store tuning based on uploaded store manifest: six XP boosters, 24-hour activation, 10-day validity, richer emoji tiers and improved inventory tab behavior.
- Tournament logic updated with required player calculations for 1-4 stages and safe room creation.
- Club UI enhanced with league badges and weekly/treasury presentation.
- Game rules translations expanded and rules remain in the dedicated rules page, not in rooms.
- Chat improvements: better emoji categories, colors, sounds, draggable dock, friend/game chat behavior, and reopen icon.
- Additional CSS refinements for game curtain, bots, room cards, hand/meld zones, store cards and admin controls.

## Validation
- PHP syntax checked with `php -l` across app/routes/views/config/database files.
- JavaScript syntax checked with `node --check public/assets/js/app.js`.
- ZIP contains one top-level folder only.
