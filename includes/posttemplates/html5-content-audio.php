<article class="<?php xtreme_post_class() ?>" id="post-<?php the_ID() ?>">
	<header>
			<?php xtreme_post_format_icon( 'h3', __( 'Audio', XF_TEXTDOMAIN ) ) ?>
			<?php xtreme_post_titles( ); ?>
		<?php xtreme_html5_byline(); ?>
	</header>
	<div class="entry-content">
		<?php the_content() ?>
	</div>
	<?php xtreme_html5_post_meta() ?>
</article>
