# Warqna v68 Pro – Gameplay, Chat & Setup Hotfix

## Fixes
- Fixed Composer setup failure caused by audit blocking when `composer.lock` is absent. Setup now sets `audit.block-insecure=false` for local development and uses Composer security-blocking compatibility mode during install.
- Improved room action handling so invalid moves return a friendly JSON message instead of breaking the page.
- Improved Tarneeb waiting state: if the turn is stuck on a bot, the room sync endpoint pushes bot moves and returns the turn to a human when possible.
- Improved action error messages for bidding, trump selection, wrong turn, and illegal cards.

## Chat & profile
- Game chat and friend chat remain inside the same page.
- Chat can be minimized, closed, and restored as a floating icon.
- Friend request and block actions from profile modal can execute without leaving the page.
- Profile modal shows friend request status: sent / accepted / blocked.

## Hand / Banakil UI
- Cards in hand-like games now display in two rows.
- Added clear meld/group slots for Hand, Banakil and Konkan.
- Increased card size and improved rank/suit contrast.

## Table and visual improvements
- Players are positioned more outside the table boundary to avoid covering cards and table center.
- Added luxurious glass and gold table skins.
- Full-size preview support for tables.
- Improved bot avatars and bot seats visually.
