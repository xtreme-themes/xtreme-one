<?php
if ( have_posts() ):
	do_action( 'xtreme_before_loop' );
	while ( have_posts() ) : the_post();
		do_action( 'xtreme_before_post' );
		?>
		<div id="post-<?php the_ID(); ?>" <?php xtreme_post_class(); ?>>
			<?php xtreme_post_headline( 'h2', false ) ?>
			<?php xtreme_byline() ?>
			<div class="entry-content">
			<?php if ( wp_attachment_is_image() ) : ?>
				<a href="<?php echo wp_get_attachment_url() ?>" title="<?php echo esc_attr( get_the_title() ) ?>" rel="attachment">
				<?php
				$attachment_size = apply_filters( 'xtreme_attachment_size', 'large' );
				echo wp_get_attachment_image( get_the_ID(), $attachment_size ) ?></a>
			<?php endif; ?>
			</div>
			<p class="backto_link"><a href="<?php echo get_permalink( $post->post_parent ) ?>" title="<?php esc_attr( printf( __( 'Return to %s', XF_TEXTDOMAIN ), esc_html( get_the_title( $post->post_parent ), 1 ) ) ) ?>"><span>&larr;</span> <?php _e('Back to', XF_TEXTDOMAIN) ?> <?php echo get_the_title( $post->post_parent ) ?></a></p>
		</div>
		<?php
		do_action('xtreme_after_post');
		xtreme_image_pagination();
		comments_template( '', true );
	endwhile;
	do_action( 'xtreme_after_loop' );
endif;