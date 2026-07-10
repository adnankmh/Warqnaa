# Warqna v50 Pro - Advanced Rules Update

## New/expanded engines
- Leekha: independent penalty-trick engine with hearts and leekha queens penalties.
- Domino: double-six set, 7 tiles per player, highest double starter, left/right matching, draw/pass, blocked game scoring.
- Backgammon: two-player board state, dice roll, moves left, simple legal movement, hit single checker, bear off and win.
- Banakil/Pinochle: 104 cards + jokers, 18-card deal where possible, wild 2s/jokers, draw/discard/meld flow.
- Konkan: separate rummy-style engine built over the Hand engine with its own identity/rules text.

## Improved gameplay UI
- Domino tiles now render with tile labels and board ends.
- Backgammon has roll dice / move prompt / pass controls.
- Hand, Banakil and Konkan expose draw deck/discard controls.
- Action panel now changes by game phase and game type.

## Database and catalog
- Updated ready SQLite database game rules text for Banakil, Leekha, Domino, Backgammon and Konkan.
- Updated GameCatalog rules so future reset/seed keeps the same descriptions.

## Notes
The implementation uses original code and standard public game-rule logic. It is inspired by popular Arab card-table gameplay patterns, but it does not copy any proprietary Jawaker source code, UI assets, or private algorithms.
