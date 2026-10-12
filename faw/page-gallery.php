<?php
/**
 * FAW Photo Gallery — page template for the "gallery" page slug
 * All event photos (Crucible + Revolution on the River), filterable
 * by event/date, lazy-loaded thumbnails, load-more, lightbox.
 *
 * Depends on: inc/gallery-data.php (manifest)
 * Photos live in: wp-content/uploads/faw-gallery/{crucible,rivor}/
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once FAW_DIR . '/inc/gallery-data.php';

$events    = faw_gallery_events();
$uploads   = wp_upload_dir();
$gal_base  = trailingslashit( $uploads['baseurl'] ) . 'faw-gallery/';

// Build the JS photo index (permanent global order: crucible then rivor).
$photos = array();
$meta   = array();
$idx    = 0;
foreach ( $events as $key => $ev ) {
    $first = $idx;
    foreach ( $ev['files'] as $f ) {
        $photos[] = array(
            'full'  => $gal_base . $ev['dir'] . '/' . $f . '-full.jpg',
            'thumb' => $gal_base . $ev['dir'] . '/' . $f . '-thumb.jpg',
            'event' => $key,
        );
        $idx++;
    }
    $meta[ $key ] = array(
        'label'     => $ev['label'],
        'dateLabel' => $ev['date_label'],
        'dateKey'   => $ev['date_key'],
        'from'      => $first,
        'to'        => $idx - 1,
    );
}

get_header();
?>

<section class="gpage" id="gpage">
    <div class="gpage__head">
        <span class="kicker">EVERY MOMENT. EVERY EVENT.</span>
        <h1 class="gpage__title">PHOTO <span class="hl">GALLERY</span></h1>
        <p class="gpage__sub">All the action from FAW events — shot by Sherri Lynn Photography.</p>
    </div>

    <div class="gpage__filters">
        <div class="gpage__fgroup" id="gfEvent">
            <span class="gpage__flabel">EVENT</span>
            <button class="chip is-active" data-event="all">All Events</button>
            <button class="chip" data-event="crucible">Crucible</button>
            <button class="chip" data-event="rivor">Revolution on the River</button>
        </div>
        <div class="gpage__fgroup" id="gfDate">
            <span class="gpage__flabel">DATE</span>
            <button class="chip is-active" data-date="all">All Dates</button>
            <button class="chip" data-date="2026-06-26">Jun 26, 2026</button>
            <button class="chip" data-date="2026-08-15">Aug 15, 2026</button>
        </div>
        <div class="gpage__fgroup" id="gfSort">
            <span class="gpage__flabel">SORT</span>
            <button class="chip is-active" data-sort="newest">Newest First</button>
            <button class="chip" data-sort="oldest">Oldest First</button>
        </div>
    </div>

    <div class="gpage__grid" id="gpageGrid"></div>

    <div class="gpage__empty" id="gpageEmpty" hidden>
        <p>No photos match these filters.</p>
    </div>

    <div class="gpage__more" id="gpageMoreWrap">
        <button class="btn btn--primary" id="gpageMore">Load More Photos</button>
        <span class="gpage__count" id="gpageCount"></span>
    </div>
</section>

<!-- Lightbox (same IDs main.js already binds) -->
<div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lightbox__close" id="lightboxClose" aria-label="Close">×</button>
    <button class="lightbox__arrow lightbox__arrow--prev" id="lightboxPrev" aria-label="Previous">‹</button>
    <button class="lightbox__arrow lightbox__arrow--next" id="lightboxNext" aria-label="Next">›</button>
    <div class="lightbox__img" id="lightboxImg"></div>
    <div class="lightbox__bar">
      <span class="lightbox__counter" id="lightboxCounter"></span>
      <button class="lightbox__download" id="lightboxDownload" aria-label="Download photo">↓ Download</button>
    </div>
</div>

<script>
window.FAW_GALLERY = <?php echo wp_json_encode( array_column( $photos, 'full' ) ); ?>;
window.FAW_GP = {
    photos: <?php echo wp_json_encode( $photos ); ?>,
    meta: <?php echo wp_json_encode( $meta ); ?>,
    perPage: 48
};
</script>

<?php get_footer(); ?>
