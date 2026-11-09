<?php
/**
 * FAW Theme Functions
 * Child theme of Astra — Frontier All-Star Wrestling
 *
 * @package FAW
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FAW_VERSION', '2.14.1' );
define( 'FAW_DIR', get_stylesheet_directory() );
define( 'FAW_URI', get_stylesheet_directory_uri() );

require_once FAW_DIR . '/inc/photo-tags.php';
require_once FAW_DIR . '/inc/matches-data.php';

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

    // Gallery page JS (only on the /gallery/ page)
    if ( is_page( 'gallery' ) ) {
        wp_enqueue_script( 'faw-gallery-page', FAW_URI . '/assets/js/gallery-page.js', array( 'faw-main' ), FAW_VERSION, true );
    }

    // Pass AJAX URL + roster data to JS
    wp_localize_script( 'faw-main', 'FAW_DATA', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'faw_nonce' ),
        'themeUri' => FAW_URI,
        'siteUrl'  => home_url( '/' ),
        'roster'   => faw_get_roster(),
        'rosterUrl' => home_url( '/roster/' ),
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

    // Email the submission to FAW inbox
    $to      = 'info@fronteirallstarwrestling.com';
    $subject = 'FAW Contact Form — ' . $name;
    $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message\n";
    $headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
    wp_mail( $to, $subject, $body, $headers );

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
        array( 'name' => 'Phantom', 'initials' => 'PH', 'tags' => array(), 'color' => '#a855f7', 'glow' => 'rgba(168,85,247,0.28)', 'img' => FAW_URI . '/assets/img/phantom.webp', 'bio' => 'A masked competitor who defies gravity and disappears into the lights.' ),
        array( 'name' => 'Mustang Mike', 'initials' => 'MM', 'tags' => array(), 'color' => '#8b0a1e', 'glow' => 'rgba(139,10,30,0.3)', 'img' => FAW_URI . '/assets/img/mustang-mike.webp', 'bio' => 'A crowd-favorite competitor with a motor that never quits.' ),
        array( 'name' => 'Big Kon', 'initials' => 'BK', 'tags' => array(), 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'img' => FAW_URI . '/assets/img/big-kon.webp', 'bio' => 'A dominant force in the FAW ring.' ),
        array( 'name' => 'Double K', 'initials' => 'DK', 'tags' => array( 'champion' ), 'color' => '#f5c542', 'glow' => 'rgba(245,197,66,0.3)', 'img' => FAW_URI . '/assets/img/double-k.webp', 'bio' => 'The FAW Heavyweight Champion. A powerhouse competitor who claimed the gold at the Crucible tournament.', 'champion' => 'FAW Heavyweight Champion' ),
        array( 'name' => 'Prince Agballah & General Oba Zo', 'initials' => 'AO', 'tags' => array( 'tag' ), 'color' => '#d4af37', 'glow' => 'rgba(212,175,55,0.3)', 'img' => FAW_URI . '/assets/img/agballah-oba-zo.webp', 'bio' => 'A royal tag team alliance of dominance.' ),
        array( 'name' => 'Jake Logan', 'initials' => 'JL', 'tags' => array(), 'color' => '#2563eb', 'glow' => 'rgba(37,99,235,0.28)', 'img' => FAW_URI . '/assets/img/jake-logan.webp', 'bio' => 'A rising competitor making his mark.' ),
        array( 'name' => 'Josh Woods', 'initials' => 'JW', 'tags' => array(), 'color' => '#16a34a', 'glow' => 'rgba(22,163,74,0.28)', 'img' => FAW_URI . '/assets/img/josh-woods.webp', 'bio' => 'Technical precision meets raw intensity.' ),
        array( 'name' => 'Juice Man', 'initials' => 'JM', 'tags' => array(), 'color' => '#ea580c', 'glow' => 'rgba(234,88,12,0.28)', 'img' => FAW_URI . '/assets/img/juice-man.webp', 'bio' => 'Brings the juice every time he steps in the ring.' ),
        array( 'name' => 'Beautiful Bobby', 'initials' => 'BB', 'tags' => array(), 'color' => '#ec4899', 'glow' => 'rgba(236,72,153,0.28)', 'img' => FAW_URI . '/assets/img/beautiful-bobby.webp', 'bio' => 'Pure style and pure skill in the ring.' ),
        array( 'name' => 'Grappler III', 'initials' => 'G3', 'tags' => array(), 'color' => '#0891b2', 'glow' => 'rgba(8,145,178,0.28)', 'img' => FAW_URI . '/assets/img/grappler-iii.webp', 'bio' => 'Third generation grappler with technical pedigree.' ),
        array( 'name' => 'Purple Haze', 'initials' => 'PH', 'tags' => array(), 'color' => '#8b5cf6', 'glow' => 'rgba(139,92,246,0.3)', 'img' => FAW_URI . '/assets/img/purple-haze.webp', 'bio' => 'An enigmatic competitor who brings the smoke.' ),
        array( 'name' => 'Jaxson Strong', 'initials' => 'JS', 'tags' => array(), 'color' => '#059669', 'glow' => 'rgba(5,150,105,0.28)', 'img' => FAW_URI . '/assets/img/jaxson-strong.webp', 'bio' => 'Power and intensity personified.' ),
        array( 'name' => 'Rene Boucher', 'initials' => 'RB', 'tags' => array(), 'color' => '#7c3aed', 'glow' => 'rgba(124,58,237,0.28)', 'img' => FAW_URI . '/assets/img/rene-boucher.webp', 'bio' => 'A fierce competitor with a relentless edge.' ),
        array( 'name' => 'Izaiah Zane', 'initials' => 'IZ', 'tags' => array(), 'color' => '#0ea5e9', 'glow' => 'rgba(14,165,233,0.28)', 'img' => FAW_URI . '/assets/img/izaiah-zane.webp', 'bio' => 'A competitor who treats every match like a chess game.' ),
        array( 'name' => 'Cowboy Cliff Rogers', 'initials' => 'CR', 'tags' => array(), 'color' => '#d97706', 'glow' => 'rgba(217,119,6,0.28)', 'img' => FAW_URI . '/assets/img/cowboy-cliff.webp', 'bio' => 'Country grit and a lariat that turns lights out.' ),
        array( 'name' => 'Ashton Blake', 'initials' => 'AB', 'tags' => array(), 'color' => '#22c55e', 'glow' => 'rgba(34,197,94,0.28)', 'img' => FAW_URI . '/assets/img/ashton-blake.webp', 'bio' => 'Versatile, athletic, and impossible to pin down.' ),
        array( 'name' => 'Seymore Money', 'initials' => 'SM', 'tags' => array(), 'color' => '#10b981', 'glow' => 'rgba(16,185,129,0.28)', 'img' => FAW_URI . '/assets/img/seymore-money.webp', 'bio' => 'Flashy, confident, and always got a trick up his sleeve.' ),
        array( 'name' => 'Shawn Crow', 'initials' => 'SC', 'tags' => array(), 'color' => '#6366f1', 'glow' => 'rgba(99,102,241,0.28)', 'img' => FAW_URI . '/assets/img/shawn-crow.webp', 'bio' => 'Dark, relentless, and unpredictable.' ),
        array( 'name' => 'Rika & Gluttony', 'initials' => 'RG', 'tags' => array( 'tag' ), 'color' => '#ec4899', 'glow' => 'rgba(236,72,153,0.28)', 'img' => FAW_URI . '/assets/img/rika-gluttony.webp', 'bio' => "A chaotic tag team combining Rika Wildlee's wild energy with The Big Top Butcher's raw power." ),
        array( 'name' => 'Da Russell Twins', 'initials' => 'RT', 'tags' => array( 'tag' ), 'color' => '#14b8a6', 'glow' => 'rgba(20,184,166,0.28)', 'img' => FAW_URI . '/assets/img/da-russell-twins.webp', 'bio' => 'A double-trouble tag team bringing twin synergy and coordinated chaos to the FAW ring.' ),
        array( 'name' => 'Tino Valentino', 'initials' => 'TV', 'tags' => array(), 'color' => '#e11d48', 'glow' => 'rgba(225,29,72,0.28)', 'img' => FAW_URI . '/assets/img/tino-valentino.webp', 'bio' => 'Style, swagger, and a mean streak.' ),
        array( 'name' => 'Jayson Land', 'initials' => 'JL', 'tags' => array(), 'color' => '#4f46e5', 'glow' => 'rgba(79,70,229,0.28)', 'img' => FAW_URI . '/assets/img/jayson-land.webp', 'bio' => 'A rising star with something to prove.' ),
        array( 'name' => 'Manimal', 'initials' => 'MA', 'tags' => array(), 'color' => '#65a30d', 'glow' => 'rgba(101,163,13,0.28)', 'img' => FAW_URI . '/assets/img/manimal.webp', 'bio' => 'Half man, all animal.' ),
        array( 'name' => 'Cajun Outlaws', 'initials' => 'CO', 'tags' => array( 'tag' ), 'color' => '#c2410c', 'glow' => 'rgba(194,65,12,0.28)', 'img' => FAW_URI . '/assets/img/cajun-outlaws.webp', 'bio' => 'An outlaw tag team with nothing to lose.' ),
        array( 'name' => 'Sideshow Freaks', 'initials' => 'SF', 'tags' => array( 'tag' ), 'color' => '#7e22ce', 'glow' => 'rgba(126,34,206,0.28)', 'img' => FAW_URI . '/assets/img/sideshow-freaks.webp', 'bio' => 'The circus comes to town and nobody leaves the same.' ),
        array( 'name' => 'Magnum CA', 'initials' => 'MC', 'tags' => array(), 'color' => '#0d9488', 'glow' => 'rgba(13,148,136,0.28)', 'img' => FAW_URI . '/assets/img/magnum-ca.webp', 'bio' => 'A straight shooter with championship intentions.' ),
    );

    foreach ( $roster as $i => $w ) {
        $roster[ $i ]['slug'] = faw_wrestler_slug( $w['name'] );
    }
    return $roster;
}

/**
 * Rewrites: /roster/{slug}/ -> wrestler profile pages
 */
