<?php
/**
 * Front Page — FAW Theme
 * Full homepage: hero carousel, roster coverflow, events, gallery,
 * merch, instagram, talent, sponsors, news, contact
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;
$roster = faw_get_roster();
$img_uri = FAW_URI . '/assets/img/';
get_header();
?>

<!-- ============ HERO (full-screen) ============ -->
<section class="hero" id="home">
    <div class="hero__slides" id="heroSlides">
        <article class="slide slide--active" style="--accent:#8b0a1e">
            <div class="slide__bg slide__bg-a"></div>
            <div class="slide__photo-frame">
                <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" target="_blank" rel="noopener" class="slide__photo-link">
                    <div class="photo-slot photo-hero-1 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'voodoo-hero.jpg' ); ?>');background-size:cover;background-position:center;"></div>
                </a>
            </div>
            <div class="slide__overlay"></div>
            <div class="slide__content">
                <span class="slide__tag">▸ MAIN EVENT</span>
                <h1 class="slide__title">VOODOO<br><span class="slide__title-accent">NIGHTS</span></h1>
                <p class="slide__copy">The FAW Halloween Showdown. The frontier turns dark this October — <strong>get your tickets before they vanish.</strong></p>
                <div class="slide__meta">
                    <div class="slide__meta-item"><span class="slide__meta-k">EVENT</span><span class="slide__meta-v">Halloween Showdown</span></div>
                    <div class="slide__meta-item"><span class="slide__meta-k">VENUE</span><span class="slide__meta-v">Covington Country Club</span></div>
                </div>
                <div class="slide__cta">
                    <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" class="btn btn--primary" target="_blank" rel="noopener">Get Tickets →</a>
                    <a href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>" class="btn btn--ghost">▶ See the Action</a>
                </div>
            </div>
        </article>

        <article class="slide" style="--accent:#f5c542">
            <div class="slide__bg slide__bg-b"></div>
            <div class="slide__photo-frame">
                <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" target="_blank" rel="noopener" class="slide__photo-link">
                    <div class="photo-slot photo-hero-2 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'double-k.webp' ); ?>');background-size:cover;background-position:center top;"></div>
                </a>
            </div>
            <div class="slide__overlay"></div>
            <div class="slide__content">
                <span class="slide__tag">▸ THE CHAMPION</span>
                <h1 class="slide__title">DOUBLE<br><span class="slide__title-accent">K</span></h1>
                <p class="slide__copy">The FAW Heavyweight Champion. A powerhouse competitor who claimed the gold at the Crucible tournament.</p>
                <div class="slide__cta">
                    <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" class="btn btn--primary" target="_blank" rel="noopener">Get Tickets →</a>
                    <a href="<?php echo esc_url( home_url( '/#roster' ) ); ?>" class="btn btn--ghost">View Full Roster</a>
                </div>
            </div>
        </article>

        <article class="slide" style="--accent:#dc2626">
            <div class="slide__bg slide__bg-c"></div>
            <div class="slide__photo-frame">
                <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" target="_blank" rel="noopener" class="slide__photo-link">
                    <div class="photo-slot photo-hero-3 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'big-kon.webp' ); ?>');background-size:cover;background-position:center top;"></div>
                </a>
            </div>
            <div class="slide__overlay"></div>
            <div class="slide__content">
                <span class="slide__tag">▸ THE CHALLENGER</span>
                <h1 class="slide__title">BIG<br><span class="slide__title-accent">KON</span></h1>
                <p class="slide__copy">The challenger steps up. <strong>Big Kon</strong> looks to dethrone Double K and take the FAW Heavyweight Championship at Voodoo Nights.</p>
                <div class="slide__cta">
                    <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" class="btn btn--primary" target="_blank" rel="noopener">Get Tickets →</a>
                    <a href="<?php echo esc_url( home_url( '/#roster' ) ); ?>" class="btn btn--ghost">View Full Roster</a>
                </div>
            </div>
        </article>
    </div>

    <button class="hero__arrow hero__arrow--prev" id="heroPrev" aria-label="Previous slide">‹</button>
    <button class="hero__arrow hero__arrow--next" id="heroNext" aria-label="Next slide">›</button>

    <div class="hero__ui">
        <div class="hero__dots" id="heroDots"></div>
        <div class="hero__progress"><div class="hero__progress-bar" id="heroProgress"></div></div>
    </div>
</section>

<!-- ============ TICKER STRIP ============ -->
<div class="ticker-strip" aria-hidden="true">
    <div class="ticker-strip__track">
        <span class="ticker-strip__item">▸ LIVE FROM COVINGTON, LOUISIANA</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">VOODOO NIGHTS — HALLOWEEN SHOWDOWN — TICKETS ON SALE NOW</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">TICKETS ON SALE NOW</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">NEW MERCH DROPS AT EVERY SHOW</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">▸ LIVE FROM COVINGTON, LOUISIANA</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">VOODOO NIGHTS — HALLOWEEN SHOWDOWN — TICKETS ON SALE NOW</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">TICKETS ON SALE NOW</span><span class="ticker-strip__sep">/</span>
        <span class="ticker-strip__item">NEW MERCH DROPS AT EVERY SHOW</span><span class="ticker-strip__sep">/</span>
    </div>
</div>

<!-- ============ ROSTER COVERFLOW ============ -->
<section class="coverflow-section" id="roster">
    <div class="section-head">
        <span class="kicker">THE ATHLETES</span>
        <h2 class="section-title">THE <span class="hl-white">ROSTER</span></h2>
        <p class="section-sub">Drag, swipe, or use the arrows. Click any athlete for their full profile.</p>
    </div>
    <div class="coverflow" id="coverflow">
        <div class="coverflow__stage" id="coverflowStage"></div>
        <button class="coverflow__arrow coverflow__arrow--prev" id="cfPrev" aria-label="Previous wrestler">‹</button>
        <button class="coverflow__arrow coverflow__arrow--next" id="cfNext" aria-label="Next wrestler">›</button>
        <div class="coverflow__counter" id="cfCounter">04 / <?php echo count( $roster ); ?></div>
    </div>
    <div class="coverflow__filters" id="cfFilters">
        <button class="chip is-active" data-filter="all">All Roster</button>
        <button class="chip" data-filter="champion">Heavyweight Champion</button>
        <button class="chip" data-filter="tag">Tag Teams</button>
    </div>
    <div class="coverflow__join">
        <a href="<?php echo esc_url( home_url( '/#talent' ) ); ?>" class="btn btn--primary btn--lg">Join the Roster →</a>
    </div>
</section>

<!-- ============ EVENTS — HORIZONTAL CAROUSEL ============ -->
<section class="h-section" id="events">
    <div class="section-head">
        <span class="kicker">THE SCHEDULE</span>
        <h2 class="section-title">UPCOMING <span class="hl">EVENTS</span></h2>
        <p class="section-sub">Swipe or drag to browse the calendar →</p>
    </div>
    <div class="h-carousel h-carousel--center" id="eventsCarousel">
        <article class="event-card event-card--feature">
            <a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" target="_blank" rel="noopener" class="event-card__photo event-card__photo--poster event-card__photo--link"><div class="photo-slot photo-event-1 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'voodoo-hero.jpg' ); ?>');background-size:contain;background-position:center;background-repeat:no-repeat;background-color:#050103;"></div><span class="event-card__badge">● SELLING FAST</span></a>
            <div class="event-card__body">
                <span class="event-card__date">HALLOWEEN SHOWDOWN</span>
                <h3>Voodoo Nights</h3>
                <p>The FAW Halloween Showdown descends on the Covington Country Club. Don't miss it.</p>
                <div class="event-card__foot"><span class="event-card__price">Tickets Available</span><a href="https://www.eventbrite.com/e/voodoo-nights-faw-wrestling-halloween-showdown-tickets-1998518316082?keep_tld=true" class="btn btn--primary btn--sm" target="_blank" rel="noopener">Tickets →</a></div>
            </div>
        </article>
        <article class="event-card">
            <div class="event-card__photo event-card__photo--poster"><div class="photo-slot photo-event-2 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'rev-hero.jpg' ); ?>');background-size:contain;background-position:center;background-repeat:no-repeat;background-color:#050103;"></div><span class="event-card__badge event-card__badge--replay">▣ PAST EVENT</span></div>
            <div class="event-card__body">
                <span class="event-card__date">AUG 15 · 2026 · PAST EVENT</span>
                <h3>Revolution on the River</h3>
                <p>Double K defended the FAW Heavyweight Championship at the Covington Country Club.</p>
                <div class="event-card__foot"><span class="event-card__price">Archive</span><a href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>" class="btn btn--ghost btn--sm">View Photos →</a></div>
            </div>
        </article>
        <article class="event-card">
            <div class="event-card__photo event-card__photo--poster"><div class="photo-slot photo-event-3 fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'event-crucible.jpg' ); ?>');background-size:contain;background-position:center;background-repeat:no-repeat;background-color:#050103;"></div><span class="event-card__badge event-card__badge--replay">▣ PAST EVENT</span></div>
            <div class="event-card__body">
                <span class="event-card__date">JUN 26 · 2026 · PAST EVENT</span>
                <h3>Crucible</h3>
                <p>The night FAW launched. Phantom vs. Steve O'Malley headlined a sold-out Dungeon Bar on June 26, 2026.</p>
                <div class="event-card__foot"><span class="event-card__price">Archive</span><a href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>" class="btn btn--ghost btn--sm">View Photos →</a></div>
            </div>
        </article>
    </div>
</section>

<!-- ============ PROUD SPONSORS ============ -->
<section class="partners" id="sponsors-wall">
    <div class="section-head">
        <span class="kicker">PROUD SPONSORS</span>
        <h2 class="section-title">BACKED BY THE <span class="hl">BEST</span></h2>
    </div>
    <div class="partners__wall">
        <div class="partner">WA WAGYU</div>
        <div class="partner">JUSTIN "HITMAN" ARD</div>
        <div class="partner">BLUNT WRAPS USA</div>
        <div class="partner">HOT HONEY NICKYS</div>
        <div class="partner">MANDES RESTAURANT</div>
        <div class="partner partner--cta">YOUR BRAND HERE</div>
    </div>
</section>

<!-- ============ PHOTO GALLERY — 2-ROW GRID + LIGHTBOX ============ -->
<section class="h-section" id="gallery">
    <div class="section-head">
        <span class="kicker">FROM THE RING</span>
        <h2 class="section-title">EVENT <span class="hl">GALLERY</span></h2>
        <p class="section-sub">The action, the crowd, the chaos. Click any photo to view full size.</p>
    </div>
    <div class="gallery-grid" id="galleryGrid">
        <?php
        $gallery_imgs = array( 'c005', 'c010', 'c012', 'c015', 'c020', 'c022', 'c025', 'c030', 'c035', 'c040', 'c045', 'c048', 'c050', 'c055', 'c060', 'c065', 'c070', 'c075', 'c080', 'c085', 'c090', 'c095', 'c100' );
        foreach ( $gallery_imgs as $i => $g ) :
        ?>
        <div class="gallery-thumb fade-in-img" data-lightbox="<?php echo esc_attr( $i ); ?>" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/' . $g . '-md.jpg' ); ?>');background-size:cover;background-position:center;"></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ LIGHTBOX ============ -->
<div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lightbox__close" id="lightboxClose" aria-label="Close">×</button>
    <button class="lightbox__arrow lightbox__arrow--prev" id="lightboxPrev" aria-label="Previous">‹</button>
    <button class="lightbox__arrow lightbox__arrow--next" id="lightboxNext" aria-label="Next">›</button>
    <div class="lightbox__img" id="lightboxImg"></div>
    <div class="lightbox__counter" id="lightboxCounter"></div>
</div>
<script>
// Build lightbox image array from the gallery data
window.FAW_GALLERY = [
    <?php foreach ( $gallery_imgs as $g ) : ?>
    '<?php echo esc_js( $img_uri . 'gallery/' . $g . '-lg.jpg' ); ?>',
    <?php endforeach; ?>
];
</script>

<!-- ============ MERCHANDISE ============ -->
<section class="merch" id="merch">
    <div class="section-head">
        <span class="kicker">OFFICIAL GEAR</span>
        <h2 class="section-title">FAW <span class="hl">MERCH</span></h2>
        <p class="section-sub">Rep the frontier. New drops land at every show.</p>
    </div>
    <div class="merch__grid">
        <article class="merch-card">
            <div class="merch-card__photo merch-card__photo--soon"><div class="photo-slot photo-merch-1 fade-in-img"><span>COMING SOON</span></div></div>
            <div class="merch-card__body"><span class="merch-card__cat">APPAREL</span><h3>T-Shirts</h3><div class="merch-card__foot"><span class="merch-card__price">Coming Soon</span></div></div>
        </article>
        <article class="merch-card">
            <div class="merch-card__photo merch-card__photo--soon"><div class="photo-slot photo-merch-2 fade-in-img"><span>COMING SOON</span></div></div>
            <div class="merch-card__body"><span class="merch-card__cat">APPAREL</span><h3>Hoodies</h3><div class="merch-card__foot"><span class="merch-card__price">Coming Soon</span></div></div>
        </article>
        <article class="merch-card">
            <div class="merch-card__photo merch-card__photo--soon"><div class="photo-slot photo-merch-3 fade-in-img"><span>COMING SOON</span></div></div>
            <div class="merch-card__body"><span class="merch-card__cat">COLLECTIBLE</span><h3>Posters</h3><div class="merch-card__foot"><span class="merch-card__price">Coming Soon</span></div></div>
        </article>
        <article class="merch-card">
            <div class="merch-card__photo merch-card__photo--soon"><div class="photo-slot photo-merch-4 fade-in-img"><span>COMING SOON</span></div></div>
            <div class="merch-card__body"><span class="merch-card__cat">APPAREL</span><h3>Headwear</h3><div class="merch-card__foot"><span class="merch-card__price">Coming Soon</span></div></div>
        </article>
    </div>
</section>

<!-- ============ INSTAGRAM FEED ============ -->
<section class="instagram" id="instagram">
    <div class="section-head">
        <span class="kicker">▸ @FRONTIERALLSTARWRESTLING</span>
        <h2 class="section-title">FROM THE <span class="hl">GRAM</span></h2>
        <p class="section-sub">Latest from our Instagram. Tap any post to view on the app.</p>
    </div>
    <div class="instagram__grid">
        <a href="https://www.instagram.com/p/DaoNy_OAevQ/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c020-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> Body slams. High flyers. A packed house losing its mind.</span>
        </a>
        <a href="https://www.instagram.com/p/DYZprLNEY4I/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c030-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> Double K continues to reign as champion.</span>
        </a>
        <a href="https://www.instagram.com/p/DN8RDxBjvfB/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c040-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> Phantom makes his intentions VERY clear.</span>
        </a>
        <a href="https://www.instagram.com/p/DZptR_jISv8/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c055-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> Get ready to rumble at FAW's Crucible.</span>
        </a>
        <a href="https://www.instagram.com/p/DVof8uZD2yi/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c070-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> "The Warmaster" makes his FAW debut.</span>
        </a>
        <a href="https://www.instagram.com/reel/DTSjFwfDUU_/" target="_blank" rel="noopener" class="ig-card fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c085-md.jpg' ); ?>');background-size:cover;background-position:center;">
            <span class="ig-card__overlay"><span class="ig-card__icon">▸</span> Phantom vs. Steve O'Malley at Crucible.</span>
        </a>
    </div>
    <div class="instagram__cta">
        <a href="https://www.instagram.com/frontierallstarwrestling/" class="btn btn--ghost btn--lg" target="_blank" rel="noopener">Follow @frontierallstarwrestling →</a>
    </div>
</section>

<!-- ============ TALENT SECTION (full-bleed) ============ -->
<section class="talent" id="talent">
    <div class="talent__inner">
        <div class="talent__visual">
            <div class="photo-slot photo-talent fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c065-lg.jpg' ); ?>');background-size:cover;background-position:center;"></div>
        </div>
        <div class="talent__content">
            <span class="kicker">▸ JOIN THE ROSTER</span>
            <h2 class="section-title">WRESTLERS,<br>STEP INTO <span class="hl">THE RING.</span></h2>
            <p class="talent__copy">Are you a wrestler, referee, manager, or ring announcer looking for a real stage? Frontier All-Star Wrestling is always scouting new talent. We offer fair pay, a safe environment, and a genuine push for athletes who deliver.</p>
            <ul class="talent__perks">
                <li>Open tryouts &amp; evaluation bookings</li>
                <li>Match opportunities for vetted athletes</li>
                <li>Exposure via Instagram &amp; socials</li>
                <li>Fair pay · Safe environment · Real push</li>
            </ul>
            <form class="form talent__form" id="talentForm">
                <div class="form__row"><input type="text" name="name" placeholder="Ring Name / Real Name" required /><input type="number" name="experience" placeholder="Years Experience" min="0" /></div>
                <div class="form__row"><input type="email" name="email" placeholder="Email" required /><input type="tel" name="phone" placeholder="Phone (optional)" /></div>
                <div class="form__row">
                    <select name="role" required><option value="">Select Role…</option><option>Wrestler</option><option>Tag Team</option><option>Referee</option><option>Manager</option><option>Ring Announcer</option><option>Other</option></select>
                    <input type="text" name="socials" placeholder="Instagram / Social Handle" />
                </div>
                <textarea name="message" rows="3" placeholder="Tell us about your style, trainers, links to footage…"></textarea>
                <button type="submit" class="btn btn--primary btn--block">Submit Application →</button>
                <p class="form__msg" id="talentMsg"></p>
            </form>
        </div>
    </div>
</section>

<!-- ============ SPONSORS SECTION (full-bleed) ============ -->
<section class="sponsors-cta" id="sponsors">
    <div class="sponsors-cta__inner">
        <div class="sponsors-cta__content">
            <span class="kicker">▸ PARTNER WITH FAW</span>
            <h2 class="section-title">PUT YOUR BRAND<br>IN <span class="hl">THE SPOTLIGHT.</span></h2>
            <p class="sponsors-cta__copy">Get in front of a sold-out, passionate Northshore audience and a rapidly growing digital fanbase. FAW builds custom partnership packages that deliver real visibility — in the ring, on the screen, and across our social channels.</p>
            <ul class="sponsors-cta__perks">
                <li>Ring mat, turnbuckle &amp; banner placement</li>
                <li>On-screen logos in all FAW content</li>
                <li>Social shout-outs to a growing fanbase</li>
                <li>Custom activation packages per show</li>
            </ul>
        </div>
        <div class="sponsors-cta__form-wrap">
            <form class="form sponsors-cta__form" id="sponsorForm">
                <h3 class="form__title">Request Sponsor Info</h3>
                <div class="form__row"><input type="text" name="name" placeholder="Name" required /><input type="text" name="company" placeholder="Company / Brand" required /></div>
                <div class="form__row"><input type="email" name="email" placeholder="Email" required /><input type="tel" name="phone" placeholder="Phone (optional)" /></div>
                <textarea name="message" rows="3" placeholder="Tell us about your brand and what you're looking for…"></textarea>
                <button type="submit" class="btn btn--primary btn--block">Send Request →</button>
                <p class="form__msg" id="sponsorMsg"></p>
            </form>
        </div>
    </div>
</section>

<!-- ============ NEWS (full-bleed) ============ -->
<section class="news" id="news">
    <div class="news__chapter full-bleed-section">
        <div class="full-bleed-section__bg photo-slot photo-news fade-in-img" style="background-image:url('<?php echo esc_url( $img_uri . 'gallery/c080-lg.jpg' ); ?>');background-size:cover;background-position:center;"></div>
        <div class="full-bleed-section__overlay"></div>
        <div class="full-bleed-section__content">
            <span class="kicker">▸ LATEST</span>
            <h2 class="section-title">FAW <span class="hl">NEWS</span></h2>
            <p class="full-bleed-section__copy">Double K defends the gold at Voodoo Nights — the FAW Halloween Showdown.</p>
            <a href="https://www.instagram.com/frontierallstarwrestling/" class="btn btn--ghost" target="_blank" rel="noopener">Read More on Instagram →</a>
        </div>
    </div>
</section>

<!-- ============ NEWSLETTER ============ -->
<section class="newsletter" id="newsletter">
    <div class="newsletter__inner">
        <div class="newsletter__copy">
            <span class="kicker kicker--light">STAY IN THE LOOP</span>
            <h2>JOIN THE <span class="hl">FAW INSIDERS</span></h2>
            <p>Presale codes, card announcements, merch drops, and roster news — before anyone else.</p>
        </div>
        <form class="newsletter__form" id="newsletterForm">
            <input type="email" name="email" placeholder="your@email.com" required aria-label="Email" />
            <button type="submit" class="btn btn--primary">Subscribe</button>
        </form>
        <p class="newsletter__note" id="newsletterNote"></p>
    </div>
</section>

<!-- ============ CONTACT ============ -->
<section class="contact" id="contact">
    <div class="contact__inner">
        <div class="contact__info">
            <span class="kicker">▸ GET IN TOUCH</span>
            <h2 class="section-title">CONTACT <span class="hl">FAW</span></h2>
            <div class="contact__details">
                <div class="contact__item"><span class="contact__ico">📍</span><div><strong>Venue</strong><small>Covington Country Club<br>200 Country Club Dr, Covington, LA 70433</small></div></div>
                <div class="contact__item"><span class="contact__ico">✉</span><div><strong>Email</strong><small>info@frontierallstarwrestling.com</small></div></div>
                <div class="contact__item"><span class="contact__ico">🎟</span><div><strong>Tickets</strong><small><a href="https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836" target="_blank" rel="noopener">eventbrite.com/o/frontier-all-star-wrestling</a></small></div></div>
            </div>
        </div>
        <form class="form contact__form" id="contactForm">
            <h3 class="form__title">Send a Message</h3>
            <div class="form__row"><input type="text" name="name" placeholder="Your Name" required /><input type="email" name="email" placeholder="Email" required /></div>
            <textarea name="message" rows="4" placeholder="Your message…"></textarea>
            <button type="submit" class="btn btn--primary btn--block">Send Message →</button>
            <p class="form__msg" id="contactMsg"></p>
        </form>
    </div>
</section>

<?php get_footer();
