<?php
$clb = 'xtreme_comments';
$elem = 'div';
$nav = 'div';
if ( xtreme_is_html5() ) {
    $clb = 'xtreme_html5_comments';
    $elem = 'section';
    $nav = 'nav';
}
$cp_tag = xtreme_get_comment_ping_title_tag();
?>
<<?php echo $elem ?> id="comments">
<?php if ( post_password_required() ) : ?>
    <p class="warning"><?php _e( 'This post is password protected. Enter the password to view any comments.', XF_TEXTDOMAIN ); ?></p>
</<?php echo $elem ?>>
<?php
    return;
endif;
if ( have_comments() ) :
    do_action('xtreme_comments_before_commentlist');
    $ping_count = count($comments_by_type['pings']);
    $comment_count = count( $comments_by_type['comment'] );

    if ( get_comment_pages_count() > 1 ) : ?>
            <<?php echo $nav ?> class="pagination">
                <div class="previous_link"><?php previous_comments_link( __('Older Comments <span>&raquo;</span>', XF_TEXTDOMAIN) ); ?></div>
                <div class="next_link"><?php next_comments_link( __('<span>&laquo;</span> Newer Comments', XF_TEXTDOMAIN) ); ?></div>
            </<?php echo $nav ?>>
   <?php endif; ?>
    <<?php echo $cp_tag ?> id="comments-title"><?php printf( _n( 'One Response', '%1$s Responses', $comment_count, XF_TEXTDOMAIN ),
        number_format_i18n( $comment_count ) ) ?></<?php echo $cp_tag ?>>
    <?php if ( $comment_count > 0 ) : ?>
    <ol class="commentlist">
    <?php wp_list_comments( array(
            'callback' => $clb,
            'type' => 'comment',
            'avatar_size' => xtreme_comments_avatar_size() ) ); ?>
    </ol>
    <?php endif ?>
    <?php if ( get_comment_pages_count() > 1 ) : ?>
            <<?php echo $nav ?> class="pagination">
                <div class="previous_link"><?php previous_comments_link( __('Older Comments <span>&raquo;</span>', XF_TEXTDOMAIN) ); ?></div>
                <div class="next_link"><?php next_comments_link( __('<span>&laquo;</span> Newer Comments', XF_TEXTDOMAIN) ); ?></div>
            </<?php echo $nav ?>>
    <?php endif;
    do_action('xtreme_comments_after_commentlist');
    
    if( $ping_count > 0 ):
        do_action('xtreme_comments_before_pinglist');
        ($ping_count === 1) ? $txt = __('Ping', XF_TEXTDOMAIN) : $txt = __('Pings', XF_TEXTDOMAIN); ?>
        <<?php echo $cp_tag ?> id="pings-title"><?php printf('%s %s', $ping_count, $txt) ?></<?php echo $cp_tag ?>>
        <ol class="pinglist">
            <?php wp_list_comments( array(
                'callback' => 'xtreme_pings',
                'type' => 'pings'
            )); ?>
        </ol>
        <?php do_action('xtreme_comments_after_pinglist');
    endif;
else : // this is displayed if there are no comments so far
    if ( comments_open() ) : // If comments are open, but there are no comments
    	echo '<span id="comments-title"></span>';
    else : // if comments are closed ?>
	    <?php if( !is_page() ) : ?>
			<p class="nocomments"><?php _e('Comments are closed.', XF_TEXTDOMAIN); ?></p>
		<?php endif; ?>
	<span class="ym-hideme" id="comments-title"></span>
    <?php endif;
endif;
comment_form(); ?>
</<?php echo $elem ?>>