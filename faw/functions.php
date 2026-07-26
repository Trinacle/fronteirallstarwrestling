<?php
/**
 * FAW Theme Functions
 * Child theme of Astra — Frontier All-Star Wrestling
 *
 * @package FAW
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FAW_VERSION', '1.0.0' );
define( 'FAW_DIR', get_stylesheet_directory() );
define( 'FAW_URI', get_stylesheet_directory_uri() );

/**
 * Enqueue styles + scripts
 */
function faw_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'faw-fonts',
        'https://fonts.googleapis.com/css2?family=Archivo+Black&family=Oswald:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );

    // Main stylesheet — versioned for cache-busting
    wp_enqueue_style( 'faw-style', FAW_URI . '/assets/css/styles.css', array( 'faw-fonts' ), FAW_VERSION );

    // Main JS
    wp_enqueue_script( 'faw-main', FAW_URI . '/assets/js/main.js', array(), FAW_VERSION, true );

    // Pass AJAX URL + roster data to JS
    wp_localize_script( 'faw-main', 'FAW_DATA', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'faw_nonce' ),
        'themeUri' => FAW_URI,
        'siteUrl'  => home_url( '/' ),
        'roster'   => faw_get_roster(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'faw_enqueue_assets', 20 );

/**
 * Remove Astra's header/footer hooks — we use our own
 */
function faw_remove_astra_hooks() {
    // Remove Astra header
    remove_action( 'astra_header', 'astra_header_markup' );
    // Remove Astra footer
    remove_action( 'astra_footer', 'astra_footer_markup' );
    // Remove Astra's default CSS that fights our design
    wp_dequeue_style( 'astra-theme-css' );
    wp_dequeue_style( 'astra-dynamic-css' );
    wp_dequeue_style( 'astra-addon-stylesheet' );
}
add_action( 'wp_enqueue_scripts', 'faw_remove_astra_hooks', 99 );

/**
 * Kill page-builder CSS on FAW bespoke templates
 * Elementor/Astra enqueues ~26 CSS files after ours — strip them
 */
function faw_kill_page_builder_css() {
    if ( ! is_page_template( array(
        'page-templates/front-page.php',
    ) ) && ! is_front_page() ) {
        return;
    }

    $killlist = array(
        'elementor-frontend',
        'elementor-global',
        'elementor-post-' . get_the_ID(),
        'e-addons-frontend',
        'elementskit-framework-widget-styles',
        'elementskit-widget-styles-pro',
        'astra-addon-stylesheet',
        'astra-dynamic-css',
    );

    foreach ( $killlist as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }
}
add_action( 'wp_enqueue_scripts', 'faw_kill_page_builder_css', 100 );

/**
 * Register navigation menus
 */
function faw_register_menus() {
    register_nav_menus( array(
        'primary_left'  => __( 'Primary Menu (Left of Logo)', 'faw' ),
        'primary_right' => __( 'Primary Menu (Right of Logo)', 'faw' ),
        'footer_menu'   => __( 'Footer Menu', 'faw' ),
    ) );
}
add_action( 'init', 'faw_register_menus' );

/**
 * Add theme support
 */
function faw_theme_setup() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_image_size( 'faw-hero', 1600, 2000, true );
    add_image_size( 'faw-card', 800, 1000, true );
    add_image_size( 'faw-thumb', 450, 560, true );
}
add_action( 'after_setup_theme', 'faw_theme_setup' );

/**
 * Register the front-page template as the active homepage
 * Falls back to front-page.php automatically when set in Settings > Reading
 */

/**
 * Fallback menu (if no menu assigned)
 */
function faw_fallback_menu() {
    echo '<ul id="navLinks">';
    echo '<li><a href="' . esc_url( home_url( '/#roster' ) ) . '">Roster</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#events' ) ) . '">Events</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#gallery' ) ) . '">Gallery</a></li>';
    echo '</ul>';
}

/**
 * AJAX: Newsletter signup
 */
function faw_newsletter_submit() {
    check_ajax_referer( 'faw_nonce', 'nonce' );
    $email = sanitize_email( $_POST['email'] ?? '' );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Please enter a valid email.' ) );
    }
    // Store as a CPT or send to email service — placeholder for now
    wp_send_json_success( array( 'message' => "You're in! Watch your inbox for presale codes." ) );
}
add_action( 'wp_ajax_faw_newsletter', 'faw_newsletter_submit' );
add_action( 'wp_ajax_nopriv_faw_newsletter', 'faw_newsletter_submit' );

