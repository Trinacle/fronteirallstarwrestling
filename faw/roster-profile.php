<?php
/**
 * Wrestler profile - /roster/{slug}/
 * Photo, bio, all photos the wrestler is tagged in, match cards.
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$slug = get_query_var( 'faw_wrestler' );
$w    = faw_get_wrestler_by_slug( $slug );

if ( ! $w ) {
    wp_safe_redirect( home_url( '/#roster' ), 302 );
    exit;
}

require_once FAW_DIR . '/inc/gallery-data.php';

$all_tags = faw_all_photo_tags();
$events   = faw_gallery_events();

$by_event = array();
foreach ( $events as $key => $ev ) {
    $by_event[ $key ] = array();
}
foreach ( $all_tags as $base => $slugs ) {
    if ( ! in_array( $slug, $slugs, true ) ) continue;
    foreach ( $events as $key => $ev ) {
        if ( in_array( $base, $ev['files'], true ) ) {
            $by_event[ $key ][] = $base;
            break;
        }
    }
}

$matches = array();
foreach ( faw_matches() as $m ) {
    if ( in_array( $slug, $m['participants'], true ) ) {
        $matches[] = $m;
    }
}

$photo_total = 0;
foreach ( $by_event as $list ) $photo_total += count( $list );
$up       = wp_upload_dir();
$gal_base = trailingslashit( $up['baseurl'] ) . 'faw-gallery/';

// lightbox list for this profile (all tagged photos, event order)
$lb = array();
foreach ( $events as $key => $ev ) {
    foreach ( $by_event[ $key ] as $base ) {
        $lb[] = $base;
    }
}
?>
<section class="rprofile" style="--wrestler-color:<?php echo esc_attr( $w['color'] ); ?>;--wrestler-glow:<?php echo esc_attr( $w['glow'] ); ?>">
    <div class="rprofile__hero">
        <div class="rprofile__photo">
            <div class="rprofile__photo-inner fade-in-img" style="background-image:url('<?php echo esc_url( $w['img'] ); ?>');background-size:cover;background-position:center top;"></div>
            <?php if ( ! empty( $w['champion'] ) ) : ?><span class="rprofile__champ">&#9733; <?php echo esc_html( $w['champion'] ); ?></span><?php endif; ?>
        </div>
        <div class="rprofile__info">
            <a class="rprofile__back" href="<?php echo esc_url( home_url( '/#roster' ) ); ?>">&#8592; Full Roster</a>
            <span class="kicker"><?php echo in_array( 'tag', $w['tags'], true ) ? 'TAG TEAM' : 'FAW COMPETITOR'; ?></span>
            <h1 class="rprofile__name"><?php echo esc_html( $w['name'] ); ?></h1>
            <p class="rprofile__bio"><?php echo esc_html( $w['bio'] ); ?></p>
            <div class="rprofile__stats">
                <div class="rprofile__stat"><strong><?php echo (int) $photo_total; ?></strong><span>Tagged Photos</span></div>
                <div class="rprofile__stat"><strong><?php echo count( $matches ); ?></strong><span>Match Cards</span></div>
            </div>
            <div class="rprofile__cta">
                <a class="btn btn--primary" href="https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836" target="_blank" rel="noopener">Get Tickets &#8594;</a>
                <a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">Event Gallery &#8594;</a>
            </div>
        </div>
    </div>

    <?php if ( $photo_total ) : ?>
    <div class="rprofile__section">
        <div class="section-head">
            <span class="kicker">FROM THE RING</span>
            <h2 class="section-title">TAGGED <span class="hl">PHOTOS</span></h2>
        </div>
        <?php foreach ( $events as $key => $ev ) :
            if ( empty( $by_event[ $key ] ) ) continue; ?>
        <h3 class="rprofile__event-label"><?php echo esc_html( $ev['label'] ); ?> &middot; <?php echo esc_html( $ev['date_label'] ); ?></h3>
        <div class="gallery-grid rprofile__grid">
            <?php foreach ( $by_event[ $key ] as $base ) : ?>
            <div class="gallery-thumb fade-in-img" data-lightbox="<?php echo esc_attr( array_search( $base, $lb, true ) ); ?>" style="background-image:url('<?php echo esc_url( $gal_base . $ev['dir'] . '/' . $base . '-thumb.jpg' ); ?>');background-size:cover;background-position:center;"></div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
    <div class="rprofile__section">
        <div class="section-head">
            <span class="kicker">FROM THE RING</span>
            <h2 class="section-title">TAGGED <span class="hl">PHOTOS</span></h2>
            <p class="section-sub">Photos of <?php echo esc_html( $w['name'] ); ?> will appear here as event galleries get tagged.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( $matches ) : ?>
    <div class="rprofile__section">
        <div class="section-head">
            <span class="kicker">THE RECORD</span>
            <h2 class="section-title">MATCH <span class="hl">CARDS</span></h2>
        </div>
        <div class="rprofile__matches">
            <?php $ev_index = array_keys( $events ); ?>
            <?php foreach ( $matches as $m ) :
                $ev = isset( $events[ $m['event'] ] ) ? $events[ $m['event'] ] : null; ?>
            <article class="rprofile__match">
                <div class="rprofile__match-event">
                    <span class="rprofile__match-date"><?php echo $ev ? esc_html( $ev['date_label'] ) : ''; ?></span>
                    <span class="rprofile__match-name"><?php echo $ev ? esc_html( $ev['label'] ) : esc_html( $m['event'] ); ?></span>
                </div>
                <div class="rprofile__match-body">
                    <h3><?php echo esc_html( $m['title'] ); ?></h3>
                    <span class="rprofile__match-type"><?php echo esc_html( $m['type'] ); ?></span>
                    <?php if ( ! empty( $m['note'] ) ) : ?><p><?php echo esc_html( $m['note'] ); ?></p><?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="rprofile__section rprofile__section-cta">
        <div class="newsletter__inner">
            <div class="newsletter__copy">
                <span class="kicker kicker--light">SEE IT LIVE</span>
                <h2>THE FRONTIER IS <span class="hl">CALLING</span></h2>
                <p>Next up: Winter Warfare. Saturday, December 12, 2026 at the Pelican Park Brown Gymnasium in Mandeville.</p>
            </div>
            <div class="rprofile__cta-btns">
                <a class="btn btn--primary btn--lg" href="https://www.eventbrite.com/o/frontier-all-star-wrestling-121196022836" target="_blank" rel="noopener">Get Tickets &#8594;</a>
            </div>
        </div>
    </div>
</section>

<div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lightbox__close" id="lightboxClose" aria-label="Close">&times;</button>
    <button class="lightbox__arrow lightbox__arrow--prev" id="lightboxPrev" aria-label="Previous">&#8249;</button>
    <button class="lightbox__arrow lightbox__arrow--next" id="lightboxNext" aria-label="Next">&#8250;</button>
    <div class="lightbox__img" id="lightboxImg"></div>
    <div class="lightbox__bar">
      <span class="lightbox__counter" id="lightboxCounter"></span>
      <button class="lightbox__download" id="lightboxDownload" aria-label="Download photo">&#8595; Download</button>
      <button class="lightbox__share" id="lightboxShare" aria-label="Share photo" aria-haspopup="true">&#8594; Share</button>
    </div>
    <div class="lightbox__tags" id="lightboxTags"></div>
</div>
<script>
window.FAW_GALLERY = <?php echo wp_json_encode( array_map( function ( $base ) use ( $gal_base, $events ) {
    foreach ( $events as $ev ) {
        if ( in_array( $base, $ev['files'], true ) ) {
            return $gal_base . $ev['dir'] . '/' . $base . '-full.jpg';
        }
    }
    return '';
}, $lb ) ); ?>;
window.FAW_LB_INFO = <?php echo wp_json_encode( array_map( function ( $base ) use ( $all_tags, $slug ) {
    return array(
        'base' => $base,
        'tags' => isset( $all_tags[ $base ] ) ? $all_tags[ $base ] : array(),
    );
}, $lb ) ); ?>;
</script>

<?php get_footer();