function faw_register_rewrites() {
    add_rewrite_rule( '^roster/([^/]+)/?$', 'index.php?faw_wrestler=$matches[1]', 'top' );
    add_rewrite_rule( '^roster/?$', 'index.php?faw_wrestler=__index__', 'top' );
}
add_action( 'init', 'faw_register_rewrites' );

function faw_query_vars( $vars ) {
    $vars[] = 'faw_wrestler';
    return $vars;
}
add_filter( 'query_vars', 'faw_query_vars' );

function faw_profile_template( $template ) {
    $slug = get_query_var( 'faw_wrestler' );
    if ( '' === $slug ) {
        return $template;
    }
    if ( '__index__' === $slug || ! faw_get_wrestler_by_slug( $slug ) ) {
        wp_safe_redirect( home_url( '/#roster' ), 302 );
        exit;
    }
    return locate_template( 'roster-profile.php' );
}
add_filter( 'template_include', 'faw_profile_template', 20 );

/**
 * Current photo deep link base (?photo=riv-042) if valid
 */
function faw_requested_photo_base() {
    if ( empty( $_GET['photo'] ) ) {
        return '';
    }
    $base = sanitize_key( wp_unslash( $_GET['photo'] ) );
    if ( ! preg_match( '/^[a-z]+\-([0-9]+)$/', $base ) ) {
        return '';
    }
    if ( ! function_exists( 'faw_photo_base_exists' ) ) {
        require_once FAW_DIR . '/inc/photo-tags.php';
    }
    return faw_photo_base_exists( $base ) ? $base : '';
}

