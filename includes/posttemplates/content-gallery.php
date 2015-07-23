<div class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<?php xtreme_post_format_icon( 'h3', __( 'Gallery', XF_TEXTDOMAIN ) ) ?>
	<?php xtreme_post_titles(  ); ?>
		<?php
		$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
		if ( $images ) :
			$total_images = count( $images );
			$image = array_shift( $images );
			$image_img_tag = wp_get_attachment_image( $image->ID, 'thumbnail' );
			$align = apply_filters( 'xtreme_loop_thumbnail_align', 'alignleft' );
		?>
			<a class="xf-thumbnail <?php echo $align ?>" title="<?php _e( '', XF_TEXTDOMAIN ) ?>" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
			<div class="entry-content">
				<?php the_excerpt() ?>
				<p><?php printf( _n( 'This gallery contains <a %1$s>%2$s photo</a>.', 'This gallery contains <a %1$s>%2$s photos</a>.', $total_images, XF_TEXTDOMAIN ),
					'href="' . esc_url( get_permalink() ) . '" title="' . sprintf( esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark"',
					number_format_i18n( $total_images ) ); ?></p>
			</div>
		<?php else : ?>
			<div class="entry-content">
				<?php the_excerpt() ?>
			</div>
		<?php endif; ?>
	<?php xtreme_byline() ?>
	<?php xtreme_post_meta() ?>
</div>