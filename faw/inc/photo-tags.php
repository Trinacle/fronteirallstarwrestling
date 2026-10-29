<?php
/**
 * FAW photo tags — storage + helpers.
 *
 * Tags are stored as one WP option per photo: faw_tag_<fileBase>
 * (e.g. faw_tag_riv-042) whose value is an array of roster slugs.
 * File bases are the gallery manifest IDs (riv-NNN / cru-NNN).
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Valid bases per current manifest (prevents junk options).
 */
function faw_photo_base_exists( $base ) {
    if ( ! function_exists( 'faw_gallery_events' ) ) {
        require_once FAW_DIR . '/inc/gallery-data.php';
    }
    foreach ( faw_gallery_events() as $ev ) {
        if ( in_array( $base, $ev['files'], true ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Get tag slugs for one photo base.
 */
function faw_get_photo_tags( $base ) {
    $tags = get_option( 'faw_tag_' . $base, array() );
    return is_array( $tags ) ? $tags : array();
}

/**
 * All tags: base => array( slug, ... ) — only options that exist.
 */
function faw_all_photo_tags() {
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options}
         WHERE option_name LIKE 'faw\_tag\_%'",
        ARRAY_A
    );
    $out = array();
    foreach ( (array) $rows as $row ) {
        $base = substr( $row['option_name'], strlen( 'faw_tag_' ) );
        $tags = maybe_unserialize( $row['option_value'] );
        if ( is_array( $tags ) && ! empty( $tags ) ) {
            $out[ $base ] = array_values( $tags );
        }
    }
    return $out;
}

/**
 * Save tags for one photo base. Empty array deletes the option.
 */
function faw_set_photo_tags( $base, array $slugs ) {
    $key = 'faw_tag_' . $base;
    if ( empty( $slugs ) ) {
        delete_option( $key );
        return true;
    }
    update_option( $key, array_values( array_unique( array_filter( $slugs ) ) ), false );
    return true;
}
