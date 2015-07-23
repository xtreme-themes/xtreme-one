<?php
/*
* Xtreme Name: Archive
*/
get_header();
if ( have_posts() ) :
    the_post();
    do_action('xtreme_archive_title');
endif;
rewind_posts();
$docmode = '';
if ( xtreme_is_html5() ) {
	$docmode = 'html5';
}
get_template_part( $docmode . 'loop', 'archive' );
get_footer();
