<div class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<?php xtreme_post_format_icon( 'h3', __( 'Status', XF_TEXTDOMAIN ) ) ?>
	<?php
		$args = array(
			'headline_link' => TRUE,
			'subtitle_tag'  => 'h2'
		);
		xtreme_post_titles( $args );
	?>
	<?php xtreme_byline() ?>
	<div class="entry-content">
		<?php the_content() ?>
	</div>
	<?php xtreme_post_meta() ?>
</div>
