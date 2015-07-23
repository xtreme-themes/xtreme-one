<div class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<?php xtreme_post_format_icon( 'h3', __('Quote', XF_TEXTDOMAIN ) ) ?>
	<?php xtreme_post_titles(  ); ?>
	<?php xtreme_byline() ?>
	<div class="entry-content">
		<?php the_content() ?>
	</div>
	<?php xtreme_post_meta() ?>
</div>
