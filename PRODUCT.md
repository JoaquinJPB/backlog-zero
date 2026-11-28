# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

A small, closed group of friends who play video games. Each one logs what they are playing, what they finished or dropped, and what is waiting in their backlog, with a rating and dates. They use it equally on phone (logging a game right after finishing it, checking the journal on the couch) and on desktop (browsing the library, looking at stats).

## Product Purpose

Backlog Zero is a personal record of the games you play: a library with status, rating (1–10, entered with five half-stars), platform and dates; a yearly journal of finished games grouped by month; and a profile with stats. Success is that logging a game takes seconds and looking back at a year of play is a pleasure. A social layer between friends (public profiles, then lists) comes next.

## Positioning

Not a public catalogue or a social network: a private, friends-only logbook. Game data comes from IGDB and is stored locally; the value is each person's own history, not reviews or discovery.

## Operating Context

- Sign-in with Google only; sign-up limited to an allow-list of friends' emails.
- Core loop: search a game → open its page → set status, rating, platform, dates → see it in the library, the journal (when finished) and the profile stats.
- Covers are IGDB images hot-linked by `image_id` (portrait, 264×374 at `cover_big`).
- Footer must show "Game data provided by IGDB.com" with a link.

## Capabilities and Constraints

- Statuses: playing, finished, abandoned, backlog. Ratings are integers 1–10, entered as half stars and shown as the whole number out of 10.
- Pages: welcome (guests), game search, game page with the library form, library with status tabs, journal per year with filters, profile with stats (owner-only for now), profile settings, username onboarding.
- All user-facing strings are Spanish and live in `lang/es/*.php`.
- Stack: Laravel 13 + Inertia 3 + React 19 + Tailwind 4. No UI component library.
- The welcome page will be reworked later; for now it gets only the minimal changes needed to belong to the new look.
- Not monetized; deployed on a free Render instance (cold starts), so pages should stay light.

## Brand Commitments

Only the name, **Backlog Zero**. Logo, favicon and visual identity are open. Copy is Spanish, casual and close, written for friends.

## Evidence on Hand

Real data is each user's own library. There are no testimonials, user counts or press, and none should be invented.

## Product Principles

1. Logging is fast: the game page and its form work in a few taps on a phone.
2. The history is the product: finished games, ratings and years deserve to look good.
3. Covers carry the colour: the interface frames the games instead of competing with them.
4. Private by default: nothing implies a public or social feature that does not exist yet.
