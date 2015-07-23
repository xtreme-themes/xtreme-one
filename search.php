<?php
/*
* Xtreme Name: Search
*/
get_header();
if ( have_posts() ) :
    do_action('xtreme_search_title');
endif;
$docmode = '';
if ( xtreme_is_html5() ) {
	$docmode = 'html5';
}
get_template_part( $docmode . 'loop', 'search' );
get_footer();
