---
version: 1
slug: 'resources-js-components-app-layout-tsx'
primary_target: 'resources/js/components/app-layout.tsx'
related_targets: ['resources/js/pages']
---

# App shell and signed-in surfaces

Scope: every Inertia page (journal, library, game page + library form, search, profile/stats, settings, onboarding) and the shared layout. Welcome gets minimal changes only. Mode: Operate. Equal use on phone and desktop. Code-led (no image generation).

## Direction contract

THESIS: Your play history read as an arcade high-score table: ranked rows, ordinals, scores in big numerals, one ink per status. Refuses the category default of a cover grid on blue-grey slate with stat cards.

OWN-WORLD: Cabinet-ink ground (#110f18) with a raised layer, a marquee header strip edged by a gold rule. Full palette with jobs: gold = finished, score and primary action; cyan reserved only for "Jugando"; red = abandoned and errors; lilac = backlog. Anybody (wide, heavy) for display and every number; Hanken Grotesk for UI text. Halves written as fractions (3½). Abandoned rows are struck through and stamped, never just greyed. Each panel is owned by one figure or chart.

STORY: At a glance you see what you play now, what you finished this year and how you rated it; logging a game takes a few taps on a phone.

FIRST VIEWPORT: Journal on desktop: marquee header with nav; left column "Journal 2026", year pills and three big figures (terminados, abandonados, nota media); right column month tables, rows = ordinal, cover, title + date/days, platform, score. Phone: header + bottom tab bar, figures in one row, rows collapse to cover + title + score.

FORM: Arcade high-score table, #5 of my ordered list (shelf, magazine review page, videoclub, cartridge label, arcade high-score table, year planner, strategy guide). seed:8b7f9c0a7dbc12e4. Signature move: scores set as large tabular numerals in the status ink; the rating input shows the score ticking as you pick half stars; library tabs and status picker use an ink indicator that slides.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
