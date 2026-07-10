# Warqna v46 Pro - Gameplay Fixes

## What was fixed in this version

- The round now actually deals cards when the room owner presses **توزيع الورق وبدء الجولة**.
- The player's hand is rendered on the table and cards can be clicked to play.
- Bidding buttons now send real actions to Laravel: Bid 7/8/9/10 and Pass.
- Trump / حكم buttons now work: ♣ ♦ ♠ ♥.
- Bot players can auto-bid, choose trump, and play simple cards so local testing does not freeze.
- The table now shows only the number of seats selected when creating the room.
- Room start now requires the selected seats to be full, so the table state is clean.
- The table UI was redesigned with a premium green felt table, deck stack, animated cards, action panel, and responsive seats.
- Store was reorganized into sections: Pasha Days, Tables, Name Colors, Chat Colors, Badges, XP Boosters.
- Error messages are now shown on the page instead of silently failing.
- Game engines were mapped more correctly for Tarneeb, Tarneeb 400, Hokm, Baloot, Kout, Hand, Konkan, and Pinochle.

## Important note

This version implements a functional real gameplay foundation inspired by Arabic card platforms, with original code and original wording. It does not copy any proprietary website assets or copyrighted UI.
