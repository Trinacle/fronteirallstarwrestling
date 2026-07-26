<?php
/**
 * Footer — FAW Theme
 * Massive footer with CTA band, giant wordmark, social icons
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main><!-- #content -->

<!-- ============ MASSIVE FOOTER ============ -->
<footer class="footer">
    <div class="footer__cta-band">
        <div class="footer__cta-inner">
            <h2 class="footer__cta-title">DON'T MISS<br>THE BELL.</h2>
            <a href="https://revolution-on-the-river.eventbrite.com" class="btn btn--primary btn--lg" target="_blank" rel="noopener">Get Tickets →</a>
        </div>
    </div>

    <div class="footer__giant" aria-hidden="true"><span class="footer__giant-text">FAW</span></div>

    <div class="footer__main">
        <div class="footer__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo logo--footer">
                <span class="logo__shield"><svg viewBox="0 0 60 60" class="logo__svg" aria-hidden="true"><path d="M12 18 L30 44 L48 18" stroke="currentColor" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span class="logo__text"><strong>FRONTIER</strong><small>ALL-STAR WRESTLING</small></span>
            </a>
            <p>Explosive live pro wrestling from Covington, Louisiana — serving the Northshore and beyond.</p>
            <div class="footer__social">
                <a href="https://www.facebook.com/frontierallstarwrestling/" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                </a>
                <a href="https://www.instagram.com/frontierallstarwrestling/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 01-1.38-.9 3.72 3.72 0 01-.9-1.38c-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 3.24a6.6 6.6 0 100 13.2 6.6 6.6 0 000-13.2zm0 10.88a4.28 4.28 0 110-8.56 4.28 4.28 0 010 8.56zm6.85-11.13a1.54 1.54 0 11-3.08 0 1.54 1.54 0 013.08 0z"/></svg>
                </a>
                <a href="https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836" target="_blank" rel="noopener" aria-label="Eventbrite" class="social-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.6 21.4c-4.46 0-8.1-3.64-8.1-8.1 0-4.46 3.64-8.1 8.1-8.1 2.3 0 4.38.97 5.86 2.52l-2.4 1.95a5.05 5.05 0 00-3.46-1.37 5.05 5.05 0 000 10.1c2.3 0 4.27-1.55 4.86-3.67h-4.43v-3.16h7.96c.1.56.16 1.14.16 1.73 0 4.46-3.63 8.1-8.1 8.1zM22 9.3v2.65h-2.05c-.04.15-.1.3-.16.45h2.2V15h-2.9a5.9 5.9 0 01-1.6 1.65h4.5v2.65h-9.3a8.1 8.1 0 003.2-2.65h-1.5a5 5 0 01-3.95 1.9 5.05 5.05 0 010-10.1c1.62 0 3.06.76 4 1.95l1.9-1.55c-.2-.26-.42-.5-.65-.73A8.06 8.06 0 0010.6 5c-4.6 0-8.35 3.74-8.35 8.35 0 4.6 4.6 8.35 8.35 8.35 2.05 0 3.93-.74 5.37-1.97H22V9.3z"/></svg>
                </a>
            </div>
        </div>
        <div class="footer__col">
            <h4>EXPLORE</h4>
            <a href="<?php echo esc_url( home_url( '/#roster' ) ); ?>">Roster</a>
            <a href="<?php echo esc_url( home_url( '/#events' ) ); ?>">Events</a>
            <a href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>">Gallery</a>
            <a href="<?php echo esc_url( home_url( '/#merch' ) ); ?>">Shop</a>
            <a href="<?php echo esc_url( home_url( '/#news' ) ); ?>">News</a>
        </div>
        <div class="footer__col">
            <h4>OPPORTUNITIES</h4>
            <a href="<?php echo esc_url( home_url( '/#talent' ) ); ?>">Wrestlers / Talent</a>
            <a href="<?php echo esc_url( home_url( '/#sponsors' ) ); ?>">Sponsors</a>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact</a>
            <a href="<?php echo esc_url( home_url( '/#newsletter' ) ); ?>">Newsletter</a>
            <a href="https://revolution-on-the-river.eventbrite.com" target="_blank" rel="noopener">Buy Tickets</a>
        </div>
        <div class="footer__col">
            <h4>VENUE</h4>
            <p>Covington Country Club<br>200 Country Club Dr<br>Covington, LA 70433</p>
            <a href="mailto:info@frontierallstarwrestling.com">info@frontierallstarwrestling.com</a>
        </div>
    </div>

    <div class="footer__bottom">
        <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Frontier All-Star Wrestling. All rights reserved.</span>
        <span>Live pro wrestling from Covington, Louisiana.</span>
    </div>
</footer>

<button class="to-top" id="toTop" aria-label="Back to top">↑</button>

<!-- wrestler modal -->
<div class="modal" id="wrestlerModal" aria-hidden="true" role="dialog">
    <div class="modal__backdrop" data-close></div>
    <div class="modal__panel">
        <button class="modal__close" data-close aria-label="Close">×</button>
        <div class="modal__content" id="modalContent"></div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
