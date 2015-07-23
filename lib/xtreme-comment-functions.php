<?php

if( !function_exists( 'xtreme_comments' ) ) {
	function xtreme_comments( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
?>
<li <?php comment_class() ?> id="li-comment-<?php comment_ID() ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment-wrapper">
		<div class="comment-author vcard">
			<?php if ( xtreme_show_avatar() ) {
				echo "<span class='align". xtreme_avatar_align() . "'>";
				echo get_avatar( $comment, $args['avatar_size'] );
				echo "</span>\n";
			}
			printf( '<cite class="fn">%1s</cite>&nbsp;<span class="says">%2s</span>' , get_comment_author_link(), __( 'says:', XF_TEXTDOMAIN ) ) ?>
		</div>
		<div class="comment-meta commentmetadata">
			<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><?php printf( __( '%1$s at %2$s', XF_TEXTDOMAIN ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', XF_TEXTDOMAIN ),'  ','' ) ?>
		</div>
		<div class="comment-body">
			<?php comment_text() ?>
		</div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			   <p class="info"><?php _e( 'Your comment is awaiting moderation.', XF_TEXTDOMAIN ) ?></p>
		<?php endif; ?>
		<?php if ( get_option('thread_comments') ) : ?>
			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div>
		<?php endif ?>
	</div>
<?php
	}
}

if( !function_exists( 'xtreme_html5_comments' ) ) {
	function xtreme_html5_comments( $comment, $args, $depth ) {
		$GLOBALS ['comment'] = $comment;
?>
<li <?php comment_class() ?> id="li-comment-<?php comment_ID() ?>">
	<article id="comment-<?php comment_ID() ?>" class="comment-wrapper">
		<header class="comment-author vcard">
			<?php if ( xtreme_show_avatar() ) {
				echo "<span class='align". xtreme_avatar_align() . "'>";
				echo get_avatar( $comment, $args['avatar_size'] );
				echo "</span>\n";
			}
			printf( '<cite class="fn">%1s</cite>&nbsp;<span class="says">%2s</span>' , get_comment_author_link(), __( 'says:', XF_TEXTDOMAIN ) );
			edit_comment_link( __( '(Edit)', XF_TEXTDOMAIN ),'  ','' ) ?>
			<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
				<time datetime="<?php echo get_comment_date('c') ?>">
					<?php printf( __( '%1$s at %2$s', XF_TEXTDOMAIN ), get_comment_date(),  get_comment_time() ); ?>
				</time>
			</a>
		</header>
		<div class="comment-body">
			<?php comment_text() ?>
		</div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<p class="info"><?php _e( 'Your comment is awaiting moderation.', XF_TEXTDOMAIN ); ?></p>
		<?php endif; ?>
		<?php if ( get_option('thread_comments') ) : ?>
			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div>
		<?php endif ?>
	</article>
<?php
	}
}

function xtreme_pings( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	?>
	<li id="comment-<?php comment_ID() ?>"><?php comment_author_link() ?>
	<?php
}

function xtreme_comment_tabs_before() {
	$html = "";
	$option = get_option( XF_OPTIONS );
	if ( ( bool ) $option['xc_comments']['tabbed_comments']['value'] === true ) {
		$html .= "<div class='tab-content'>";
	}
	echo $html;
}
add_action( 'xtreme_comments_before_commentlist', 'xtreme_comment_tabs_before' );
add_action( 'xtreme_comments_before_pinglist', 'xtreme_comment_tabs_before' );

function xtreme_comment_tabs_after() {
	$html = "";
	$option = get_option( XF_OPTIONS );
	if ( (bool) $option['xc_comments']['tabbed_comments']['value'] === true ) {
		$html .= "</div>";
	}
	echo $html;
}
add_action( 'xtreme_comments_after_commentlist', 'xtreme_comment_tabs_after' );
add_action( 'xtreme_comments_after_pinglist', 'xtreme_comment_tabs_after' );

function xtreme_comment_default_fields() {
	$aria_req = "";
	$commenter = wp_get_current_commenter();
	$options = get_option( XF_OPTIONS );
	$req = get_option( 'require_name_email' );
	if ( true === $options['xc_general']['aria_required']['value'] && $req ) {
		$aria_req = " aria-required='true'";
	}
	$email = 'text';
	$url = 'text';
	if ( xtreme_is_html5() ) {
		$email = 'email';
		$url = 'url';
	}
	$a = '<div class="ym-fbox-text">';
	$a .= '<label for="author">' . __( 'Name', XF_TEXTDOMAIN ) . ' ' . ( $req ? '<sup>*</sup>' : '' ) . '</label>';
	$a .= '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" tabindex="1"' . $aria_req . ' />';
	$a .= '</div>';
	$m = '<div class="ym-fbox-text">';
	$m .= '<label for="email">' . __( 'Email', XF_TEXTDOMAIN ) . ' ' .( $req ? '<sup>*</sup>' : '' ) .'</label>';
	$m .= '<input id="email" name="email" type="' . $email .'" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" tabindex="2"' . $aria_req . ' />';
	$m .= '</div>';
	$u = '<div class="ym-fbox-text">';
	$u .= '<label for="url">' . __( 'Website', XF_TEXTDOMAIN ) . '</label>';
	$u .= '<input id="url" name="url" type="' . $url . '" value="' . esc_url( $commenter['comment_author_url'] ) . '" size="30" tabindex="3" />';
	$u .= '</div>';
	$args = array(
		'author' => $a,
		'email' => $m,
		'url' => $u
	);
	return $args;
}
add_filter( 'comment_form_default_fields', 'xtreme_comment_default_fields' );

function xtreme_comment_form_textarea( $args ) {
	
	$options = get_option( XF_OPTIONS );
	
	$aria_req = '';
	if ( TRUE === $options['xc_general']['aria_required']['value'] ) {
		$aria_req = " aria-required='true'";
	}
	/**
	 * label text comes from Backend Xtreme One -> Settings -> Comment -> Label title
	 * @since 1.6.3
	 * @link https://github.com/inpsyde/xtreme-one/issues/191
	 */
	$label_text = isset( $options['xc_comments']['label_text']['value'] ) ? $options['xc_comments']['label_text']['value'] : __( 'Your Comment', XF_TEXTDOMAIN );
	$msg  = '<div class="comment-form-comment ym-fbox-text">';
	$msg .= '<label for="comment">' . $label_text . '</label>';
	$msg .= '<textarea id="comment" name="comment" cols="45" rows="10" tabindex="4" ' . $aria_req . '></textarea>';
	$msg .= '</div>';
	
	$args['comment_field'] = $msg;
	
	return $args;
}
add_filter( 'comment_form_defaults', 'xtreme_comment_form_textarea' );
// add_filter( 'comment_form_field_comment', 'xtreme_comment_form_textarea' );

function xtreme_comment_form_startdiv() {
	$html = "";
	$options = get_option( XF_OPTIONS );
	$value = ( int ) $options['xc_comments']['form_class']['value'];
	switch( $value ) {
		case 0:
			$class = 'linearize-form';
			break;
		case 1:
			$class = 'ym-columnar linearize-form';
			break;
		case 2:
			$class = 'ym-full';
			break;
	}
	$html = "<div class='ym-form " . $class . "'>\n";
	echo $html;
	do_action('xtreme_comment_form_top');
}
add_action( 'comment_form_top', 'xtreme_comment_form_startdiv', 0 );

function xtreme_comment_form_enddiv() {
	do_action('xtreme_comment_form_bottom');
	echo '</div>';
}
add_action( 'comment_form', 'xtreme_comment_form_enddiv', 99 );

/* hook between input url and textarea */
function xtreme_comment_form_after_fields() {
	$options = get_option( XF_OPTIONS );
	$txt = wp_filter_post_kses( $options['xc_comments']['comment_notes_before_textarea']['value'] );
	if ( !empty ( $txt ) ) {
		echo '<p>' . $txt . '</p>';
	}
}
add_action( 'comment_form_after_fields', 'xtreme_comment_form_after_fields' );

function xtreme_comment_form_defaults( $args ) {
	$options = get_option( XF_OPTIONS );
	$default_text = trim( $options['xc_comments']['comment_notes_default']['value'] );
	$add_text = trim( $options['xc_comments']['comment_notes_before_add']['value'] );

	$args['title_reply'] = esc_attr( $options['xc_comments']['title_reply']['value'] );
	if ( !empty ( $add_text ) ) {
		$args['comment_notes_before'] = '<p>' . esc_attr( $default_text ) . '<br />';
		$args['comment_notes_before'] .= esc_attr( $add_text ) . '</p>';
	} else {
		$args['comment_notes_before'] = '<p>' . esc_attr( $default_text ) . '</p>';
	}
	if ( false === $options['xc_comments']['allowed_tags']['value'] ) {
		$args['comment_notes_after'] = '';
	}
	$args['label_submit'] = esc_attr( $options['xc_comments']['label_submit']['value'] );
	$args['cancel_reply_link'] = esc_attr( $options['xc_comments']['cancel_reply_link']['value'] );
	return $args;
}
add_filter( 'comment_form_defaults', 'xtreme_comment_form_defaults' );

function xtreme_comments_avatar_size() {
	$options = get_option( XF_OPTIONS );
	return absint( $options['xc_comments']['avatar_size']['value'] );
}

function xtreme_show_avatar() {
	$options = get_option( XF_OPTIONS );
	return ( bool ) $options['xc_comments']['show_avatar']['value'];
}

function xtreme_avatar_align() {
	$options = get_option( XF_OPTIONS );
	return esc_attr( $options['xc_comments']['avatar_align']['value'] );
}
