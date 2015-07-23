<?php
if ( have_posts() ) :
	do_action('xtreme_before_loop');
	while ( have_posts() ) : the_post();
		do_action('xtreme_before_single_post');
		get_template_part('includes/posttemplates/html5-content', 'single');
		do_action('xtreme_after_single_post');
		comments_template( '', true );
		do_action('xtreme_after_comments_template');
	endwhile;
	do_action('xtreme_after_loop');
endif;