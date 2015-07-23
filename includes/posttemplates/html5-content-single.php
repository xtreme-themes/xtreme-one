<article class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<header>
		<?php xtreme_post_titles( array( 'headline_link' => false, 'subtitle_link' => false ) ); ?>
		<?php xtreme_html5_byline(); ?>
	</header>
	<div class="entry-content">
		<?php the_content() ?>
		<?php wp_link_pages( array( 'before' => '<nav><strong>' . __( 'Pages:', XF_TEXTDOMAIN ) . '</strong> ', 'after' => '</nav>', 'next_or_number' => 'number' ) ) ?>
		<?php edit_post_link( __( 'Edit', XF_TEXTDOMAIN ), '<div class="postedit">', '</div>' ) ?>
	</div>
	<?php do_action( 'xtreme_single_footer' ) ?>
</article>