# Warqna v82 Store & Game Finalization

- Added purchase button directly inside the store preview modal.
- Fixed inventory/M مشترياتي flow: purchased items are appended to inventory and can be activated via Ajax without leaving the tab.
- Activation now returns payload data so table skins, card backs, name/chat colors can update immediately on the current page where possible.
- Added legal-card hints for trick games so illegal cards are visually disabled and show a friendly explanation instead of crashing.
- Improved Tarneeb UX messages around following suit and choosing trump.
- Added safer leave handling: manual leave replaces the user with a bot; after 3 manual exits from the same room, the user is banned from returning to that room.
- Timeout auto-play now bans from the same room after 3 automatic turns.
- Hand melds are restricted to 3–5 cards and UI text reflects that.
- Removed hand scroll behavior and improved card rows: Hand/Banakil/Konkan use two-row layout; other games stay one row.
- Added additional premium emoji packs, realistic table skins, and card backs to the seeder.
- Added richer CSS for premium tables, card backs, profile modal, store preview, and chat emojis.
