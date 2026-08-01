# FAW WordPress Theme — Development Handoff

## Project Overview
**Client:** Frontier All-Star Wrestling (frontierallstarwrestling.com)
**Type:** WordPress child theme (child of Astra)
**Current Version:** 2.4.1
**Location on server:** `~/public_html/website_7ddacff9/wp-content/themes/faw/`
**Local repo:** `C:\Users\kevin\ZCodeProject\faw-repo\`

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
| **CDN/WAF** | Cloudflare (blocks direct curl/REST from server-to-server) |
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

### GitHub Actions Auto-Deploy
- Workflow file: `faw/.github/workflows/deploy.yml`
- **NOT yet active** — needs 3 GitHub secrets added:
  - `FAW_SSH_HOST` = `sh00751.bluehost.com`
  - `FAW_SSH_USER` = `slquxqmy`
  - `FAW_SSH_KEY` = full contents of `~/.ssh/faw_deploy` private key
- Once secrets added, every `git push origin main` auto-deploys via rsync

---

## Manual Deploy Process (current method)

Since rsync is not installed on Windows, use tar+scp:

```bash
cd C:\Users\kevin\ZCodeProject\faw-repo
git add -A && git commit -m "vX.Y.Z — description" && git push
tar czf /tmp/faw-theme.tar.gz --exclude='.git' --exclude='.github' -C faw .
scp -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes -o StrictHostKeyChecking=no /tmp/faw-theme.tar.gz slquxqmy@sh00751.bluehost.com:/tmp/faw-theme.tar.gz
ssh -i ~/.ssh/faw_deploy -o IdentitiesOnly=yes -o StrictHostKeyChecking=no slquxqmy@sh00751.bluehost.com "cd ~/public_html/website_7ddacff9/wp-content/themes/faw && tar xzf /tmp/faw-theme.tar.gz && rm /tmp/faw-theme.tar.gz && find . -type d -exec chmod 755 {} \; && find . -type f -exec chmod 644 {} \; && cd ~/public_html/website_7ddacff9 && wp cache flush && echo 'DEPLOYED'"
```

### IMPORTANT: Always bump version before deploy
In `faw/functions.php`:
```php
define( 'FAW_VERSION', '2.4.2' ); // increment on EVERY change
```
This cache-busts the CSS/JS so browsers fetch the new version.

---

## Theme Structure

```
faw/
├── style.css              ← Theme header (child of Astra, Template: astra)
├── functions.php          ← Setup, enqueue, AJAX handlers, CPTs, roster data
├── header.php             ← Dynamic bg, center-split nav, mobile tickets button
├── footer.php             ← CTA band, giant wordmark, SVG social icons
├── front-page.php         ← Full homepage (ALL sections)
├── assets/
│   ├── css/styles.css     ← Full design system (~550 lines, dark red palette)
│   ├── js/main.js         ← Hero carousel, coverflow, carousels, lightbox, AJAX forms
│   └── img/               ← Wrestler photos (.webp), gallery, match cards, logo
│       ├── gallery/       ← 46 optimized Crucible event photos (lg + md sizes)
│       ├── logo.webp/png  ← Optimized FAW logo (48KB webp)
│       ├── wrestler-bg.webp ← Red background for coverflow cards
│       ├── rev-hero.jpg   ← Revolution on the River poster
│       ├── event-crucible.jpg ← Crucible poster
│       └── match-1..5.jpg ← Match card images
└── .github/workflows/deploy.yml ← Auto-deploy (needs secrets)
```

---

## Homepage Sections (top to bottom)

1. **Hero carousel** — 3 slides (Revolution on the River, Double K champion, Big Kon challenger). Autoplay + arrows + dots + swipe. Photos clickable to Eventbrite.
2. **Ticker strip** — Scrolling announcements
3. **Match cards** — 6 match card images in a grid, click opens lightbox
4. **Roster coverflow** — 3D carousel of 16 wrestlers, starts at Double K (index 3). Click opens bio modal. Has filters (All / Heavyweight Champion / Tag Teams).
5. **Events** — Horizontal carousel. Revolution on the River (live) + Crucible (past event). Posters clickable.
6. **Sponsors wall** — 5 real sponsors + "Your Brand Here"
7. **Gallery** — 2-row grid of 23 Crucible photos, click opens lightbox with keyboard/touch nav
8. **Merch** — 4 "Coming Soon" cards
9. **Instagram** — 6 clickable photo cards linking to real IG posts
10. **Talent** — Full-bleed section with application form (AJAX → CPT)
11. **Sponsors CTA** — Full-bleed section with inquiry form (AJAX → CPT)
12. **News** — Full-bleed photo with headline
13. **Newsletter** — Email signup (AJAX)
14. **Contact** — Contact form (AJAX → sends to info@fronteirallstarwrestling.com)
15. **Footer** — American flag themed CTA band, giant "FAW" wordmark, social icons, links

---

## Design System

### Colors (CSS custom properties in styles.css)
```css
--void: #020103;        /* near-black base */
--ink: #040104;
--panel: #0a0306;
--crimson: #8b0a1e;     /* dark red primary */
--crimson-bright: #c81030;
--crimson-deep: #5e0612;
--gold: #f5c542;        /* champion accent */
--amber: #ffb020;
--text: #ffffff;        /* pure white text */
--text-dim: #c8c8cc;
```

### Fonts
- **Archivo Black** — display headings
- **Oswald** — nav, labels, buttons
- **Inter** — body text

### Key CSS patterns
- `.hl` = color `#8b0a1e` (was `#ff2e4c`, changed per client request)
- `.hl-white` = pure white (used in "THE ROSTER" title)
- `.fade-in-img` = images fade in on load (JS adds `.is-loaded`)
- `.btn--primary` = red gradient with pulse animation on nav tickets button

