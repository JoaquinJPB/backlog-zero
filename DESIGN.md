---
name: Backlog Zero
description: A friends-only game logbook set as an arcade high-score table.
colors:
    cabinet: '#110f18'
    cabinet-raised: '#1a1726'
    cabinet-high: '#242036'
    rule: '#2e2a42'
    rule-strong: '#443e60'
    ink: '#f1edff'
    ink-muted: '#aaa3c6'
    ink-faint: '#857ea2'
    gold: '#ffcf40'
    gold-deep: '#e0ac12'
    gold-bright: '#ffd95f'
    gold-pale: '#ffe7a0'
    cyan: '#45cdf2'
    red: '#ff6464'
    red-pale: '#ffb3b3'
    lilac: '#ab9dff'
typography:
    display:
        fontFamily: 'Anybody, Hanken Grotesk, ui-sans-serif, sans-serif'
        fontSize: '3.4rem'
        fontWeight: 850
        lineHeight: 0.9
        letterSpacing: '-0.005em'
        fontVariation: "'wdth' 125"
        fontFeature: "'tnum' 1"
    headline:
        fontFamily: 'Anybody, Hanken Grotesk, ui-sans-serif, sans-serif'
        fontSize: '2.25rem'
        fontWeight: 850
        lineHeight: 1.1
        letterSpacing: '-0.005em'
        fontVariation: "'wdth' 125"
        fontFeature: "'tnum' 1"
    score:
        fontFamily: 'Anybody, Hanken Grotesk, ui-sans-serif, sans-serif'
        fontSize: '1.875rem'
        fontWeight: 800
        lineHeight: 1
        fontVariation: "'wdth' 112"
        fontFeature: "'tnum' 1"
    label:
        fontFamily: 'Anybody, Hanken Grotesk, ui-sans-serif, sans-serif'
        fontSize: '0.75rem'
        fontWeight: 700
        letterSpacing: '0.08em'
        fontVariation: "'wdth' 125"
    title:
        fontFamily: 'Hanken Grotesk, ui-sans-serif, system-ui, sans-serif'
        fontSize: '1.0625rem'
        fontWeight: 600
        lineHeight: 1.375
    body:
        fontFamily: 'Hanken Grotesk, ui-sans-serif, system-ui, sans-serif'
        fontSize: '1rem'
        fontWeight: 400
        lineHeight: 1.5
    ui:
        fontFamily: 'Hanken Grotesk, ui-sans-serif, system-ui, sans-serif'
        fontSize: '0.9375rem'
        fontWeight: 600
        lineHeight: 1.4
    caption:
        fontFamily: 'Hanken Grotesk, ui-sans-serif, system-ui, sans-serif'
        fontSize: '0.875rem'
        fontWeight: 400
        lineHeight: 1.43
rounded:
    stamp: '3px'
    sm: '4px'
    cover: '5px'
    md: '6px'
    lg: '8px'
    xl: '12px'
    full: '9999px'
spacing:
    gutter-phone: '16px'
    gutter-tablet: '24px'
    gutter-desktop: '32px'
    row-y: '12px'
    row-y-loose: '14px'
    panel: '20px'
    panel-wide: '24px'
    section: '40px'
