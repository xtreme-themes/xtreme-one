<div class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<?php
	$args = array(
		'headline_link' => FALSE,
		'subtitle_tag'  => 'h3'
	);
	xtreme_post_titles( $args );
	xtreme_byline();
	?>
	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages( array(
			'before' => '<p><strong>' . __( 'Pages:', XF_TEXTDOMAIN ) . '</strong> ',
			'after' => '</p>',
			'next_or_number' => 'number'
		) );
		edit_post_link( __( 'Edit', XF_TEXTDOMAIN ), '<div class="postedit">', '</div>' );
		?>
	</div>
</div>