---

## Roster Data (IMPORTANT)

Roster is defined in **TWO places** — keep them in sync:
1. `functions.php` → `faw_get_roster()` (PHP, passed to JS via `wp_localize_script`)
2. `assets/js/main.js` → `WRESTLERS` array (JS fallback if PHP data fails)

### Current roster (16 wrestlers, in order):
1. Phantom
2. Mustang Mike
3. Big Kon
4. **Double K** (champion — coverflow starts here, index 3)
5. Juice Man
6. Beautiful Bobby
7. Grappler III
8. Purple Haze
9. Jaxson Strong
10. Rene Boucher
11. Izaiah Zane
12. Cowboy Cliff Rogers
13. Ashton Blake
14. Seymore Money
15. Shawn Crow
16. Rika & Gluttony (tag team)

### Removed wrestlers (NOT on roster):
Xander Gold, Antonio Bronson, Cody Hawkins, Chris Black, Thaddeus Collins, Suge Whyte

### Data rules (per client):
- ❌ NO role labels ("Brawler", "High Flyer", etc.)
- ❌ NO hometown / "where from"
- ❌ NO finishing move names
- ❌ NO height/weight stats
- ✅ Only: name, initials, photo, bio, champion tag (Double K only), color/glow

---

## Forms (AJAX → WordPress CPTs)

| Form | AJAX action | CPT | Notes |
|---|---|---|---|
| Talent application | `faw_talent` | `faw_application` | Stores name, email, role, experience, message |
| Sponsor inquiry | `faw_sponsor` | `faw_inquiry` | Stores name, company, email, message |
| Contact | `faw_contact` | `faw_inquiry` | Also sends email to info@fronteirallstarwrestling.com |
| Newsletter | `faw_newsletter` | (AJAX response only) | Wire to Mailchimp later |

CPTs visible in WP Admin sidebar (Talent Applications, Inquiries).

---

## Key Things to Watch

### 1. Cloudflare blocks server-to-server requests
- `curl` and `wp_remote_get()` to the live domain get a 403 "Just a moment..." challenge
- Always test from the server using WP-CLI or check error logs
- To verify changes: hard-refresh browser (Ctrl+Shift+R) or add `?nc=TIMESTAMP`

### 2. LiteSpeed cache
- Always run `wp cache flush` after deploying
- May also need `?litespeed_purgeall=1` on the URL

### 3. Roster data in two places
- If you add/remove/reorder wrestlers, update BOTH `functions.php` and `main.js`
- The `cfActive` start index in main.js must match Double K's position (currently 3)