components:
    button-primary:
        backgroundColor: '{colors.gold}'
        textColor: '{colors.cabinet}'
        typography: '{typography.ui}'
        rounded: '{rounded.md}'
        padding: '0 20px'
        height: '44px'
    button-primary-hover:
        backgroundColor: '{colors.gold-bright}'
        textColor: '{colors.cabinet}'
    button-secondary:
        backgroundColor: '{colors.cabinet-raised}'
        textColor: '{colors.ink}'
        typography: '{typography.ui}'
        rounded: '{rounded.md}'
        padding: '0 20px'
        height: '44px'
    button-secondary-hover:
        backgroundColor: '{colors.cabinet-high}'
        textColor: '{colors.ink}'
    button-ghost:
        textColor: '{colors.ink-muted}'
        rounded: '{rounded.md}'
        padding: '0 14px'
        height: '36px'
    button-ghost-hover:
        backgroundColor: '{colors.cabinet-high}'
        textColor: '{colors.ink}'
    button-danger:
        textColor: '{colors.red}'
        rounded: '{rounded.md}'
        padding: '0 14px'
        height: '36px'
    field:
        backgroundColor: '{colors.cabinet}'
        textColor: '{colors.ink}'
        typography: '{typography.body}'
        rounded: '{rounded.md}'
        padding: '0 14px'
        height: '44px'
    field-search:
        backgroundColor: '{colors.cabinet-raised}'
        textColor: '{colors.ink}'
        rounded: '{rounded.xl}'
        padding: '0 16px 0 48px'
        height: '56px'
    pill:
        textColor: '{colors.ink-muted}'
        rounded: '{rounded.full}'
        padding: '0 14px'
        height: '36px'
    pill-active:
        backgroundColor: '{colors.ink}'
        textColor: '{colors.cabinet}'
        rounded: '{rounded.full}'
        padding: '0 14px'
        height: '36px'
    status-badge:
        rounded: '{rounded.full}'
        padding: '0 10px'
        height: '24px'
    status-option:
        textColor: '{colors.ink-muted}'
        rounded: '{rounded.lg}'
        padding: '0 14px'
        height: '48px'
    panel:
        backgroundColor: '{colors.cabinet-raised}'
        rounded: '{rounded.xl}'
        padding: '20px'
    abandoned-stamp:
        textColor: '{colors.red}'
        typography: '{typography.label}'
        rounded: '{rounded.stamp}'
        padding: '1px 6px'
    game-row:
        rounded: '{rounded.lg}'
        padding: '14px 12px'
    game-row-hover:
        backgroundColor: '{colors.cabinet-raised}'
    now-playing:
        textColor: '{colors.cyan}'
        rounded: '{rounded.xl}'
        padding: '10px'
    marquee-header:
        backgroundColor: '{colors.cabinet}'
        height: '64px'
    tab-bar:
        backgroundColor: '{colors.cabinet}'
        textColor: '{colors.ink-faint}'
        height: '64px'
---

# Design System: Backlog Zero

## Overview

**Creative North Star: "The Hall of Fame"**

Backlog Zero reads a person's play history as an arcade high-score table. The ground is a dark cabinet; the header is a backlit marquee strip edged by a gold rule; every list is a ranked table of ruled rows, and every rating is set as a big tabular score in the ink of the game's status. The interface is a frame for the covers, which carry the colour: the chrome stays in violet-black neutrals so the box art and four status inks are the only saturated things on screen.

Density is that of a scoreboard, not a dashboard. Rows are compact and separated by hairline rules rather than boxed into cards; one figure or one chart owns each region. Numbers are the heroes: counts, years, ordinals and scores all speak in Anybody's wide, heavy display cut, while every word of UI text stays in the quiet Hanken Grotesk. Motion is operate-mode: short, state-only, never decorative.

The system refuses the category default of a cover grid on blue-grey slate with stat cards. Abandoned games are never greyed into invisibility: they are struck through in red and stamped.

**Key Characteristics:**

