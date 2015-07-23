<article class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<header>
		<?php xtreme_post_titles( ); ?>
		<?php xtreme_html5_byline(); ?>
	</header>
	<?php
	$size = apply_filters( 'xtreme_loop_thumbnail_size', 'thumbnail' );
	$align = apply_filters( 'xtreme_loop_thumbnail_align', 'alignleft' );
	xtreme_post_thumbnail( $size, $align );
	?>
	<div class="entry-content">
		<?php xtreme_excerpt( 55, __( 'Read more...', XF_TEXTDOMAIN ), '...' ) ?>
	</div>
</article>
