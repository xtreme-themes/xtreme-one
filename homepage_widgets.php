<?php
/**
 * Template Name: Widgetized Homepage
 * Xtreme Name:   Widgetized Homepage
 */

get_header();

if ( is_active_sidebar( 'widgetized-homepage' ) ) {
	dynamic_sidebar( 'widgetized-homepage' );
} else {
	do_action( 'xtreme_blindtext' );
}

get_footer();