- Dark cabinet ground (#110f18) with two raised layers and violet-tinted hairline rules.
- One ink per status: gold finished, cyan playing, red abandoned, lilac backlog.
- Ratings shown as score numerals in the status ink, halves written as fractions (4½).
- Ruled rows, ordinals and big figures instead of cards and stat tiles.
- Anybody wide/heavy for display and every number; Hanken Grotesk for all UI text.
- Operate-mode motion: 150ms colour, 200ms lift, 220ms ease-out-expo entrances.

## Colors

A violet-black cabinet with violet-tinted inks, lit by four status inks that each have exactly one job.

### Primary

- **Marquee Gold** (#ffcf40): finished games, every score of a finished game, the primary button, the current-nav indicator, the header rule (at 55%), focus outlines, caret, selection, form accent and the 100% trophy. Gold is the app's voice.
- **Hot Gold** (#ffd95f): primary button hover only.
- **Deep Gold** (#e0ac12): declared in the theme as the darker step of gold; reserved, currently unused in components.
- **Pale Gold** (#ffe7a0): text of gold-tinted notices (success flash, search notice, settings warning) on a 10% gold wash, and the hint of the checked 100% key.

### Secondary

- **Now-Playing Cyan** (#45cdf2): the "playing" status and nothing else. Status badge, status option, score ink, library tab dot, and the "Jugando ahora" block in the journal.

### Tertiary

- **Tilt Red** (#ff6464): abandoned games (strike-through, stamp, journal figure, checkbox accent), errors, invalid field borders, the danger button.
- **Pale Red** (#ffb3b3): text of the error flash on a 10% red wash.
- **Backlog Lilac** (#ab9dff): the "backlog" status only.

### Neutral

- **Cabinet** (#110f18): page ground, field background, text on gold.
- **Cabinet Raised** (#1a1726): panels (at 70%), row hover, secondary button, search field.
- **Cabinet High** (#242036): hover fill for ghost buttons, cover placeholder, skeletons, avatar fallback, tab press.
- **Rule** (#2e2a42): row separators, section rules, footer rule, empty histogram bars.
- **Strong Rule** (#443e60): field and button borders, inactive pill border, dashed empty-state border, scrollbar thumb.
- **Ink** (#f1edff): primary text, active pill fill.
- **Muted Ink** (#aaa3c6): secondary text, inactive nav, metadata, section titles.
- **Faint Ink** (#857ea2): tertiary text, placeholders, ordinals of abandoned rows, zero-value figures, inactive tab-bar items. 4.98:1 on Cabinet, 4.12:1 on Cabinet High, so keep small faint text off Cabinet High.

### Named Rules

**The One Ink, One Job Rule.** Each status owns exactly one ink, mapped once in `resources/js/lib/status.ts` (text, 12% background, 45% border, solid dot). Never colour a status any other way, and never use a status ink for something that is not that status, except gold, which also carries primary action, focus and the 100% trophy (whatever the game's status).

**The Cyan Is Playing Rule.** Cyan appears only where a game is currently being played. No links, info states, charts or highlights in cyan.

**The No New Accents Rule.** The palette is closed. A new screen uses these tokens; a new hue needs a design decision, not a hex literal.

## Typography

**Display Font:** Anybody (variable, weights 500–900, widths 50–150%), self-hosted from `resources/fonts`, falling back to Hanken Grotesk.
**Body Font:** Hanken Grotesk (variable, weights 400–700), self-hosted, falling back to `ui-sans-serif, system-ui`.
**Label Font:** Anybody at 125% width, uppercase.

**Character:** A wide, heavy arcade marquee face for anything you would see lit up on a cabinet (titles, numbers, labels), against a calm grotesk that does all the reading and form work.

Three utilities carry the display voice; use them instead of setting Anybody by hand:

- `font-marquee`: 125% width, weight 850, -0.005em tracking, tabular numerals. Page titles, the wordmark, avatar initials, cover placeholders.
- `font-score`: 112% width, weight 800, tabular numerals, line-height 1. Every number: scores, figures, counts, years, ordinals, pagination.
- `font-label`: 125% width, weight 700, 0.08em tracking, uppercase. Month headings, section titles, fact labels, the abandoned stamp.

### Hierarchy

- **Display** (marquee 850; 2.6rem phone, 3.75rem sm, 3.4rem lg; line-height 0.9): the journal's "Journal 2026" title with the year in gold, the welcome headline (3rem–4.5rem).
- **Headline** (marquee 850; 2rem–3rem; line-height ~1.1): page titles on library, search, settings, onboarding, profile, game page (the game page drops to 100% width on phones to fit long names).
- **Score** (score 800; sizes sm 1.25rem, md 1.875rem, lg 3rem, xl 3.75–4.5rem; row scores 1.5rem phone, 1.875rem sm; figures 2.25rem → 3.75rem): every rating and figure.
- **Label** (label 700; 0.75rem–1.125rem; 0.08em, uppercase): month headings (1.125rem), section titles (0.875rem, muted), fact labels and the "Jugando ahora" heading (0.75rem).
- **Title** (Hanken 600; 1rem phone, 1.0625rem sm; line-height 1.375): game titles in rows, clamped to 2 lines on phones and 1 from sm.
- **UI** (Hanken 600; 0.9375rem buttons and nav, 0.875rem small buttons, pills and labels; 0.6875rem tab-bar labels).
- **Body** (Hanken 400; 1rem; game summary at leading-relaxed, max 68ch).
- **Caption** (Hanken 400; 0.875rem, Muted Ink): row metadata, hints, figure labels. Footer 0.75rem Faint Ink.

### Named Rules

**The Every Number Is a Score Rule.** Any numeral that is data (rating, count, year, ordinal, page number) is set in `font-score` with tabular figures. Numbers never appear in Hanken.

**The Fraction Rule.** Ratings are stored 1–10 and shown as stars with a fraction glyph for halves: 4½, ½, 5. Averages keep one Spanish decimal (4,3). Use `starsFraction` / `starsDecimal` from `components/score.tsx`.

## Layout

A single centred column on a ruled page.

- **Containers:** `max-w-6xl` (72rem) for pages, `max-w-xl` (36rem) for forms (`AppLayout width="narrow"`); search and welcome centre at 48rem and 42rem.
- **Gutters:** 16px phone, 24px from sm, 32px from lg. Main content starts 24px under the header (40px from md) and ends 40px above the footer.
- **Rows:** ruled list items, 12–14px vertical padding, with a -12px horizontal bleed so the hover fill extends past the text edge. Cover, then title block (flex-1, min-w-0), then the score at the right edge.
- **Journal grid:** single column below lg; from lg a `300px | 1fr` two-column grid with a 56px gap, the left column (title, year pills, Jugando ahora, figures) sticky at 7rem from top. Journal rows use a 4-column grid on phones (ordinal 2.5rem, cover, title, score) and add a 10rem platform column from sm (ordinal 3.5rem, score 4.5rem).
- **Figures:** the journal's three figures sit in one row of three below lg and stack in one column from lg. The profile scoreboard is 2 columns, 3 from sm, 6 from lg. Profile body is `1.15fr | 1fr` from lg.
- **Game page:** cover beside title on phones (128px, 160px sm), then a `240px | 1fr` grid from md and `264px | 1fr` from lg.
- **Navigation shift:** below md the header holds only wordmark and avatar, and navigation moves to a fixed bottom tab bar (4 items, 64px tall plus safe-area inset); the footer reserves that height. From md the header shows inline nav links and a logout icon button, and the tab bar is hidden.
- **Horizontal pill rows** (year pills, library tabs) scroll sideways with a hidden scrollbar on phones and bleed to the screen edge; from sm they wrap.
- **Section rhythm:** 28–40px between sections, 56px before the profile's played-games list; forms use 28px between field groups and 20–24px panel padding.

## Elevation & Depth

Depth is mostly tonal: Cabinet, Cabinet Raised and Cabinet High step up the lightness, and hairline rules divide rather than shadows lifting. Shadows exist in a few deliberate places and are long, soft and dark, as if lit from the marquee; two carry a gold glow. Translucent chrome (header at 88%, tab bar at 92%) uses a medium backdrop blur. The game page has one atmospheric layer: its cover, blown up, blurred (blur-3xl), at 35% opacity and 150% saturation, masked to fade out downward.

### Shadow Vocabulary

- **Marquee glow** (`box-shadow: 0 10px 30px -20px rgb(255 207 64 / 0.35)`): under the sticky header, with its 55% gold bottom border.
- **Gold key** (`box-shadow: inset 0 1px 0 0 rgb(255 255 255 / 0.35), 0 6px 16px -8px rgb(255 207 64 / 0.55)`): the primary button only.
- **Cover drop** (`box-shadow: 0 8px 20px -10px rgb(0 0 0 / 0.8)` plus a 1px `rgb(255 255 255 / 0.08)` ring): every game cover.
- **Hero cover** (`box-shadow: 0 30px 60px -24px rgb(0 0 0 / 0.9)`): the large cover on the game page.
- **Search well** (`box-shadow: 0 12px 30px -18px rgb(0 0 0 / 0.9)`): the big search field.

### Named Rules

**The Rules Not Boxes Rule.** Lists and stats are divided by 1px Rule lines, not wrapped in cards. The only container is the panel (forms) and the Jugando ahora block; the profile explicitly has "no boxes, only rules".

## Shapes

Soft but tight corners, scaled to the object: 3px on the stamp, 4px for focus outlines, 5px on covers (keeping IGDB's 264:374 portrait ratio), 6px on buttons and fields, 8px on rows, status keys and notices, 12px on panels, the search field and empty states, and full pills for tabs, filters, badges and avatars. Borders are 1px everywhere except the abandoned stamp (2px) and the active-nav indicator (a 2px rounded gold bar). Empty states use a dashed Strong Rule border. Icons are one custom stroke set on a 24px grid, 1.75 stroke, round caps and joins (`components/icons.tsx`), sized 16–24px.

## Components

### Buttons

Built with `buttonClass(variant, size)` from `resources/js/lib/ui.ts`; never restyle a button by hand.

- **Shape:** gently rounded (6px), semibold, icon gap 8px. Sizes: md 44px tall, 20px side padding, 15px text; sm 36px tall, 14px padding, 14px text.
- **Primary:** gold fill, cabinet text, gold-key shadow; hover Hot Gold. One per view: the main action (Añadir, Guardar, sign-in).
- **Secondary:** Cabinet Raised fill, Strong Rule border, ink text; hover Cabinet High fill and Faint Ink border. Pagination and profile actions.
- **Ghost:** muted text, no border; hover Cabinet High fill and ink text. Icon buttons (logout) and quiet actions.
- **Danger:** red text; hover 10% red wash. Removing a library entry (after a confirm).
- **States:** 150ms ease-out on colour; press scales to 0.97; disabled at 45% opacity with no pointer events. Focus is the global 2px gold outline at 2px offset.

### Pills (tabs and filters)

`pillClass(active)`: 36px tall, full radius, 14px padding, semibold 14px. Inactive: Strong Rule border, muted text, hover ink. Active: ink fill with cabinet text (the one place the light ink becomes a surface). Counts inside a pill are `font-score` at 12px and 60% opacity; library status tabs prefix an 8px status-ink dot.

### Status Badge

`StatusBadge`: 24px pill with the status's 12% wash, 45% border, ink text and a 6px solid dot. Not shown on abandoned rows, which get the stamp instead.

### Panels

`panelClass`: 12px radius, 1px Rule border, Cabinet Raised at 70%, 20px padding (24px from sm). The only container shape; used for the library form, settings and onboarding.

### Inputs / Fields

`fieldClass` for inputs and the `Select` component for selects (native select with the app's chevron).

- **Style:** 44px tall, 6px radius, Strong Rule border on Cabinet, 14px padding, 16px text (no iOS zoom), Faint Ink placeholder.
- **Hover / Focus:** hover Faint Ink border; focus gold border plus a 3px gold ring at 20%. Colour transitions at 150ms.
- **Error / Read-only:** `aria-invalid` turns the border red, with an `errorClass` message (14px, red, medium) below; read-only text is muted. Labels use `labelClass` (14px semibold ink), hints `hintClass` (14px muted).
- **Search field:** the one larger variant: 56px, 12px radius, Cabinet Raised, 18px text, search icon inset at 16px that turns gold on focus and pulses gold while searching; focus ring 4px at 15%.
- **Username field:** a fused field with a gold `@` prefix in `font-score`, same focus treatment as `fieldClass`.

### Status Picker

`StatusOption` in the library form: four radio keys (2×2 on phones, 4 across from sm), 48px tall, 8px radius. Unselected: Strong Rule border, muted text, dot at 75% scale and 50% opacity. Selected: the status's 45% border and 12% wash, ink text, dot at full scale (200ms ease-out). Press scales to 0.97; keyboard focus shows a 2px gold ring.

### Game Row

`GameRow` (library) and `JournalRow` (journal) are the high-score table. Cover (56×80, or 40×56 in the journal growing to ~46×64 from sm), title in Title type with the year in faint `font-score`, metadata line in Caption with status badge, platform and dates (dates hidden on phones), and the score on the right. Hover: Cabinet Raised fill over 150ms and the cover lifts 2px over 200ms. Journal rows lead with an ordinal (`6º`) in muted `font-score`: the newest finished game of the year carries the highest number; abandoned rows show a dash.

### Game Tile

`GameTile` is the profile's "Juegos jugados" shelf: a grid of 3 columns on phones, 4 from sm and 5 from md (60 games a page fills whole rows at every width), 12–24px column gaps and 28px row gaps. Each tile is the fluid `big` cover, then the title (14px semibold, two lines max, turns gold on hover) and a line with the status badge on the left and the score (`text-xl`) on the right. Hover lifts the cover 4px over 200ms. Abandoned tiles keep the struck title, the grey 55% cover and the stamp in place of the badge and score. A 100% game carries its trophy on the cover's top-right corner, in a 32px Cabinet chip at 85% with a 45% gold ring.

### Score (signature)

`Score` sets a rating as large tabular numerals in the status ink of the game (gold for finished, cyan for playing, and so on), sizes sm–xl. No rating renders an en dash in Faint Ink. It is an image role with a Spanish aria-label ("4,5 estrellas"), so the fraction glyph is never read raw.

### Rating Input and the Tick (signature)

`StarRatingInput`: five gold stars (36–40px) overlaid by ten invisible radio halves, so taps hit a left or right half and arrow keys work. Beside it the chosen score in `font-score` 36px gold remounts on every change and plays `score-tick` (220ms ease-out-expo: from 0.18em lower, 92% scale and 40% opacity to rest). Empty stars sit at 45% opacity. A "Quitar" text button clears the rating.

### The 100% Trophy

"100%" (every achievement) is one drawn trophy (`TrophyIcon`, same stroke set) and the only trophy in the app. In the library form, `CompletedToggle` is a checkbox dressed as a key below the rating, hidden for backlog games: min 56px tall, 8px radius, a 28px trophy, "Completado al 100 %" over a "Todos los logros conseguidos" hint. Off: Strong Rule border, muted text, outlined trophy in Faint Ink. On: 45% gold border, 12% gold wash, ink title, Pale Gold hint, and the trophy fills with gold (200ms fill transition) and plays `trophy-pop` (220ms ease-out-expo: from 70% scale and -14° through 114% to rest), only when toggled, never on load. Press scales to 0.98; keyboard focus shows the 2px gold ring. Where entries are listed (library, profile, journal), a completed game carries an 18px filled gold trophy right after its title (an image role labelled "Completado al 100 %"); the game page shows it as a pill beside the status badge with "100 %" in `font-score`.

### Abandoned Row (signature)

Abandoning never erases a game. The title is struck through in red (2px decoration) and drops to Muted Ink; the cover goes grayscale at 55% opacity; the score slot holds `AbandonedStamp`, a red 2px-bordered `font-label` stamp at 10px, rotated -3°, with a 3px radius.

### Jugando Ahora

The journal's now-playing block: 12px radius, 35% cyan border on a 6% cyan wash, a `font-label` cyan heading with an 8px cyan dot, then compact rows (40×56 cover, title, platform) with a 10% cyan hover. The only place cyan is a surface.

### Figures

Journal figures and the profile scoreboard: a `dl` whose value (`font-score`, 36–60px) sits above its caption, each cell topped by a 1px rule. Ink by meaning: gold for finished and averages, red for abandoned when non-zero, Faint Ink for zero. The average adds a small star display from lg.

### Navigation

- **Marquee header:** sticky, 56px (64px from md), Cabinet at 88% with backdrop blur, 55% gold bottom border and marquee glow, respects the top safe-area inset. Wordmark in `font-marquee`, "BACKLOG" in ink and "ZERO" in gold.
- **Desktop nav (md+):** icon plus label links, 15px semibold. Inactive muted, hover ink; current is ink with a gold icon and a 2px gold bar on the header's bottom edge. Profile sits behind the avatar with the handle; logout is a ghost icon button.
- **Tab bar (below md):** fixed bottom, Cabinet at 92% with blur, Rule top border, four 64px cells with a 24px icon and an 11px label. Inactive Faint Ink; current ink with a gold icon and a 32px gold bar on the top edge; press fills Cabinet High. Respects the bottom safe-area inset.

### Flash and Notices

Flash messages enter with `flash-in` (220ms, from 6px above). Success: 40% gold border, 10% gold wash, Pale Gold text, gold check icon. Error: 40% red border, 10% red wash, Pale Red text, red alert icon, `role="alert"`. 8px radius, 16px × 12px padding.

### Covers

`GameCover`: IGDB images hot-linked at `cover_small` / `cover_big` with a 2× srcset, lazy unless above the fold. Missing covers render a Cabinet High tile with the title's initial in `font-marquee` Faint Ink.

### Motion

- 150ms ease-out: colour, border and background transitions; button and key press (scale 0.97).
- 200ms ease-out: cover lift on row hover, status-dot scale.
- 220ms `cubic-bezier(0.16, 1, 0.3, 1)` (ease-out-expo): `score-tick`, `rise` (6px up, for the finish-date field and the 100% key appearing), `flash-in` (6px down), `trophy-pop` (marking 100%).
- Skeleton rows and the searching icon use the standard pulse.
- **Reduced motion:** every animation and transition collapses to 1ms; the score tick alone survives as a 150ms opacity fade so a change is still acknowledged.

## Do's and Don'ts

### Do:

- **Do** put every user-facing string in `lang/es/ui.php` (or another `lang/es/*.php` file) and read it with `t()` / `tc()`; never hard-code Spanish text in a component.
- **Do** build buttons with `buttonClass`, inputs with `fieldClass`, selects with `Select`, tabs and filters with `pillClass`, containers with `panelClass`, and labels, hints and errors with `labelClass`, `hintClass`, `errorClass`.
- **Do** take status colours from `STATUS_INK` in `resources/js/lib/status.ts` (text, 12% wash, 45% border, dot).
- **Do** set every data numeral in `font-score`, ratings via `Score` / `starsFraction`, and page titles in `font-marquee`.
- **Do** divide lists with 1px Rule lines and give each region one figure or chart.
- **Do** strike and stamp abandoned games (`abandonedTitleClass` + `AbandonedStamp`, grayscale cover at 55%).
- **Do** keep motion at 150–220ms, state-driven, with the ease-out-expo curve for entrances, and check it under `prefers-reduced-motion`.
- **Do** keep the bottom tab bar below md and header nav from md for any new top-level section, and reserve the tab bar's height at the foot of the page.
- **Do** use the custom stroke icons in `components/icons.tsx` (24px grid, 1.75 stroke) and add new ones in the same style.

### Don't:

- **Don't** add new accent colours or hex literals; use the theme tokens in `resources/css/app.css`.
- **Don't** use cyan for anything except "Jugando" / "Jugando ahora".
- **Don't** colour a status with any ink but its own, or use red for anything but abandoned and errors.
- **Don't** wrap lists or stats in cards or stat tiles; this is a ruled table, not a dashboard.
- **Don't** grey out an abandoned game without the strike and the stamp.
- **Don't** write half ratings as decimals in scores (use 3½, not 3.5) or set numbers in Hanken Grotesk.
- **Don't** put small Faint Ink text on Cabinet High (4.12:1).
- **Don't** add decorative or looping motion, or animations longer than 220ms.
- **Don't** load fonts from a CDN; Anybody and Hanken Grotesk are self-hosted woff2 subsets.