/**
 * Photo URL helpers (uploads/faw-gallery)
 */
function faw_photo_url( $base, $size = 'full' ) {
    if ( ! function_exists( 'faw_gallery_events' ) ) {
        require_once FAW_DIR . '/inc/gallery-data.php';
    }
    foreach ( faw_gallery_events() as $ev ) {
        if ( in_array( $base, $ev['files'], true ) ) {
            $up = wp_upload_dir();
            return trailingslashit( $up['baseurl'] ) . 'faw-gallery/' . $ev['dir'] . '/' . $base . '-' . $size . '.jpg';
        }
    }
    return '';
}

/**
 * OpenGraph for shared photo permalinks (works with or without Yoast)
 */
function faw_photo_og_title() {
    $base = faw_requested_photo_base();
    if ( ! $base ) {
        return '';
    }
    $names = array();
    $index = faw_roster_index();
    foreach ( faw_get_photo_tags( $base ) as $slug ) {
        if ( isset( $index[ $slug ] ) ) {
            $names[] = $index[ $slug ]['name'];
        }
    }
    $title = $names ? implode( ' · ', $names ) . ' - FAW Photo' : 'FAW Photo';
    return $title . ' | Frontier All-Star Wrestling';
}

function faw_og_image_url() {
    if ( is_front_page() ) {
        return '';
    }
    if ( faw_requested_photo_base() ) {
        return faw_photo_url( faw_requested_photo_base(), 'full' );
    }
    $slug = get_query_var( 'faw_wrestler' );
    if ( $slug ) {
        $w = faw_get_wrestler_by_slug( $slug );
        if ( $w && ! empty( $w['img'] ) ) {
            return $w['img'];
        }
    }
    return '';
}

