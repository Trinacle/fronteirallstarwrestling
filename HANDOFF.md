# FAW WordPress Theme — Development Handoff

## Project Overview
**Client:** Frontier All-Star Wrestling (frontierallstarwrestling.com)
**Type:** WordPress child theme (child of Astra)
**Current Version:** 2.11.2 (LIVE on server, repo in sync)
**Location on server:** `~/public_html/website_7ddacff9/wp-content/themes/faw/`
**Local repo:** `C:\Users\kevin\ZCodeProject\faw-repo\`
**Last updated:** 2026-09-10

---

## Server Access

| Detail | Value |
|---|---|
| **SSH Host** | `sh00751.bluehost.com` (IP 50.6.253.248) |
| **SSH User** | `slquxqmy` |
| **SSH Key** | `~/.ssh/faw_deploy` |
| **SSH Alias** | `ssh faw-bluehost` (in `~/.ssh/config`) |
| **cPanel** | `https://sh00751.bluehost.com:2083` |
| **WP path** | `~/public_html/website_7ddacff9/` |
| **Hosting** | Bluehost (LiteSpeed cache) |
| **Fronting** | Cloudflare via DNS A record → `66.235.200.145` (NOT the box IP; `50.6.253.248` is SSH/direct only) |
| **WP-CLI** | `/usr/local/bin/wp` |

### SSH test:
```bash
ssh -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes slquxqmy@sh00751.bluehost.com "whoami"
```

---

## ⚠️ CRITICAL DNS KNOWLEDGE (site outage 2026-09-10)

