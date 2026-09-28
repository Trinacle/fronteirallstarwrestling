# FAW WordPress Theme — Development Handoff

## Project Overview
**Client:** Frontier All-Star Wrestling (frontierallstarwrestling.com)
**Type:** WordPress child theme (child of Astra)
**Current Version:** 2.9.1 (LIVE on server)
**Location on server:** `~/public_html/website_7ddacff9/wp-content/themes/faw/`
**Local repo:** `C:\Users\kevin\ZCodeProject\faw-repo\`
**Last updated:** 2026-09-10

---

## Server Access

| Detail | Value |
|---|---|
| **SSH Host** | `sh00751.bluehost.com` |
| **SSH User** | `slquxqmy` |
| **SSH Key** | `~/.ssh/faw_deploy` |
| **SSH Alias** | `ssh faw-bluehost` (configured in `~/.ssh/config`) |
| **cPanel** | `https://sh00751.bluehost.com:2083` |
| **WP path** | `~/public_html/website_7ddacff9/` |
| **Hosting** | Bluehost (LiteSpeed cache, cPanel) |
| **CDN/WAF** | Cloudflare (blocks direct curl/REST server-to-server — verify via WP-CLI, not curl) |
| **WP-CLI** | Available at `/usr/local/bin/wp` |

### SSH test command:
```bash
ssh -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes slquxqmy@sh00751.bluehost.com "whoami"
```

---

## GitHub

