<?php
/*
 * Xtreme Name: Page
 */
get_header();
if ( have_posts() ) :
	do_action('xtreme_before_loop');
	while ( have_posts() ) : the_post();
		do_action('xtreme_before_post');
		if ( !xtreme_is_html5() ) : ?>
			<div class="post page" id="post-<?php the_ID() ?>">
			<?php xtreme_post_subtitle('h3') ?>
			<?php xtreme_post_headline( 'h2', false ) ?>
		<?php else : ?>
			<article class="post page" id="post-<?php the_ID() ?>">
			<?php if ( current_theme_supports('xtreme-subtitles') ) : ?>
				<?php xtreme_post_subtitle('h3') ?>
			<?php endif; ?>
			<?php xtreme_post_headline( 'h2', false ) ?>
			<?php if ( current_theme_supports('xtreme-subtitles') ) : ?>
			<?php endif; ?>
		<?php endif ?>
		<div class="entry-content">
			<?php the_content() ?>
			<?php wp_link_pages( array( 'before' => '<p><strong>' . __( 'Pages:', XF_TEXTDOMAIN ) . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number' ) ) ?>
			<?php edit_post_link( __( 'Edit', XF_TEXTDOMAIN ), '<p>', '</p>' ) ?>
		</div>
		<?php if ( !xtreme_is_html5() ) echo '</div>'; else echo '</article>';
		do_action('xtreme_after_post');
	endwhile;
	do_action('xtreme_after_loop');
endif;
get_footer();