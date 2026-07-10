# Warqna Zone v30 - Rules Implementation Notes

This build tightens the playable engines for Tarneeb, Hand, Pinochle/Banakil, Trix-family, Baloot, Hokm/Kout-family, Domino/Backgammon quick rooms, and keeps unsupported games either mapped to a suitable card engine or hidden from final public lists.

Implemented in server/index.js:
- Tarneeb: 4 seats only, bidding starts after dealer to the right, pass once removes bidder from current auction, 7-13 bids, target 31/41/61, trump choice, follow-suit enforcement, trump beats lead, trick animation delay.
- Hand: draw deck/discard pile, selectable discard index pulls all cards above it, custom melds of 3+ cards, first meld >= 51, hand-complete validation, two-row hand UI.
- Pinochle/Banakil: 2 or 4 seats, 2s and jokers as wilds, meld/run validation, 19-card first discard, draw/meld/discard loop, finish and scoring hooks, two-row hand UI.
- Trix-family: follow suit, penalty cards counted.
- Baloot/Hokm/Kout-family: follow suit, trump/no-trump ranking hooks, 4-player seating.
- XP/tokens/shop: max level 100, default yellow name color, 30 table skins, boosters and purchases active immediately.

Important: this is an original implementation inspired by common regional card-game mechanics. It does not copy Jawaker assets, branding, images, private code, or proprietary UI.