add_filter( 'wpseo_title', function ( $title ) {
    $t = faw_photo_og_title();
    if ( $t ) {
        return $t;
    }
    $slug = get_query_var( 'faw_wrestler' );
    if ( $slug ) {
        $w = faw_get_wrestler_by_slug( $slug );
        if ( $w ) {
            return $w['name'] . ' - FAW Roster | Frontier All-Star Wrestling';
        }
    }
    return $title;
} );

add_filter( 'wpseo_opengraph_image', function ( $image ) {
    $img = faw_og_image_url();
    return $img ? $img : $image;
} );

// Fallback OG tags if Yoast is not active
add_action( 'wp_head', function () {
    if ( defined( 'WPSEO_VERSION' ) ) {
        return;
    }
    $img  = faw_og_image_url();
    $t    = faw_photo_og_title();
    $slug = get_query_var( 'faw_wrestler' );
    if ( $slug && ! $t ) {
        $w = faw_get_wrestler_by_slug( $slug );
        $t = $w ? $w['name'] . ' - FAW Roster | Frontier All-Star Wrestling' : '';
    }
    if ( ! $img && ! $t ) {
        return;
    }
    echo '<meta property="og:title" content="' . esc_attr( $t ?: get_bloginfo( 'name' ) ) . '" />' . "\n";
    if ( $img ) {
        echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
    }
    echo '<meta property="og:type" content="website" />' . "\n";
}, 5 );

/**
 * AJAX: save photo tags (admin only)
 */
function faw_admin_save_tags() {
    check_ajax_referer( 'faw_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Not allowed.' ), 403 );
    }
    $base = isset( $_POST['base'] ) ? sanitize_key( wp_unslash( $_POST['base'] ) ) : '';
    if ( ! faw_photo_base_exists( $base ) ) {
        wp_send_json_error( array( 'message' => 'Unknown photo.' ) );
    }
    $valid  = array_keys( faw_roster_index() );
    $slugs  = array();
    if ( isset( $_POST['slugs'] ) && is_array( $_POST['slugs'] ) ) {
        foreach ( wp_unslash( $_POST['slugs'] ) as $s ) {
            $s = sanitize_key( $s );
            if ( in_array( $s, $valid, true ) ) {
                $slugs[] = $s;
            }
        }
    }
    faw_set_photo_tags( $base, $slugs );
    wp_send_json_success( array( 'message' => count( $slugs ) . ' tag(s) saved.', 'slugs' => $slugs ) );
}
add_action( 'wp_ajax_faw_admin_save_tags', 'faw_admin_save_tags' );

/**
 * Admin: Photo Tags screen
 */
function faw_register_photo_tags_admin() {
    add_menu_page(
        'Photo Tags',
        'Photo Tags',
        'manage_options',
        'faw-photo-tags',
        'faw_render_photo_tags_admin',
        'dashicons-format-gallery',
        26
    );
}
add_action( 'admin_menu', 'faw_register_photo_tags_admin' );

