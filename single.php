<?php
/*
 * Xtreme Name: Single
 */

get_header();
$docmode = '';
if ( xtreme_is_html5() ) {
	$docmode = 'html5';
}
get_template_part( $docmode . 'loop', 'single' );
get_footer();