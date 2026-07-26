# FAW WordPress Theme — Deployment Guide

## What's Built

A complete WordPress child theme (child of **Astra**) for Frontier All-Star Wrestling, deployed to:
```
~/public_html/website_7ddacff9/wp-content/themes/faw/
```

## Repository
```
https://github.com/Trinacle/fronteirallstarwrestling.git
```

## To Activate the Auto-Deploy

The GitHub Actions workflow (`.github/workflows/deploy.yml`) needs **4 secrets** added to the repo:
**Settings → Secrets and variables → Actions → New repository secret**

| Secret Name | Value |
|---|---|
| `FAW_SSH_HOST` | Your server hostname (e.g. `box1234.bluehost.com`) |
| `FAW_SSH_USER` | Your cPanel/SSH username |
| `FAW_SSH_KEY` | Your **private SSH key** (full contents, including BEGIN/END lines) |

### How to generate the SSH key (if you don't have one):
```bash
ssh-keygen -t ed25519 -C "faw-deploy" -f ~/.ssh/faw_deploy
# Leave passphrase empty
```
- **Private key** (`~/.ssh/faw_deploy`) → paste full contents into `FAW_SSH_KEY` secret
- **Public key** (`~/.ssh/faw_deploy.pub`) → add to `~/.ssh/authorized_keys` on the server

### To add the public key on Bluehost:
1. cPanel → SSH Access → Manage SSH Keys → Import Key
2. Paste the public key contents
3. Or: SSH in and run: `echo "PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys`

## Once Secrets Are Added

Every `git push origin main` will automatically:
1. rsync the `faw/` folder to the server
2. Fix file permissions (755 dirs, 644 files)
3. Be live within ~90 seconds

## Manual Deployment (if needed)
```bash
# From repo root
rsync -avz --delete --exclude='.git' --exclude='.github' \
  faw/ USER@HOST:~/public_html/website_7ddacff9/wp-content/themes/faw/
```

## Activating in WordPress
1. WP Admin → Appearance → Themes
2. Activate "FAW — Frontier All-Star Wrestling"
3. Settings → Reading → "Your homepage displays" → "A static page" → select your home page
   (or the theme uses `front-page.php` automatically)

## Theme Structure
```
faw/
├── style.css              ← Theme header (child of Astra)
├── functions.php          ← Setup, enqueue, AJAX handlers, CPTs, roster data
├── header.php             ← Dynamic bg, center-split nav
├── footer.php             ← CTA band, giant wordmark, social icons
├── front-page.php         ← Full homepage (all sections)
├── assets/
│   ├── css/styles.css     ← Full design system (dark red, white text)
│   ├── js/main.js         ← Hero carousel, coverflow, carousels, AJAX forms
│   └── img/               ← 15 wrestler photos + 23 Crucible gallery images
└── .github/workflows/deploy.yml  ← Auto-deploy pipeline
```

## Form Submissions
All forms save to WordPress CPTs (visible in WP Admin):
- **Talent Applications** → `faw_application` post type
- **Sponsor/Contact Inquiries** → `faw_inquiry` post type
- **Newsletter** → AJAX response (wire to Mailchimp/email service later)

## Cache Busting
Every time you change CSS/JS, bump `FAW_VERSION` in `functions.php`:
```php
define( 'FAW_VERSION', '1.0.1' );  // increment on each change
```
