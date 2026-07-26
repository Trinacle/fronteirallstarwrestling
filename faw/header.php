<?php
/**
 * Header — FAW Theme
 * Center-split nav with logo in the middle
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080204">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ============ DYNAMIC SCROLL BACKGROUND ============ -->
<div class="dyn-bg" id="dynBg" aria-hidden="true">
    <div class="dyn-bg__orb dyn-bg__orb--1"></div>
    <div class="dyn-bg__orb dyn-bg__orb--2"></div>
    <div class="dyn-bg__grid"></div>
    <div class="dyn-bg__noise"></div>
    <div class="dyn-bg__curtain" id="dynCurtain"></div>
</div>

<!-- ============ HEADER (center-split nav) ============ -->
<header class="nav" id="nav">
    <div class="nav__inner">
        <nav class="nav__links nav__links--left" aria-label="Left navigation">
            <a href="<?php echo esc_url( home_url( '/#roster' ) ); ?>">Roster</a>
            <a href="<?php echo esc_url( home_url( '/#events' ) ); ?>">Events</a>
            <a href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>">Gallery</a>
        </nav>

        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" aria-label="FAW home">
            <span class="logo__shield">
                <svg viewBox="0 0 60 60" class="logo__svg" aria-hidden="true">
                    <path d="M12 18 L30 44 L48 18" stroke="currentColor" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="logo__text">
                <strong>FRONTIER</strong>
                <small>ALL-STAR WRESTLING</small>
            </span>
        </a>

        <nav class="nav__links nav__links--right" aria-label="Right navigation">
            <a href="<?php echo esc_url( home_url( '/#merch' ) ); ?>">Shop</a>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact</a>
            <a href="https://revolution-on-the-river.eventbrite.com" class="btn btn--primary btn--sm" target="_blank" rel="noopener">Tickets</a>
        </nav>

        <button class="nav__toggle" id="navToggle" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main id="content">