/**
 * AJAX: Talent application
 */
function faw_talent_submit() {
    check_ajax_referer( 'faw_nonce', 'nonce' );
    $name       = sanitize_text_field( $_POST['name'] ?? '' );
    $email      = sanitize_email( $_POST['email'] ?? '' );
    $role       = sanitize_text_field( $_POST['role'] ?? '' );
    $experience = intval( $_POST['experience'] ?? 0 );
    $message    = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $name ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Name and valid email required.' ) );
    }

    // Store submission as a CPT
    $post_id = wp_insert_post( array(
        'post_type'   => 'faw_application',
        'post_title'  => $name . ' — ' . $role,
        'post_status' => 'private',
        'post_content' => $message,
        'meta_input'  => array(
            'faw_email'      => $email,
            'faw_role'       => $role,
            'faw_experience' => $experience,
        ),
    ) );

    wp_send_json_success( array( 'message' => 'Application received. FAW talent scouting will be in touch.' ) );
}
add_action( 'wp_ajax_faw_talent', 'faw_talent_submit' );
add_action( 'wp_ajax_nopriv_faw_talent', 'faw_talent_submit' );

/**
 * AJAX: Sponsor inquiry
 */
function faw_sponsor_submit() {
    check_ajax_referer( 'faw_nonce', 'nonce' );
    $name    = sanitize_text_field( $_POST['name'] ?? '' );
    $company = sanitize_text_field( $_POST['company'] ?? '' );
    $email   = sanitize_email( $_POST['email'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $name ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Name and valid email required.' ) );
    }

    wp_insert_post( array(
        'post_type'    => 'faw_inquiry',
        'post_title'   => $company . ' — ' . $name,
        'post_status'  => 'private',
        'post_content' => $message,
        'meta_input'   => array(
            'faw_email'   => $email,
            'faw_company' => $company,
        ),
    ) );

    wp_send_json_success( array( 'message' => 'Thanks! Our partnership team will reach out within 48 hours.' ) );
}
add_action( 'wp_ajax_faw_sponsor', 'faw_sponsor_submit' );
add_action( 'wp_ajax_nopriv_faw_sponsor', 'faw_sponsor_submit' );

/**
 * AJAX: Contact form
 */
function faw_contact_submit() {
    check_ajax_referer( 'faw_nonce', 'nonce' );
    $name    = sanitize_text_field( $_POST['name'] ?? '' );
    $email   = sanitize_email( $_POST['email'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $name ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Name and valid email required.' ) );
    }

    wp_insert_post( array(
        'post_type'    => 'faw_inquiry',
        'post_title'   => 'Contact — ' . $name,
        'post_status'  => 'private',
        'post_content' => $message,
        'meta_input'   => array( 'faw_email' => $email ),
    ) );

    wp_send_json_success( array( 'message' => 'Message sent! We\'ll get back to you shortly.' ) );
}
add_action( 'wp_ajax_faw_contact', 'faw_contact_submit' );
add_action( 'wp_ajax_nopriv_faw_contact', 'faw_contact_submit' );

/**
 * Register CPTs for form submissions (private, admin-only)
 */
function faw_register_cpts() {
    // Talent applications
    register_post_type( 'faw_application', array(
        'labels' => array(
            'name' => 'Talent Applications',
            'singular_name' => 'Application',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => array( 'title', 'editor', 'custom-fields' ),
        'capability_type' => 'post',
    ) );

    // Sponsor / contact inquiries
    register_post_type( 'faw_inquiry', array(
        'labels' => array(
            'name' => 'Inquiries',
            'singular_name' => 'Inquiry',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email-alt',
        'supports' => array( 'title', 'editor', 'custom-fields' ),
        'capability_type' => 'post',
    ) );
}
add_action( 'init', 'faw_register_cpts' );

/**
 * Helper: get wrestler roster data
 * Returns the same data structure the JS uses
 */
function faw_get_roster() {
    $roster = array(
        array( 'name' => 'Phantom', 'initials' => 'PH', 'role' => 'High Flyer', 'tags' => array( 'flyer' ), 'color' => '#a855f7', 'glow' => 'rgba(168,85,247,0.28)', 'img' => FAW_URI . '/assets/img/phantom.webp', 'height' => "5'10\"", 'weight' => '185 lbs', 'from' => 'Parts Unknown', 'bio' => 'A masked high-flyer who defies gravity and disappears into the lights. No rope too high, no dive too reckless.', 'signature' => 'The Phantom Drop' ),
        array( 'name' => 'Xander Gold', 'initials' => 'XG', 'role' => 'Showman', 'tags' => array( 'flyer', 'technical' ), 'color' => '#ffb020', 'glow' => 'rgba(255,176,32,0.28)', 'img' => FAW_URI . '/assets/img/xander-gold.webp', 'height' => "6'0\"", 'weight' => '210 lbs', 'from' => 'Los Angeles, CA', 'bio' => 'If wrestling is a show, Xander Gold is the headliner. Equal parts athlete and entertainer.', 'signature' => 'The Gold Standard' ),
        array( 'name' => 'Mustang Mike', 'initials' => 'MM', 'role' => 'High Flyer', 'tags' => array( 'flyer' ), 'color' => '#ff2e4c', 'glow' => 'rgba(255,46,76,0.3)', 'img' => FAW_URI . '/assets/img/mustang-mike.webp', 'height' => "5'9\"", 'weight' => '175 lbs', 'from' => 'Dallas, TX', 'bio' => 'Built like a muscle car and twice as fast. The crowd-favorite underdog with a motor that never quits.', 'signature' => 'The Stampede' ),
        array( 'name' => 'Kris Keith', 'initials' => 'KK', 'role' => 'Heavyweight Champion', 'tags' => array( 'heavyweight', 'champion' ), 'color' => '#f5c542', 'glow' => 'rgba(245,197,66,0.3)', 'img' => FAW_URI . '/assets/img/kris-keith.webp', 'height' => "6'3\"", 'weight' => '255 lbs', 'from' => 'New Orleans, LA', 'bio' => 'The inaugural FAW Heavyweight Champion. A powerhouse brawler who combines raw strength with surprising agility, Kris Keith battered his way through the Crucible tournament to claim the gold.', 'champion' => 'FAW Heavyweight Champion', 'signature' => 'The Bayou Bomb' ),
        array( 'name' => 'Izaiah Zane', 'initials' => 'IZ', 'role' => 'Technician', 'tags' => array( 'technical' ), 'color' => '#0ea5e9', 'glow' => 'rgba(14,165,233,0.28)', 'img' => FAW_URI . '/assets/img/izaiah-zane.webp', 'height' => "5'11\"", 'weight' => '200 lbs', 'from' => 'Atlanta, GA', 'bio' => 'A mat general who treats every match like a chess game. The quiet assassin of the FAW locker room.', 'signature' => 'Zane Cradle' ),
        array( 'name' => 'Cowboy Cliff Rogers', 'initials' => 'CR', 'role' => 'Brawler', 'tags' => array( 'heavyweight' ), 'color' => '#d97706', 'glow' => 'rgba(217,119,6,0.28)', 'img' => FAW_URI . '/assets/img/cowboy-cliff.webp', 'height' => "6'2\"", 'weight' => '250 lbs', 'from' => 'Houston, TX', 'bio' => 'Country grit and a lariat that turns lights out. At home in a no-DQ scrap as much as a technical affair.', 'signature' => 'Last Call Lariat' ),
        array( 'name' => 'Antonio Bronson', 'initials' => 'AB', 'role' => 'Heavyweight', 'tags' => array( 'heavyweight' ), 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'img' => FAW_URI . '/assets/img/antonio-bronson.webp', 'height' => "6'4\"", 'weight' => '270 lbs', 'from' => 'Chicago, IL', 'bio' => 'The biggest man in FAW and one of the most dangerous. A walking demolition derby.', 'signature' => 'The Windy City Driver' ),
        array( 'name' => 'Cody Hawkins', 'initials' => 'CH', 'role' => 'Technician', 'tags' => array( 'technical' ), 'color' => '#94a3b8', 'glow' => 'rgba(148,163,184,0.25)', 'img' => FAW_URI . '/assets/img/cody-hawkins.webp', 'height' => "5'10\"", 'weight' => '190 lbs', 'from' => 'Covington, LA', 'bio' => 'The hometown hero. A crisp technical worker with the crowd firmly behind him every time.', 'signature' => 'The Covington Clutch' ),
        array( 'name' => 'Ashton Blake', 'initials' => 'AB', 'role' => 'All-Rounder', 'tags' => array( 'flyer', 'technical' ), 'color' => '#22c55e', 'glow' => 'rgba(34,197,94,0.28)', 'img' => FAW_URI . '/assets/img/ashton-blake.webp', 'height' => "6'0\"", 'weight' => '205 lbs', 'from' => 'Mobile, AL', 'bio' => 'Versatile, athletic, and impossible to pin down. One of the most dangerous wildcards on the roster.', 'signature' => "Blake's Wake" ),
        array( 'name' => 'Seymore Money', 'initials' => 'SM', 'role' => 'Showman', 'tags' => array( 'technical' ), 'color' => '#10b981', 'glow' => 'rgba(16,185,129,0.28)', 'img' => FAW_URI . '/assets/img/seymore-money.webp', 'height' => "5'11\"", 'weight' => '195 lbs', 'from' => 'New Orleans, LA', 'bio' => 'Flashy, confident, and always got a trick up his sleeve. Seymore Money brings the showmanship every time.', 'signature' => 'The Money Maker' ),
        array( 'name' => 'Suge Whyte', 'initials' => 'SW', 'role' => 'Heavyweight', 'tags' => array( 'heavyweight' ), 'color' => '#8b5cf6', 'glow' => 'rgba(139,92,246,0.28)', 'img' => FAW_URI . '/assets/img/suge-whyte.webp', 'height' => "6'1\"", 'weight' => '245 lbs', 'from' => 'Atlanta, GA', 'bio' => "A dominant force with a mean streak. Suge Whyte doesn't just beat opponents — he sends a message.", 'signature' => 'The White Out' ),
        array( 'name' => 'Shawn Crow', 'initials' => 'SC', 'role' => 'Brawler', 'tags' => array( 'heavyweight' ), 'color' => '#6366f1', 'glow' => 'rgba(99,102,241,0.28)', 'img' => FAW_URI . '/assets/img/shawn-crow.webp', 'height' => "6'0\"", 'weight' => '230 lbs', 'from' => 'Memphis, TN', 'bio' => 'Dark, relentless, and unpredictable. Shawn Crow stalks his prey and strikes when you least expect it.', 'signature' => 'The Crow Bar' ),
        array( 'name' => 'Chris Black', 'initials' => 'CB', 'role' => 'Heavyweight', 'tags' => array( 'heavyweight' ), 'color' => '#ef4444', 'glow' => 'rgba(239,68,68,0.3)', 'img' => FAW_URI . '/assets/img/chris-black.webp', 'height' => "6'2\"", 'weight' => '250 lbs', 'from' => 'Detroit, MI', 'bio' => 'The Franchise. A veteran technician who has held gold across the territory. Chris Black is the standard-bearer of professional wrestling.', 'champion' => 'The Franchise', 'signature' => 'The Blackout' ),
        array( 'name' => 'Rika & Gluttony', 'initials' => 'RG', 'role' => 'Tag Team', 'tags' => array( 'tag', 'heavyweight' ), 'color' => '#ec4899', 'glow' => 'rgba(236,72,153,0.28)', 'img' => FAW_URI . '/assets/img/rika-gluttony.webp', 'height' => 'Combined', 'weight' => 'Combined', 'from' => 'The Big Top', 'bio' => "A chaotic tag team combining Rika Wildlee's wild energy with The Big Top Butcher's raw power. Mayhem incarnate.", 'signature' => 'The Circus Suplex' ),
        array( 'name' => 'Thaddeus Collins', 'initials' => 'TC', 'role' => 'Heavyweight', 'tags' => array( 'heavyweight' ), 'color' => '#f59e0b', 'glow' => 'rgba(245,158,11,0.28)', 'img' => FAW_URI . '/assets/img/thaddeus.png', 'height' => "6'5\"", 'weight' => '275 lbs', 'from' => 'Dallas, TX', 'bio' => 'The Takeover. A massive athlete with championship DNA. Thaddeus Collins came to conquer.', 'signature' => 'The Hostile Takeover' ),
    );
    return $roster;
}

/**
 * Yoast SEO filters — use Yoast if active, don't duplicate meta tags
 */
function faw_seo_title( $title ) {
    if ( is_front_page() ) {
        return 'FAW — Frontier All-Star Wrestling | Live Pro Wrestling';
    }
    return $title;
}
add_filter( 'wpseo_title', 'faw_seo_title' );

/**
 * Defer jQuery if no forms need it immediately (helps performance)
 * Re-enable if Forminator or similar is added
 */
// add_filter( 'script_loader_tag', 'faw_defer_js', 10, 3 );