| Detail | Value |
|---|---|
| **Repo** | `https://github.com/Trinacle/fronteirallstarwrestling.git` |
| **Branch** | `main` |
| **Local clone** | `C:\Users\kevin\ZCodeProject\faw-repo\` |

### ⚠️ Git credential gotcha (hit 2026-09-10, fixed)
Windows Credential Manager had a repo-pinned credential for the `kevin-treman` account (no Trinacle access) → push 403. Fixed by deleting it. If it recurs:
```powershell
cmdkey /delete:LegacyGeneric:target=git:https://github.com/Trinacle/fronteirallstarwrestling.git
```

### GitHub Actions Auto-Deploy
- Workflow: `faw/.github/workflows/deploy.yml`
- **NOT yet active** — needs 3 secrets in repo Settings → Secrets → Actions:
  - `FAW_SSH_HOST` = `sh00751.bluehost.com`
  - `FAW_SSH_USER` = `slquxqmy`
  - `FAW_SSH_KEY` = full contents of `~/.ssh/faw_deploy` private key
- Once added, every push to main auto-deploys via rsync + cache flush

---

## Manual Deploy Process (current method)

rsync is not on Windows — use tar+scp:

```bash
cd C:\Users\kevin\ZCodeProject\faw-repo
git add -A && git commit -m "vX.Y.Z — description" && git push
tar czf /tmp/faw-theme.tar.gz --exclude='.git' --exclude='.github' -C faw .
scp -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes -o StrictHostKeyChecking=no /tmp/faw-theme.tar.gz slquxqmy@sh00751.bluehost.com:/tmp/faw-theme.tar.gz
ssh -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes -o StrictHostKeyChecking=no slquxqmy@sh00751.bluehost.com "cd ~/public_html/website_7ddacff9/wp-content/themes/faw && tar xzf /tmp/faw-theme.tar.gz && rm /tmp/faw-theme.tar.gz && find . -type d -exec chmod 755 {} \; && find . -type f -exec chmod 644 {} \; && cd ~/public_html/website_7ddacff9 && wp cache flush && echo 'DEPLOYED'"
```

### ALWAYS bump version before deploy
In `faw/functions.php`: `define( 'FAW_VERSION', 'X.Y.Z' );` — cache-busts CSS/JS. Every deploy needs this.

---

## Theme Structure

```
faw/
├── style.css              ← Theme header (Template: astra)
├── functions.php          ← Setup, enqueue, AJAX forms→CPTs, roster data, Meta Pixel
├── header.php             ← Dynamic bg, center-split nav, mobile tickets button
├── footer.php             ← American-flag CTA band, giant wordmark, SVG social icons
├── front-page.php         ← Full homepage (all sections)
├── assets/
│   ├── css/styles.css     ← Design system (dark red #8b0a1e, polished v2.9.0)
│   ├── js/main.js         ← Hero carousel, coverflow, carousels, lightbox, AJAX forms
│   └── img/
│       ├── rivor/         ← 30 Revolution 8.15.26 photos (lg 1200 + md 800) — THE GALLERY
│       ├── gallery/       ← 10 Crucible archive photos (lg + md)
│       ├── wrestlers      ← *.webp 819px (~50KB each)
│       ├── wrestler-bg.webp ← Red card background for coverflow
│       ├── voodoo-hero.jpg / event-crucible.jpg / rev-hero.jpg ← event posters
│       └── logo.webp/png  ← Optimized FAW logo
└── .github/workflows/deploy.yml
```

---

## Current Homepage Sections (top to bottom)

1. **Hero carousel** — 6 slides: **Voodoo Nights (main event, purple/orange theme)** → Double K (champ) → Prince Agballah & Gen. Oba Zo → Jake Logan → Josh Woods → Da Russell Twins. Autoplay+arrows+dots+swipe; photos link to Eventbrite. (Big Kon slide removed by request.)
2. **Ticker strip** — "VOODOO NIGHTS — HALLOWEEN SHOWDOWN — TICKETS ON SALE NOW" (pauses on hover)
3. **Roster coverflow** — 3D carousel, 21 wrestlers, starts at **Double K (index 3)**. Filters: All / Heavyweight Champion / Tag Teams. Red bg cards + gold shimmer champ badge. Join the Roster button.
4. **Events** — 3 cards: **Voodoo Nights (LIVE, SELLING FAST)** → Revolution on the River (PAST) → Crucible (PAST). Poster-style 3:4 photos, clickable to Eventbrite.
5. **Sponsors wall** — Wa Wagyu, Justin "Hitman" Ard, Blunt Wraps USA, Hot Honey Nickys, Mandes Restaurant + "Your Brand Here"
6. **Gallery** — 30 Revolution photos, 5-col grid, click → lightbox (30 Riv + 10 Crucible = 40 images, keyboard/touch nav)
7. **Merch** — 4 "Coming Soon" cards
8. **Instagram** — 6 clickable cards → real IG posts
9. **Talent** — full-bleed, application form (AJAX → faw_application CPT)
10. **Sponsors CTA** — full-bleed, inquiry form (AJAX → faw_inquiry CPT); visible on mobile
11. **News** — full-bleed "Voodoo Nights" promo
12. **Newsletter** — AJAX signup
13. **Contact** — form emails info@frontierallstarwrestling.com + stores as CPT
14. **Footer** — American flag CTA band (navy/red split, white button, striped borders), giant "FAW" wordmark, social icons

---

## Current Event

**Voodoo Nights — FAW Halloween Showdown — Oct 17, 2026** @ Covington Country Club
**Eventbrite (ALL ticket links site-wide):** https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true

Past events: Revolution on the River (Aug 15, 2026), Crucible (Jun 26, 2026 — debut).

---

## Design System

```css
--crimson: #8b0a1e;      /* dark red primary (buttons/bg) */
--crimson-bright: #c81030;
--crimson-deep: #5e0612;
--void: #020103;          /* near-black base */
--gold: #f5c542;          /* champion accent */
--text: #ffffff;          /* pure white */
.hl { color: #e8163f; }   /* text accents — brighter for readability on black */
```
Fonts: Archivo Black (display), Oswald (nav/labels), Inter (body). Voodoo hero slide uses its own purple/orange bg (`.slide__bg-voodoo`).

---

## Roster (21 wrestlers — defined in TWO places, keep in sync!)

1. `functions.php` → `faw_get_roster()` (source of truth, passed to JS via wp_localize_script)
2. `assets/js/main.js` → `WRESTLERS` array (fallback)

**Order:** Phantom → Mustang Mike → Big Kon → **Double K (champion, coverflow starts here, index 3)** → Prince Agballah & General Oba Zo (tag) → Jake Logan → Josh Woods → Juice Man → Beautiful Bobby → Grappler III → Purple Haze → Jaxson Strong → Rene Boucher → Izaiah Zane → Cowboy Cliff Rogers → Ashton Blake → Seymore Money → Shawn Crow → Rika & Gluttony (tag) → Da Russell Twins (tag)

**Data rules (client-mandated):** NO role labels, NO hometown, NO finishing moves, NO height/weight. Only: name, initials, photo, bio, champion flag (Double K only), color/glow, tags (champion/tag only).

**Removed (NOT roster):** Xander Gold, Antonio Bronson, Cody Hawkins, Chris Black, Thaddeus Collins, Suge Whyte.

**Adding a wrestler:** drop PNG in `assets/img/`, optimize to 819px webp (~50KB, PIL quality=82), add matching entries to BOTH arrays, bump version, deploy.

---

## Meta Pixel (installed v2.8.0)

In `functions.php` via `add_action('wp_head', ..., 1)`:
- **Pixel ID 3055922027946414** — PageView on all pages
- Document-level capture listener fires **InitiateCheckout** on ANY `eventbrite.com` link click (content: Voodoo Nights, ID 1998518316082)
- Verified server-side (init/listener/noscript all render in wp_head)
- GA4 (GT-TQTV6XST) runs alongside, untouched
- **Domain verification pending:** client to send the `facebook-domain-verification` meta tag from Meta Business Settings → Brand Safety → Domains; drop it into the same wp_head block when it arrives
- If a CMP is ever added, pixel must be gated behind marketing consent

---

## Photo Pipeline (Revolution on the River shoot)

**Source:** 5 ZIPs from Sherri Lynn Photography (22.8GB, 818 photos, 22–41MB each) in `~/Downloads`.
**Processed:** streaming extract (never exceeded 15GB free disk) → perceptual-hash dedup (dHash, hamming ≤6 pass 1 / ≤8 cluster pass 2) → **730 unique winners**.

| Output | Location | Contents |
|---|---|---|
| Website gallery | `faw/assets/img/rivor/` | 30 curated (evenly spread across event), lg 1200 + md 800 |
| Social drip | `C:\Users\kevin\ZCodeProject\faw-photos\social-drip\` | 730 × 1080px IG-ready + POSTING-CALENDAR.md + SOCIAL-HANDOFF.md |
| Pipeline scripts | `C:\Users\kevin\ZCodeProject\faw-photos\` | process_parts.py, finalize.py, make_calendar.py |

**Social drip (managed by separate "FAW Wrestling Social" chat):** 72 posts, 2/day, Sep 12 → Oct 17, countdown captions from Oct 7. Handoff: `social-drip/SOCIAL-HANDOFF.md`.

---

## Forms (AJAX → WordPress CPTs)

| Form | AJAX action | CPT | Notes |
|---|---|---|---|
| Talent application | `faw_talent` | `faw_application` | name, email, role, experience, message |
| Sponsor inquiry | `faw_sponsor` | `faw_inquiry` | name, company, email, message |
| Contact | `faw_contact` | `faw_inquiry` | ALSO emails info@frontierallstarwrestling.com |
| Newsletter | `faw_newsletter` | — (response only) | wire to Mailchimp eventually |

CPTs visible in WP Admin: "Talent Applications", "Inquiries".

---

## Key Gotchas

1. **Cloudflare** blocks server-to-server HTTP — verify via WP-CLI (`wp eval`) or error logs, never curl to the live domain. Browser verification needs hard refresh (Ctrl+Shift+R).
2. **LiteSpeed cache** — always `wp cache flush` after deploy.
3. **Roster in two places** — functions.php + main.js must stay identical; `cfActive` start index (currently 3) must match Double K's position.
4. **WP homepage** — `show_on_front=page`, `page_on_front=14`; `front-page.php` renders automatically.
5. **Images must be optimized** — wrestler webp 819px ~50KB; gallery lg/md variants. Never ship 30MB originals.
6. **Yoast warning** in error log (class-wpseo-options.php foreach) is pre-existing plugin noise, not ours.

---

## Version History (recent)

| Version | Changes |
|---|---|
| 2.5.1 | Voodoo Nights replaces Revolution as main event; match cards removed; Revolution → past event; all ticket links → new Eventbrite |
| 2.6.0 | Voodoo hero purple/orange theme |
| 2.7.0 | +Prince Agballah & Gen. Oba Zo (tag), Jake Logan, Josh Woods + hero slides |
| 2.7.1 | Big Kon removed from hero rotation |
| 2.8.0 | **Meta Pixel installed** (PageView + InitiateCheckout) |
| 2.8.1 | Removed duplicate Da Russell Twins entry |
| 2.9.0 | Frontend polish: readable .hl accents (#e8163f), kicker lines, nav underlines, champ shimmer, gallery zoom icons, lightbox entrance, focus rings, custom scrollbar, content-visibility perf |
| 2.9.1 | **Gallery replaced with 30 Revolution on the River photos** (from 730-photo dedup pipeline); trimmed unused Crucible thumbs |

---

## External Links

| Resource | URL |
|---|---|
| Live site | https://frontierallstarwrestling.com |
| Voodoo Nights Eventbrite | https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true |
| Eventbrite org (all events) | https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836 |
| Facebook | https://www.facebook.com/frontierallstarwrestling/ |
| Instagram | https://www.instagram.com/frontierallstarwrestling/ |
| GitHub repo | https://github.com/Trinacle/fronteirallstarwrestling.git |
| Social drip handoff | C:\Users\kevin\ZCodeProject\faw-photos\social-drip\SOCIAL-HANDOFF.md |

## WP Application Password (REST API if ever needed)
```
aAof DHkH G5LH UKYo RW9b cqSq
```
(Cloudflare blocks direct REST from servers — prefer WP-CLI via SSH.)
