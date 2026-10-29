<?php
/**
 * FAW match history — per-event match cards.
 * 'participants' holds roster slugs (faw_wrestler_slug of the roster name).
 * Edit this file to add cards; profiles pick them up automatically.
 *
 * @package FAW
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function faw_matches() {
    return array(
        array(
            'event'        => 'crucible',
            'title'        => "Phantom vs. Steve O'Malley",
            'type'         => 'Singles Match',
            'note'         => 'Main event of the FAW debut show.',
            'participants' => array( 'phantom' ),
        ),
        array(
            'event'        => 'crucible',
            'title'        => 'Crucible Tournament Final',
            'type'         => 'Heavyweight Championship',
            'note'         => 'Double K claims the gold and becomes the first FAW Heavyweight Champion.',
            'participants' => array( 'double-k' ),
        ),
        array(
            'event'        => 'rivor',
            'title'        => 'Double K Championship Defense',
            'type'         => 'Heavyweight Championship',
            'note'         => 'Double K defends the FAW Heavyweight Championship at the Covington Country Club.',
            'participants' => array( 'double-k' ),
        ),
    );
}