### 4. Image optimization
- All wrestler photos should be `.webp` at 819px wide, ~50KB
- Use: `python -c "from PIL import Image; ..."` to optimize
- Gallery images use `-lg.jpg` (1200w) for lightbox and `-md.jpg` (800w) for thumbnails

### 5. WordPress homepage setting
- `show_on_front` = `page`, `page_on_front` = `14` (page titled "Frontier All-Star Wrestling")
- `front-page.php` template handles the homepage automatically

---

## Recent Change History

| Version | Changes |
|---|---|
| 1.0.0 | Initial theme build + deploy |
| 1.1.0 | Removed Phantom slide, TBA events, fake sponsors; added real sponsors + Coming Soon merch |
| 1.2.0 | Optimized logo (48KB webp), wired into header/footer |
| 1.2.1 | Bigger header + footer logos |
| 1.3.0 | Centered events carousel, removed THIS IS FRONTIER section, contact form emails info@ |
| 1.4.0 | Revolution hero image + match card section (6 cards) |
| 1.5.0 | Match cards + hero link to Eventbrite, event images use real posters |
| 1.6.0 | Taller event posters, SELLING FAST/PAST EVENT labels, #8b0a1e replaces #ff2e4c |
| 1.6.1 | Hero accent #8b0a1e, reduced hero height |
| 1.7.0 | Removed 6 non-roster wrestlers, stripped all incorrect fields |
| 1.8.0 | Added Big Kon, Purple Haze, Beautiful Bobby, Grappler III, Jaxson Strong; Kris Keith → Double K |
| 1.8.1 | Added Juice Man + Rene Boucher |
| 1.9.0 | All hero photos clickable, Big Kon challenger slide |
| 1.9.1 | "More matches to be announced soon" text |
| 1.9.2 | Moved new wrestlers behind Double K |
| 2.0.0 | Gallery: 2-row grid + lightbox with all 23 photos |
| 2.1.0 | Wrestler bg image on cards, carousel full width + bigger |
| 2.1.1 | Fixed wrestler layer order (wrestler on top, bg behind) |
| 2.2.0 | Double K to spot 4, new Grappler III photo |
| 2.2.1 | Reordered: Juice Man after Double K, Purple Haze after Grappler III |
| 2.3.0 | Clickable posters, match card lightbox, mobile hero/events fixes |
| 2.4.0 | Hero overlay fix, American flag CTA, sponsor form on mobile, mobile optimization |
| 2.4.1 | Tickets button in mobile header |

---

## External Links

| Resource | URL |
|---|---|
| Live site | https://frontierallstarwrestling.com |
| Eventbrite (Revolution on the River) | https://revolution-on-the-river.eventbrite.com |
| Eventbrite (all events) | https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836 |
| Facebook | https://www.facebook.com/frontierallstarwrestling/ |
| Instagram | https://www.instagram.com/frontierallstarwrestling/ |
| GitHub repo | https://github.com/Trinacle/fronteirallstarwrestling.git |
| Original WP site (for reference) | https://frontierallstarwrestling.com/wp-content/uploads/ |

---

## WP Application Password (for REST API if needed)
```
aAof DHkH G5LH UKYo RW9b cqSq
```
Note: Cloudflare blocks direct REST API access from server-to-server. Use WP-CLI via SSH instead.

---

## How to Add a New Wrestler

1. Drop the photo PNG in `faw/assets/img/`
2. Optimize it:
```bash
cd faw/assets/img
python -c "from PIL import Image; im=Image.open('NAME.png'); r=819/im.size[0]; im.resize((819,int(im.size[1]*r)),Image.LANCZOS).save('slug-name.webp','WEBP',quality=82,method=6)"
rm NAME.png
```
3. Add to BOTH roster arrays (functions.php `faw_get_roster()` + main.js `WRESTLERS`):
```php
array( 'name' => 'Wrestler Name', 'initials' => 'WN', 'tags' => array(), 'color' => '#HEXCOLOR', 'glow' => 'rgba(R,G,B,0.28)', 'img' => FAW_URI . '/assets/img/slug-name.webp', 'bio' => 'Short bio.' ),
```
4. Bump `FAW_VERSION`, commit, deploy.
