<?php
/**
 * FAW Theme Functions
 * Child theme of Astra — Frontier All-Star Wrestling
 *
 * @package FAW
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FAW_VERSION', '2.3.0' );
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