Someone added `CNAME @ → sparkpostmail.com` in DNS. Site went down with ERR_SSL_VERSION_OR_CIPHER_MISMATCH (SparkPost's CloudFront has no cert for our domain). **Fixed by user restoring `A @ → 66.235.200.145` (TTL 4h, "hosting" label).**

Rules:
- Web traffic A record: `66.235.200.145`. Never point `@` anywhere else.
- `50.6.253.248` is the raw box (SSH/SCP only). Publishing it as the A record breaks vhost routing (302→404).
- SparkPost/email records go on SUBDOMAINS (`em.`, `returnpath.`) + TXT DKIM. NEVER on `@`.
- Verification truth when Cloudflare blocks curl: WP-CLI via SSH, or browser hard-refresh. Direct origin curl gets mod_security 406/Cloudflare challenge — that is pre-existing normal.

---

## GitHub

| Detail | Value |
|---|---|
| **Repo** | `https://github.com/Trinacle/fronteirallstarwrestling.git` |
| **Branch** | `main` (fully in sync as of v2.11.2) |
| **Local clone** | `C:\Users\kevin\ZCodeProject\faw-repo\` |

**Git credential gotcha (fixed twice):** Windows Credential Manager caches a repo-pinned credential for the `kevin-treman` account (no Trinacle access) → push 403 or hang. Fix:
```powershell
cmdkey /delete:LegacyGeneric:target=git:https://github.com/Trinacle/fronteirallstarwrestling.git
git push origin main   # completes interactive login on YOUR screen
```
If push hangs silently in a tool context, it is waiting on that invisible login — run it from the user's terminal.

**Auto-deploy (GitHub Actions)** still NOT active. Needs 3 repo secrets: `FAW_SSH_HOST=sh00751.bluehost.com`, `FAW_SSH_USER=slquxqmy`, `FAW_SSH_KEY` = contents of `~/.ssh/faw_deploy`.

---

## Manual Deploy (current method)

```bash
cd C:\Users\kevin\ZCodeProject\faw-repo
# 1. bump FAW_VERSION in faw/functions.php (cache-bust!)
git add -A && git commit -m "vX.Y.Z — desc" && git push
# 2. ship to server
tar czf /tmp/faw-theme.tar.gz --exclude='.git' --exclude='.github' -C faw .
scp -q -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes -o ServerAliveInterval=15 /tmp/faw-theme.tar.gz slquxqmy@sh00751.bluehost.com:/tmp/
ssh -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes slquxqmy@sh00751.bluehost.com "cd ~/public_html/website_7ddacff9/wp-content/themes/faw && tar xzf /tmp/faw-theme.tar.gz && rm /tmp/faw-theme.tar.gz && find . -type f -exec chmod 644 {} \; && cd ~/public_html/website_7ddacff9 && wp cache flush"
```
- ALWAYS bump `FAW_VERSION` first. ALWAYS `wp cache flush` after.
- scp can drop on big uploads ("Connection closed") — retry with `-o ServerAliveInterval=15`.
- Theme tarball is ~23MB (photos live in uploads, NOT the theme — see below).

---

## Theme Structure

```
faw/
├── style.css              ← header (Template: astra)
├── functions.php          ← setup, enqueue, AJAX forms→CPTs, roster data, Meta Pixel
├── header.php             ← dyn bg, center-split nav, mobile tickets btn
├── footer.php             ← American-flag CTA band, giant wordmark, SVG socials
├── front-page.php         ← homepage (all sections)
├── page-gallery.php       ← /gallery/ page (auto-applies via page slug 'gallery', WP page ID 209)
├── inc/gallery-data.php   ← GENERATED manifest: 103 crucible + 730 rivor photo basenames
├── assets/
│   ├── css/styles.css     ← design system
│   ├── js/main.js         ← hero carousel, coverflow, lightbox (+ FAW_LIGHTBOX API), AJAX forms
│   ├── js/gallery-page.js ← /gallery/ filters, sort, lazy grid, load-more (enqueued only on that page)
│   └── img/               ← wrestlers (webp 819px), rivor/ (30 curated lg+md), gallery/ (10 crucible),
│                            voodoo-hero.jpg, event-crucible.jpg, rev-hero.jpg, wrestler-bg.webp, logo.*
└── .github/workflows/deploy.yml
```

**Photo library (NOT in theme, NOT in git):** `wp-content/uploads/faw-gallery/{crucible,rivor}/` — 1,666 files (full 1200/1080px + 480px thumbs), ~190MB. Source pipeline in `C:\Users\kevin\ZCodeProject\faw-photos\`. Theme deploys never touch it (tar extracts over theme dir only, no --delete).

---

## Homepage Sections (top to bottom)

1. **Hero carousel** — 6 slides: Voodoo Nights (purple/orange bg) → Double K (champ) → Prince Agballah & Gen. Oba Zo → Jake Logan → Josh Woods → Da Russell Twins. Autoplay+dots+swipe; photos → Eventbrite.
2. **Ticker** — Voodoo Nights copy; pauses on hover
3. **Roster coverflow** — 21 wrestlers, starts Double K (index 3). Filters: All/Heavyweight Champion/Tag Teams. Red bg cards + gold shimmer badge. Join the Roster btn.
4. **Events** — Voodoo Nights (SELLING FAST, poster→Eventbrite) | Revolution (PAST, → /gallery/?event=rivor) | Crucible (PAST, → /gallery/?event=crucible)
5. **Sponsors wall** — Wa Wagyu, Justin "Hitman" Ard, Blunt Wraps USA, Hot Honey Nickys, Mandes Restaurant + Your Brand Here
6. **Gallery teaser** — 30 Rivor photos, lightbox, **View Full Gallery → /gallery/**
7. **Merch** — 4 Coming Soon cards
8. **Instagram** — 6 cards → real IG posts
9. **Talent** — form → faw_application CPT
10. **Sponsors CTA** — form → faw_inquiry CPT
11. **News** — full-bleed Voodoo Nights promo
12. **Newsletter** — AJAX
13. **Contact** — form → faw_inquiry CPT + emails info@frontierallstarwrestling.com
14. **Footer** — flag CTA band, giant FAW wordmark, socials

---

## /gallery/ Page (v2.10+)

- **833 photos**: 103 Crucible (Jun 26 2026) + 730 Revolution (Aug 15 2026), in `uploads/faw-gallery/`
- Filters: **Event** (All/Crucible/Revolution), **Date** (All/Jun 26/Aug 15), **Sort** (Newest First default / Oldest First)
- 48/page, **Load More**, live counter ("Showing 48 of 833")
- Deep links: `/gallery/?event=rivor` or `?event=crucible` pre-filter
- Thumbs 480px lazy (`loading="lazy"`), lightbox uses fulls
- Lightbox: arrows/keyboard/swipe + **Download button** (counter + ↓ Download side by side, centered under photo; saves as `faw-<basename>`)
- `main.js` exposes `window.FAW_LIGHTBOX = {open, close}`; gallery-page.js uses it via event delegation
- WP page: slug `gallery`, ID 209; `page-gallery.php` auto-applies via slug hierarchy
- Regenerating manifest: `python C:\Users\kevin\ZCodeProject\faw-photos\build_site_gallery.py` (writes inc/gallery-data.php)

---

## Current Event

**Voodoo Nights — FAW Halloween Showdown — Oct 17, 2026** @ Covington Country Club
**Eventbrite (ALL site ticket links):** https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true
Past: Revolution on the River (Aug 15), Crucible (Jun 26, debut).

---

## Design System

**Fonts:** Archivo Black (display) · Oswald (nav/labels/buttons) · Inter (body)

```css
--crimson:#8b0a1e; --crimson-bright:#c81030; --crimson-deep:#5e0612;
--blood:#3a040c; --wine:#260208; --void:#020103; --ink:#040104;
--panel:#0a0306; --panel-2:#100509;
--gold:#f5c542; --amber:#ffb020;
--text:#fff; --text-dim:#c8c8cc; --text-faint:#7a7a80;
.hl{color:#e8163f}  /* text accents; .hl-white for Roster */
/* Voodoo hero: purple #8a2de2 / orange #ff8c00 (.slide__bg-voodoo) */
/* Flag CTA band: navy #0a2472 / red #b8252e / white */
```

---

## Roster (21 — TWO sources, keep in sync!)

1. `functions.php` → `faw_get_roster()` (truth; passed via wp_localize_script)
2. `assets/js/main.js` → `WRESTLERS` fallback

**Order:** Phantom → Mustang Mike → Big Kon → **Double K (champion; cfActive=3)** → Prince Agballah & General Oba Zo (tag) → Jake Logan → Josh Woods → Juice Man → Beautiful Bobby → Grappler III → Purple Haze → Jaxson Strong → Rene Boucher → Izaiah Zane → Cowboy Cliff Rogers → Ashton Blake → Seymore Money → Shawn Crow → Rika & Gluttony (tag) → Da Russell Twins (tag)

**Client rules:** NO role labels / hometown / finishers / height-weight. Only name, initials, photo, bio, champion flag (Double K), color/glow, tags (champion|tag).
**Removed:** Xander Gold, Antonio Bronson, Cody Hawkins, Chris Black, Thaddeus Collins, Suge Whyte.
**Add wrestler:** PNG → 819px webp q82 (~50KB) → BOTH arrays → bump version → deploy.

---

## Meta Pixel (v2.8.0, live+verified)

`functions.php` via `add_action('wp_head', ..., 1)`:
- ID **3055922027946414**; PageView all pages
- Capture listener → **InitiateCheckout** on any eventbrite.com click (Voodoo Nights, ID 1998518316082)
- GA4 (GT-TQTV6XST) coexists. If CMP added later → gate behind marketing consent.
- **Pending:** domain-verification meta tag from client (Meta Business Settings → Brand Safety → Domains) → add to same wp_head block.

---

## Photo Pipeline + Social Drip (Revolution shoot)

Source: Sherri Lynn Photography 5 ZIPs (22.8GB, 818 shots) → streaming pipeline → dhash dedup → **730 winners**.

| Output | Where |
|---|---|
| Gallery page files | uploads/faw-gallery (see above) |
| Homepage teaser 30 | theme assets/img/rivor/ |
| Social drip (separate chat "FAW Wrestling Social") | `C:\Users\kevin\ZCodeProject\faw-photos\social-drip\` — 730×1080px + POSTING-CALENDAR.md (72 posts, 2/day Sep 12→Oct 17) + SOCIAL-HANDOFF.md |
| Scripts | faw-photos\: process_parts.py, finalize.py, make_calendar.py, build_site_gallery.py |

---

## Forms (AJAX → CPTs)

| Form | Action | CPT | Notes |
|---|---|---|---|
| Talent | faw_talent | faw_application | |
| Sponsor | faw_sponsor | faw_inquiry | |
| Contact | faw_contact | faw_inquiry | + email info@frontierallstarwrestling.com |
| Newsletter | faw_newsletter | — | wire to Mailchimp someday |

---

## Gotchas

1. **Cloudflare challenge** blocks curl/REST server-to-server — verify via WP-CLI.
2. **LiteSpeed cache** — flush after every deploy.
3. **Roster in two places** + `cfActive` index must match Double K position.
4. **WP homepage** — show_on_front=page, page_on_front=14; front-page.php auto.
5. **Gallery page photos in uploads** — theme deploys must NOT use --delete; manifest regen only when adding photos.
6. **Yoast foreach warning** in error_log = pre-existing plugin noise.
7. **Em-dash ban** (Kevin's rule): no em-dashes in ANY copy, code comments, commits.
8. **Brand voice answers** (for AI-writer settings) delivered 2026-09-10 — author "The FAW Front Office", tone "bold, cinematic, no fluff, hometown-proud, fight-night loud", unsubscribe title "Tagged out.", booking accent #c81030.

---

## Version History (recent)

| Ver | Changes |
|---|---|
| 2.10.0 | **/gallery/ page**: 833 photos, event/date filters, lazy-load, load-more, lightbox; homepage View Full Gallery + past-event deep links |
| 2.10.1 | Gallery: 48/page, sort Newest First default / Oldest First |
| 2.10.2 | New Voodoo Nights poster (6.8MB → 242KB) |
| 2.11.0 | Lightbox **Download button** (homepage + gallery) |
| 2.11.1/2 | Download button centered under photo, side by side with counter |

---

## External Links

| Resource | URL |
|---|---|
| Live site | https://frontierallstarwrestling.com |
| Gallery page | https://frontierallstarwrestling.com/gallery/ |
| Voodoo Nights Eventbrite | https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true |
| Facebook / Instagram | facebook.com/frontierallstarwrestling · @frontierallstarwrestling |
| GitHub | github.com/Trinacle/fronteirallstarwrestling |
| Social drip handoff | C:\Users\kevin\ZCodeProject\faw-photos\social-drip\SOCIAL-HANDOFF.md |

**WP Application Password:** `aAof DHkH G5LH UKYo RW9b cqSq` (REST blocked by CF; prefer WP-CLI).
