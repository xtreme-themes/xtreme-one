<?php
if ( have_posts() ): while ( have_posts() ) : the_post();
do_action( 'xtreme_before_post' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header>
        <?php xtreme_post_headline( 'h2', false ) ?>
        <?php xtreme_html5_byline() ?>
    </header>
<?php if ( wp_attachment_is_image() ) : ?>
    <figure>
    <a href="<?php echo wp_get_attachment_url() ?>" title="<?php echo esc_attr( get_the_title() ) ?>" rel="attachment">
    <?php
    $attachment_size = apply_filters( 'xtreme_attachment_size', 'large' );
    echo wp_get_attachment_image( $post->ID, $attachment_size ) ?></a>
    </figure>
<?php endif; ?>
</article>
<nav class="backto_link"><a href="<?php echo get_permalink( $post->post_parent ) ?>" title="<?php esc_attr( printf( __( 'Return to %s', XF_TEXTDOMAIN ), esc_html( get_the_title( $post->post_parent ), 1 ) ) ) ?>"><span>&larr;</span> <?php _e('Back to', XF_TEXTDOMAIN) ?> <?php echo get_the_title( $post->post_parent ) ?></a></nav>
<?php
do_action('xtreme_after_post');
xtreme_image_pagination();
comments_template( '', true );
endwhile;
endif;