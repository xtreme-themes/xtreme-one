<?php
/*
* Xtreme Name: Author
*/
get_header();
if ( have_posts() ) :
	the_post();
	do_action('xtreme_author_title');
	$docmode = '';
	if ( get_the_author_meta( 'description' ) ):
		$el = 'div';
		$photo = 'div';
		if ( xtreme_is_html5() ) {
			$el = 'section';
			$photo = 'figure';
			$docmode = 'html5';
		} ?>
		<<?php echo $el ?> class='vcard' id='authorbox'>
		<<?php echo $photo ?> class='avatar photo'><?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'xtreme_author_avatar_size', 60 ) ) ?></<?php echo $photo ?>>
		<h3 class='fn n'><?php printf( __( 'About %s', XF_TEXTDOMAIN ), get_the_author() ); ?></h3>
		<p class='note'><?php the_author_meta( 'description' )?></p>
		<?php do_action( 'xtreme_author_inside_vcard' ) ?>
		</<?php echo $el ?>>
		<?php do_action( 'xtreme_author_after_vcard' ) ?>
	<?php endif;
	rewind_posts();
	get_template_part( $docmode . 'loop', 'author' );
endif;
get_footer();
