<?php

global $wp_query, $postcounter;

if ( ! have_posts() )
	xtreme_not_found();

do_action( 'xtreme_before_loop' );

$postcounter = 0;
while ( have_posts() ) :
	
	the_post();
	$postcounter ++;
	do_action( 'xtreme_before_post' );
	
	xtreme_get_template_part( 'content' );
	
	do_action( 'xtreme_after_post' );
endwhile;

do_action( 'xtreme_after_loop' );

if (  $wp_query->max_num_pages > 1 )
	xtreme_post_pagination();