function faw_render_photo_tags_admin() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! function_exists( 'faw_gallery_events' ) ) {
        require_once FAW_DIR . '/inc/gallery-data.php';
    }
    $events = faw_gallery_events();
    $up     = wp_upload_dir();
    $base   = trailingslashit( $up['baseurl'] ) . 'faw-gallery/';
    $tags   = faw_all_photo_tags();
    $roster = faw_roster_index();
    ?>
    <div class="wrap" id="faw-pt-wrap">
        <h1>FAW Photo Tags</h1>
        <p>Click a photo, check the wrestlers in it, save. Tagged photos show a badge. Tags appear on the site immediately (profiles, gallery filter, lightbox).</p>
        <div id="faw-pt-app"
             data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
             data-nonce="<?php echo esc_attr( wp_create_nonce( 'faw_nonce' ) ); ?>"
             data-baseurl="<?php echo esc_url( $base ); ?>"
             data-events="<?php echo esc_attr( wp_json_encode( $events ) ); ?>"
             data-tags="<?php echo esc_attr( wp_json_encode( $tags ) ); ?>"
             data-roster="<?php echo esc_attr( wp_json_encode( $roster ) ); ?>"></div>
    </div>
    <style>
        #faw-pt-wrap { font-family: -apple-system, system-ui, sans-serif; }
        #faw-pt-app { display: flex; gap: 16px; align-items: flex-start; margin-top: 12px; }
        #faw-pt-grid-wrap { flex: 1; min-width: 0; }
        #faw-pt-filters { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; align-items: center; }
        #faw-pt-filters button { padding: 6px 14px; border: 1px solid #2271b1; background: #fff; color: #2271b1; border-radius: 3px; cursor: pointer; }
        #faw-pt-filters button.on { background: #2271b1; color: #fff; }
        #faw-pt-search { min-width: 220px; }
        #faw-pt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; max-height: 70vh; overflow-y: auto; border: 1px solid #ccd0d4; padding: 10px; background: #fff; }
        .faw-pt-thumb { position: relative; border: 2px solid transparent; border-radius: 3px; overflow: hidden; cursor: pointer; background: #f0f0f1; aspect-ratio: 3/2; }
        .faw-pt-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .faw-pt-thumb.sel { border-color: #2271b1; }
        .faw-pt-badge { position: absolute; top: 3px; right: 3px; background: #d63638; color: #fff; font-size: 10px; font-weight: 700; border-radius: 8px; padding: 1px 6px; }
        #faw-pt-side { width: 340px; position: sticky; top: 46px; background: #fff; border: 1px solid #ccd0d4; padding: 14px; border-radius: 3px; }
        #faw-pt-side img { width: 100%; border-radius: 3px; margin-bottom: 8px; }
        #faw-pt-side .base { font-family: monospace; color: #50575e; margin-bottom: 10px; }
        #faw-pt-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 10px; max-height: 320px; overflow-y: auto; margin-bottom: 10px; }
        #faw-pt-checks label { font-size: 12px; display: flex; gap: 5px; align-items: center; }
        #faw-pt-status { margin-left: 8px; font-weight: 600; }
        #faw-pt-empty { color: #50575e; padding: 20px 0; text-align: center; grid-column: 1 / -1; }
    </style>
    <script>
    (function () {
      var app = document.getElementById('faw-pt-app');
      var events = JSON.parse(app.dataset.events || '{}');
      var tags = JSON.parse(app.dataset.tags || '{}');
      var roster = JSON.parse(app.dataset.roster || '{}');
      var baseurl = app.dataset.baseurl;
      var evFilter = 'all', q = '', selected = null;

      function files() {
        var out = [];
        Object.keys(events).forEach(function (k) {
          var ev = events[k];
          ev.files.forEach(function (f) { out.push({ base: f, ev: k, dir: ev.dir }); });
        });
        return out;
      }
      function renderFilters() {
        var f = document.getElementById('faw-pt-filters');
        var html = '';
        Object.keys(events).forEach(function (k) {
          html += '<button data-ev="' + k + '" class="' + (evFilter === k ? 'on' : '') + '">' + events[k].label + '</button>';
        });
        html += '<button data-ev="all" class="' + (evFilter === 'all' ? 'on' : '') + '">All</button>';
        html += '<input type="search" id="faw-pt-search" placeholder="Filter by photo ID (e.g. riv-042)" value="' + q + '">';
        f.innerHTML = html;
        f.querySelectorAll('button').forEach(function (b) {
          b.addEventListener('click', function () { evFilter = b.dataset.ev; renderFilters(); renderGrid(); });
        });
        document.getElementById('faw-pt-search').addEventListener('input', function (e) { q = e.target.value.trim().toLowerCase(); renderGrid(); });
      }
      function renderGrid() {
        var g = document.getElementById('faw-pt-grid');
        var list = files().filter(function (p) {
          if (evFilter !== 'all' && p.ev !== evFilter) return false;
          if (q && p.base.indexOf(q) === -1) return false;
          return true;
        });
        if (!list.length) { g.innerHTML = '<div id="faw-pt-empty">No photos match.</div>'; return; }
        g.innerHTML = list.map(function (p) {
          var n = (tags[p.base] || []).length;
          return '<div class="faw-pt-thumb" data-base="' + p.base + '" data-dir="' + p.dir + '">' +
            '<img loading="lazy" src="' + baseurl + p.dir + '/' + p.base + '-thumb.jpg" alt="' + p.base + '">' +
            (n ? '<span class="faw-pt-badge">' + n + '</span>' : '') + '</div>';
        }).join('');
        if (selected) {
          var el = g.querySelector('[data-base="' + selected + '"]');
          if (el) el.classList.add('sel');
        }
      }
      function renderSide() {
        var side = document.getElementById('faw-pt-side');
        if (!selected) {
          side.innerHTML = '<p>Select a photo to tag it.</p>';
          return;
        }
        var p = files().filter(function (x) { return x.base === selected; })[0];
        var cur = tags[selected] || [];
        var checks = Object.keys(roster).map(function (slug) {
          return '<label><input type="checkbox" value="' + slug + '"' + (cur.indexOf(slug) !== -1 ? ' checked' : '') + '> ' + roster[slug].name + '</label>';
        }).join('');
        side.innerHTML =
          '<img src="' + baseurl + p.dir + '/' + p.base + '-full.jpg" alt="">' +
          '<div class="base">' + selected + ' · ' + events[p.ev].label + '</div>' +
          '<div id="faw-pt-checks">' + checks + '</div>' +
          '<button class="button button-primary" id="faw-pt-save">Save Tags</button>' +
          '<span id="faw-pt-status"></span>';
        document.getElementById('faw-pt-save').addEventListener('click', function () {
          var slugs = Array.prototype.slice.call(document.querySelectorAll('#faw-pt-checks input:checked')).map(function (i) { return i.value; });
          var fd = new FormData();
          fd.append('action', 'faw_admin_save_tags');
          fd.append('nonce', app.dataset.nonce);
          fd.append('base', selected);
          slugs.forEach(function (s) { fd.append('slugs[]', s); });
          document.getElementById('faw-pt-status').textContent = 'Saving...';
          fetch(app.dataset.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              document.getElementById('faw-pt-status').textContent = res.success ? 'Saved.' : 'Error.';
              if (res.success) {
                if (slugs.length) tags[selected] = slugs; else delete tags[selected];
                renderGrid();
              }
            })
            .catch(function () { document.getElementById('faw-pt-status').textContent = 'Network error.'; });
        });
      }
      document.addEventListener('click', function (e) {
        var t = e.target.closest('.faw-pt-thumb');
        if (!t) return;
        selected = t.dataset.base;
        document.querySelectorAll('.faw-pt-thumb').forEach(function (x) { x.classList.remove('sel'); });
        t.classList.add('sel');
        renderSide();
      });
      app.innerHTML =
        '<div id="faw-pt-grid-wrap"><div id="faw-pt-filters"></div><div id="faw-pt-grid"></div></div>' +
        '<div id="faw-pt-side"></div>';
      renderFilters();
      renderGrid();
      renderSide();
    })();
    </script>
    <?php
}

/**
 * Helper: wrestler slug (stable URL key for profile pages)
 */
function faw_wrestler_slug( $name ) {
    $s = strtolower( $name );
    $s = str_replace( array( '&', '.' ), array( ' and ', ' ' ), $s );
    $s = preg_replace( '/[^a-z0-9]+/', '-', $s );
    $s = trim( $s, '-' );
    return $s;
}

/**
 * Helper: roster lookup by slug
 */
function faw_get_wrestler_by_slug( $slug ) {
    $roster = faw_get_roster();
    foreach ( $roster as $w ) {
        if ( faw_wrestler_slug( $w['name'] ) === $slug ) {
            return $w;
        }
    }
    return null;
}

/**
 * Helper: index roster by slug => array( name, slug )
 */
function faw_roster_index() {
    $out = array();
    foreach ( faw_get_roster() as $w ) {
        $slug = faw_wrestler_slug( $w['name'] );
        $out[ $slug ] = array(
            'name' => $w['name'],
            'slug' => $slug,
        );
    }
    return $out;
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

/**
 * Meta Pixel — base code + Eventbrite click tracking
 * Pixel ID: 3055922027946414
 * Fires PageView sitewide; InitiateCheckout on any eventbrite.com link click.
 * Note: if a consent-management platform is added later, gate this behind marketing consent.
 */
add_action( 'wp_head', function () {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        echo '<!-- Meta Pixel: enabled, ID 3055922027946414 -->' . "\n";
    }
    ?>
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '3055922027946414');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=3055922027946414&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
<script>
document.addEventListener('click', function (e) {
  var el = e.target;
  if (!el || !el.closest) return;
  var a = el.closest('a[href*="eventbrite.com"]');
  if (a && typeof fbq === 'function') {
    fbq('track', 'InitiateCheckout', {
      content_name: 'Voodoo Nights - FAW Halloween Showdown',
      content_category: 'Event Tickets',
      content_ids: ['1998518316082']
    });
  }
}, true);
</script>
    <?php
}, 1 );
