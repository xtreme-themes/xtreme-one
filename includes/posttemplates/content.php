<div class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<?php
	xtreme_post_titles(  );
	xtreme_byline();
	$size = apply_filters( 'xtreme_loop_thumbnail_size', 'thumbnail' );
	$align = apply_filters( 'xtreme_loop_thumbnail_align', 'alignleft' );
	xtreme_post_thumbnail( $size, $align );
	?>
	<div class="entry-content">
		<?php xtreme_excerpt( 55, __( 'Read more...', XF_TEXTDOMAIN ), '...' ) ?>
	</div>
</div>
