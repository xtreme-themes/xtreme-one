<?php

if ( ! function_exists( 'xtreme_post_pagination' ) ) {
	function xtreme_post_pagination() {

		global $wp_query;

		$options = get_option( XF_OPTIONS );
		$start   = '<div class="pagination">';
		$end     = '</div>';
		if ( xtreme_is_html5() ) {
			$start = '<nav class="pagination">';
			$end   = '</nav>';
		}

		echo $start;

		$previous_string = (isset($options['xc_pagination']['previous_string']['value']) ? $options['xc_pagination']['previous_string']['value'] : __( 'Newer Posts <span>&rarr;</span>', XF_TEXTDOMAIN ) );
		$next_string     = (isset($options['xc_pagination']['next_string']['value']) ? $options['xc_pagination']['next_string']['value'] : __( '<span>&larr;</span> Older Posts', XF_TEXTDOMAIN ) );

		if ( ! isset($options['xc_pagination']) || $options['xc_pagination']['pagination_type']['value'] != 1) { ?>
			<div class="previous_link"><?php previous_posts_link( $previous_string ) ?></div>
			<div class="next_link"><?php next_posts_link( $next_string ) ?></div>
		<?php } else {

			$type = (isset($options['xc_pagination']['type']['value']) ? $options['xc_pagination']['type']['value'] : 'plain');
			$big = 999999999; // need an unlikely integer
			$page_links = paginate_links( array(
			                                   'base'      => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
			                                   'format'    => '?paged=%#%',
			                                   'current'   => max( 1, get_query_var('paged') ),
			                                   'total'     => $wp_query->max_num_pages,
			                                   'end_size'  => (isset($options['xc_pagination']['end_size']['value']) ? (int)$options['xc_pagination']['end_size']['value'] : 1),
			                                   'mid_size'  => (isset($options['xc_pagination']['mid_size']['value']) ? (int)$options['xc_pagination']['mid_size']['value'] : 2),
			                                   'show_all'  => (isset($options['xc_pagination']['show_all']['value']) ? (bool)$options['xc_pagination']['show_all']['value'] : false),
			                                   'prev_next' => (isset($options['xc_pagination']['prev_next']['value']) ? (bool)$options['xc_pagination']['prev_next']['value']: true),
			                                   'type'      => 'array'
			                              ) );
			$page_links = apply_filters( 'xtreme_post_pagination', $page_links );
			if ( is_array( $page_links ) ) {
				switch( $type ) {
					case 'list':
						$r = "<ul class='page-numbers'>\n\t<li>";
						$r .= join( "</li>\n\t<li>", $page_links );
						$r .= "</li>\n</ul>\n";
						break;
					default:
						$r = join( "\n", $page_links );
						break;
				}
			} else {
				$r = (string)$page_links;
			}
			echo $r;
		}
		echo $end;
	}
}

function xtreme_image_pagination() {
	$start = '<div class="pagination image">';
	$end = '</div>';
	if ( xtreme_is_html5() ) {
		$start = '<nav class="pagination image">';
		$end = '</nav>';
	}
	echo $start ?>
	<div class="previous_link"><?php previous_image_link( 'thumbnail' ) ?></div>
	<div class="next_link"><?php next_image_link( 'thumbnail' ) ?></div>
	<?php echo $end;
}

function xtreme_single_pagination() {
	$start = '<div class="pagination">';
	$end = '</div>';
	if ( xtreme_is_html5() ) {
		$start = '<nav class="pagination">';
		$end = '</nav>';
	}
	echo $start ?>
	<div class="previous_link"><?php previous_post_link('%link <span>&rarr;</span>', '%title') ?></div>
	<div class="next_link"><?php next_post_link('<span>&larr;</span> %link', '%title' ) ?></div>
	<?php echo $end;
}
add_action( 'xtreme_after_comments_template', 'xtreme_single_pagination' );

function xtreme_not_found() {
	locate_template( array( 'includes/notfound.php' ), true );
}

/**
 * xtreme custom excerpt
 * @param int $length
 * @param string $more_link_text
 * @param string $more
 * @param bool $echo
 * @param bool $show_tags
 *
 * @return string $output
 */
function xtreme_excerpt( $length = 55, $more_link_text = '', $more = '...', $echo = true, $show_tags = false ) {
	global $post;
	$read_more = "";
	$more = esc_html( $more );

	if ( $more_link_text ) {
		$read_more = " <span class='read-more'><a href='" . get_permalink() . "' title='" . __( 'Read more about', XF_TEXTDOMAIN ) . ' ' . esc_attr( get_the_title() ) . "'>" . esc_html( $more_link_text ) . "</a></span>";
	}
	if ( post_password_required( $post ) ) {
		$output = __( 'There is no excerpt because this is a protected post.', XF_TEXTDOMAIN );
		return $output;
	}
	if (current_theme_supports('xtreme-make-excerpts-wp-compatible') && ('' != $post->post_excerpt)) {
		$kind_of_excerpt = get_theme_support('xtreme-make-excerpts-wp-compatible');
		$kind_of_excerpt = $kind_of_excerpt[0];
		$do_more = false;
		$raw_excerpt = $post->post_excerpt;
		switch($kind_of_excerpt) {
			case 'explicit_no_readmore':
				$raw_excerpt = preg_replace('/<!--more(.*?)?-->/', '', $raw_excerpt);
				break;
			case 'explicit_detect_readmore':
				if (preg_match('/<!--more(.*?)?-->/', $raw_excerpt)) $do_more = true;
				break;
			case 'explicit_auto_readmore':
			default:
				if (!preg_match('/<!--more(.*?)?-->/', $raw_excerpt)) $raw_excerpt .= '<!--more-->';
				$do_more = true;
				break;
		}

		$output = apply_filters('wp_trim_excerpt', $post->post_excerpt, $raw_excerpt);
		$output = force_balance_tags($output);
		if ($do_more) {
			if (empty($more_link_text)) $more_link_text = __( 'Read more about', XF_TEXTDOMAIN );
			$output .= apply_filters( 'the_content_more_link', ' <span class=\'read-more\'><a href="' . get_permalink() . "\" class=\"more-link\">$more_link_text</a></span>", $more_link_text );
		}
		$output = '<p>' . $output . '</p>';
		if ( $echo === true ) {
			echo $output;
			return '';
		} else {
			return $output;
		}
	}
	$length = absint( $length );
	$output = get_the_content( '' );
	$output = strip_shortcodes( $output );
	$output = apply_filters( 'the_content', $output );
	$output = str_replace( ']]>', ']]&gt;', $output );
	if( !$show_tags ) {
		$output = strip_tags( $output );
	}
	$excerpt_length = min( max( 5, $length ), 80 );
	$words = preg_split( "/[\n\r\t ]+/", $output, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );

	if ( count( $words ) > $excerpt_length ) {
		array_pop( $words );
		array_push( $words, $more );
		$output = implode( ' ', $words );
	} else {
		$output = implode( ' ', $words );
	}
	$output = apply_filters( 'wptexturize', $output );
	$output = apply_filters( 'convert_chars', $output );
	$output = $output . ' ' . $read_more;
	$output = '<p>' . $output . '</p>';
	if ( $echo === true ) {
		echo $output;
	} else {
		return $output;
	}
}

function xtreme_author_box() {
	$str = "";
	$stra = "";
	$el = 'div';
	$photo = 'div';
	if ( xtreme_is_html5() ) {
		$el = 'section';
		$photo = 'figure';
	}
	if ( get_the_author_meta( 'description' ) ) {
		$str .= "<" . $el . " class='vcard' id='authorbox'>";
		$str .= "<" . $photo . " class='avatar photo'>";
		$str .= get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'xtreme_authorbox_avatar_size', '60' ) );
		$str .= "</" . $photo . ">";
		$name = "<span class='nickname'>" . get_the_author() . "</span>";
		$str .= "<h3 class='fn n'>".__( 'About', XF_TEXTDOMAIN ) . " " . $name . "</h3>";
		$str .= "<p class='note'>" . get_the_author_meta( 'description' ) . "</p>";
		$stra .= "<p class='url fn'>";
		$stra .= "<a href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "' title='" . sprintf( esc_attr__( 'View all posts by %s', XF_TEXTDOMAIN ), get_the_author() ) . "'>";
		$stra .= sprintf( esc_attr__( 'View all posts by %s', XF_TEXTDOMAIN ), $name ) . " <span class='sign'>&rarr;</span></a>";
		$stra .= "</p>";
		$stra .= "</" . $el . ">";
	}
	echo $str;
	do_action( 'xtreme_authorbox_inside_vcard' );
	echo $stra;
}
add_action('xtreme_after_single_post', 'xtreme_author_box', 2 );

if ( !function_exists( 'xtreme_archive_headline' ) ) {
	function xtreme_archive_headline() {
		if ( is_day() ) :
			$headline = sprintf( __( 'Daily Archives for <span>%s</span>', XF_TEXTDOMAIN ), get_the_date() );
		elseif ( is_month() ) :
			$headline = sprintf( __( 'Monthly Archives for <span>%s</span>', XF_TEXTDOMAIN ), get_the_date('F Y') );
		elseif ( is_year() ) :
			$headline = sprintf( __( 'Yearly Archives for <span>%s</span>', XF_TEXTDOMAIN ), get_the_date('Y') );
		else :
			$headline = __( 'Archive', XF_TEXTDOMAIN );
		endif;
		echo '<h3 class="page-title">' . $headline . '</h3>';
	}
}
add_action( 'xtreme_archive_title', 'xtreme_archive_headline' );

if ( !function_exists( 'xtreme_search_headline' ) ) {
	function xtreme_search_headline() {
		$headline = sprintf( __( 'Search Results for: %s', XF_TEXTDOMAIN ), '<span>' . get_search_query() . '</span>' );
		echo '<h3 class="page-title">' . $headline  . '</h3>';
	}
}
add_action( 'xtreme_search_title', 'xtreme_search_headline' );

if ( !function_exists( 'xtreme_category_headline' ) ) {
	function xtreme_category_headline() {
		$headline = sprintf( __( 'Category %s', XF_TEXTDOMAIN ), '<span>' . single_cat_title( '', false ) . '</span>' );
		echo '<h3 class="page-title">' . $headline . '</h3>';
	}
}
add_action('xtreme_category_title', 'xtreme_category_headline');

if (!function_exists('xtreme_author_headline')) {
	function xtreme_author_headline() {
		$headline = sprintf( __( 'All posts by <span>%s</span>', XF_TEXTDOMAIN ), get_the_author() );
		echo '<h3 class="page-title">' . $headline . '</h3>';
	}
}
add_action( 'xtreme_author_title', 'xtreme_author_headline' );

if ( !function_exists('xtreme_post_meta' ) ) {
	function xtreme_post_meta() {
		$tags = get_the_tag_list( '', ', ' );
		$cats = get_the_category_list( ', ' );
		echo '<p class="post-meta"><span class="category">' . sprintf( __( 'Filed under: %s', XF_TEXTDOMAIN ), $cats ) . '</span>';
		if ( !empty( $tags ) ) echo '&nbsp;|&nbsp;<span class="tag">' . sprintf( __( 'Tagged with: %s', XF_TEXTDOMAIN), $tags ) . '</span>';
		echo '</p>';
	}
}


if ( !function_exists('xtreme_html5_post_meta' ) ) {
	function xtreme_html5_post_meta() {
		$tags = get_the_tag_list( '', ', ' );
		$cats = get_the_category_list( ', ' );
		echo '<footer class="post-meta"><span class="category">' . sprintf( __( 'Filed under: %s', XF_TEXTDOMAIN ), $cats ) . '</span>';
		if ( !empty( $tags ) ) echo '&nbsp;|&nbsp;<span class="tag">' . sprintf( __( 'Tagged with: %s', XF_TEXTDOMAIN), $tags ) . '</span>';
		echo '</footer>';
	}
}

if ( !function_exists( 'xtreme_byline' ) ) {
	function xtreme_byline() {
		?>
		<div class="entry-meta">
			<span class="postdate published"><?php echo get_the_date()  ?></span>&nbsp;
			<span class="postauthor"><?php esc_attr_e('by', XF_TEXTDOMAIN) ?>&nbsp;<?php the_author_posts_link() ?></span>
			<?php if(comments_open()) : ?>
				&nbsp;|&nbsp;<span class="postcomments"><?php comments_popup_link( __('no Comments', XF_TEXTDOMAIN), __('1 Comment', XF_TEXTDOMAIN), __('% Comments', XF_TEXTDOMAIN), '', __('off', XF_TEXTDOMAIN) ) ?></span>
			<?php endif ?>
		</div>
	<?php
	}
}

if ( !function_exists( 'xtreme_html5_byline' ) ) {
	function xtreme_html5_byline() {
		?>
		<div class="entry-meta">
			<time datetime="<?php echo get_the_date('c') ?>" pubdate><?php echo get_the_date() ?></time>&nbsp;|&nbsp;
			<span class="postauthor"><?php esc_attr_e('by', XF_TEXTDOMAIN) ?>&nbsp;<?php the_author_posts_link() ?></span>
			<?php if(comments_open()) : ?>
				&nbsp;|&nbsp;<span class="postcomments"><?php comments_popup_link( __('no Comments', XF_TEXTDOMAIN), __('1 Comment', XF_TEXTDOMAIN), __('% Comments', XF_TEXTDOMAIN), '', __('off', XF_TEXTDOMAIN) ) ?></span>
			<?php endif ?>
			<?php edit_post_link( __( 'Edit', XF_TEXTDOMAIN ), '&nbsp;|&nbsp;<span class="postedit">', '</span>' ) ?>
		</div>
	<?php
	}
}

if ( ! function_exists( 'xtreme_post_headline' ) ) {
	/**
	 * Get headline, the title of posts
	 *
	 * @param  $tag  String
	 * @param  $link Boolean
	 * @param  $echo Boolean
	 * @return void
	 */
	function xtreme_post_headline( $tag = 'h2', $link = TRUE, $echo = TRUE ) {

		$default_allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		// Hook for change the allowed tags
		$allowed_tags         = apply_filters( 'xtreme_allowed_tags_post_headline', $default_allowed_tags );
		$open_link    = '';
		$close_link   = '';

		if ( ! in_array( $tag, $allowed_tags ) )
			$tag = 'h2';

		if ( $link ) {
			$open_link = sprintf( '<a href="%s" rel="bookmark" title="' . esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ) . '">', get_permalink(), the_title_attribute( 'echo=0' ) );
			$close_link = '</a>';
		}

		do_action( 'xtreme_before_post_headline' );

		$output =  sprintf( '<%1$s class="posttitle">%3$s%2$s%4$s</%1$s>', $tag, get_the_title(), $open_link, $close_link );
		if ( $echo )
			echo $output;
		else
			return $output;

		do_action( 'xtreme_after_post_headline' );
	}
}

if ( ! function_exists( 'xtreme_post_subtitle' ) ) {
	/**
	 * Get subtitle, the additional subtitle of posts
	 *
	 * @param  $tag  String
	 * @param  $link Boolean
	 * @param  $echo Boolean
	 * @return void
	 */
	function xtreme_post_subtitle( $tag = 'h4', $link = FALSE, $echo = TRUE ) {

		if ( ! current_theme_supports( 'xtreme-subtitles' ) )
			return;

		$pt  = get_post_type( get_the_ID() );
		$spt = get_theme_support( 'xtreme-subtitles' );
		if ( ! in_array( $pt, $spt[0]) )
			return;

		if ( ! has_subtitle() )
			return;

		$default_allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' );
		// Hook for change the allowed tags
		$allowed_tags         = apply_filters( 'xtreme_allowed_tags_post_subtitle', $default_allowed_tags );
		$open_link    = '';
		$close_link   = '';

		if ( ! in_array( $tag, $allowed_tags ) )
			$tag = 'h4';

		if ( $link ) {
			$open_link  = sprintf(
				'<a href="%s" rel="bookmark" title="' . esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ) . '">',
				get_permalink(),
				the_title_attribute( 'echo=0' )
			);
			$close_link = '</a>';
		}

		do_action( 'xtreme_before_post_subtitle' );

		$output = sprintf( '<%1$s class="subtitle">%3$s%2$s%4$s</%1$s>', $tag, get_the_subtitle(), $open_link, $close_link );
		if ( $echo )
			echo $output;
		else
			return $output;

		do_action( 'xtreme_after_post_subtitle' );
	}
}

/**
 * Return headline and subtitle
 *
 * @param  Array $args, see $defaults
 * @return void
 */
function xtreme_post_titles( $args = array() ) {

	// default values
	$defaults = array(
		'order'         => 'ASC', // or DESC
		'headline_tag'  => 'h2',  // String for Markup
		'headline_link' => TRUE,  // Boolean
		'subtitle_tag'  => 'h4',  // String for Markup
		'subtitle_link' => FALSE  // Boolean
	);

	// set a filter for custom settings via plugin-, theme-function
	$args = wp_parse_args(
		$args,
		apply_filters( 'xtreme_post_titles_args', $defaults )
	);

	// control input for parameters
	$allowed_orders = array( 'ASC', 'DESC' );
	if ( ! in_array( $args['order'], $allowed_orders ) )
		$args['order'] = 'ASC';

	if ( 'ASC' === $args['order'] ) {
		xtreme_post_headline( $args['headline_tag'], $args['headline_link'] );
		xtreme_post_subtitle( $args['subtitle_tag'], $args['subtitle_link'] );
	} else {
		xtreme_post_subtitle( $args['subtitle_tag'], $args['subtitle_link'] );
		xtreme_post_headline( $args['headline_tag'], $args['headline_link'] );
	}

}

if ( ! function_exists( 'xtreme_widget_post_headline' ) ) {
	/**
	 * Get headline, the title of posts, extra for widgets
	 *
	 * @param  $tag  String
	 * @param  $link Boolean
	 * @param  $echo Boolean
	 * @return void
	 */
	function xtreme_widget_post_headline( $tag = 'h2', $link = TRUE, $echo = TRUE, $custom_posttitle = '' ) {

		$default_allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		// Hook for change the allowed tags
		$allowed_tags         = apply_filters( 'xtreme_widget_allowed_tags_post_headline', $default_allowed_tags );
		$open_link    = '';
		$close_link   = '';

		if ( ! in_array( $tag, $allowed_tags ) )
			$tag = 'h2';

		if ( $link ) {
			$open_link = sprintf( '<a href="%s" rel="bookmark" title="' . esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ) . '">', get_permalink(), the_title_attribute( 'echo=0' ) );
			$close_link = '</a>';
		}

		do_action( 'xtreme_widget_before_post_headline' );

		$output =  sprintf( '<%1$s class="posttitle">%3$s%2$s%4$s</%1$s>', $tag, ( $custom_posttitle != '' ? $custom_posttitle : get_the_title() ), $open_link, $close_link );
		if ( $echo )
			echo $output;
		else
			return $output;

		do_action( 'xtreme_widget_after_post_headline' );
	}
}

if ( ! function_exists( 'xtreme_widget_post_subtitle' ) ) {
	/**
	 * Get subtitle, the additional title of posts, extra for widgets
	 *
	 * @param  $tag  String
	 * @param  $link Boolean
	 * @param  $echo Boolean
	 * @return void
	 */
	function xtreme_widget_post_subtitle( $tag = 'h4', $link = FALSE, $echo = TRUE ) {

		if ( ! current_theme_supports( 'xtreme-subtitles' ) )
			return;

		$pt  = get_post_type( get_the_ID() );
		$spt = get_theme_support( 'xtreme-subtitles' );
		if ( ! in_array( $pt, $spt[0]) )
			return;

		if ( ! has_subtitle() )
			return;

		$default_allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' );
		// Hook for change the allowed tags
		$allowed_tags         = apply_filters( 'xtreme_widget_allowed_tags_post_subtitle', $default_allowed_tags );
		$open_link    = '';
		$close_link   = '';
		if ( ! in_array( $tag, $allowed_tags ) )
			$tag = 'h4';

		if ( $link ) {
			$open_link  = sprintf(
				'<a href="%s" rel="bookmark" title="' . esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ) . '">',
				get_permalink(),
				the_title_attribute( 'echo=0' )
			);
			$close_link = '</a>';
		}

		do_action( 'xtreme_widget_before_post_subtitle' );

		$output = sprintf( '<%1$s class="subtitle">%3$s%2$s%4$s</%1$s>', $tag, get_the_subtitle(), $open_link, $close_link );
		if ( $echo )
			echo $output;
		else
			return $output;

		do_action( 'xtreme_widget_after_post_subtitle' );
	}
}

if ( ! function_exists( 'xtreme_post_thumbnail' ) ) {
	function xtreme_post_thumbnail( $size = 'thumbnail', $align = 'alignleft' ) {

		$allowed = ($size !== 'none') ? TRUE : FALSE;

		if ( $allowed && has_post_thumbnail() ) {
			$title = sprintf(
				esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ),
				the_title_attribute( 'echo=0' )
			);
			$classes = array( 'xf-thumbnail', esc_attr( $size ), esc_attr( $align ) );
			$class = implode( ' ', $classes );
			?>
			<a class="<?php echo $class; ?>" href="<?php the_permalink() ?>" rel="bookmark" title="<?php echo $title; ?>">
				<?php
				do_action( 'xtreme_before_post_thumbnail' );
				the_post_thumbnail( esc_attr( $size ) );
				do_action( 'xtreme_after_post_thumbnail' );
				?>
			</a>
		<?php }
	}
}
/**
 * Get postthumbnail without a link, extra for widgets
 *
 * @param  $size  String
 * @param  $align String
 * @param  $echo Boolean
 * @return void
 * @since 1.5.5
 */
if ( ! function_exists( 'xtreme_widget_post_thumbnail' ) ) {
	function xtreme_widget_post_thumbnail( $size = 'thumbnail', $align = 'alignleft' ) {

		$allowed = ($size !== 'none') ? TRUE : FALSE;

		if ( $allowed && has_post_thumbnail() ) {
			$title = sprintf(
				esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ),
				the_title_attribute( 'echo=0' )
			);
			$classes = array( 'xf-thumbnail', esc_attr( $size ), esc_attr( $align ) );
			$class = implode( ' ', $classes );
			?>
			<?php
			do_action( 'xtreme_before_post_thumbnail' );
			the_post_thumbnail( esc_attr( $size ) );
			do_action( 'xtreme_after_post_thumbnail' );
			?>
		<?php }
	}
}
function xtreme_background_post_thumbnail( $size = 'thumbnail', $align = 'alignleft', $txt = '' ) {
	$default_txt = __( 'Read more...', XF_TEXTDOMAIN );
	$allowed = ( $size !== 'none' ) ? true : false;
	if ( $allowed && has_post_thumbnail() ) {
		$pic = wp_get_attachment_image_src( get_post_thumbnail_id() , $size );
		if ( !empty( $txt ) || $txt != '') {
			$default_txt = $txt;
		}
		do_action( 'xtreme_before_background_post_thumbnail' );
		echo '<a class="xf-image-wrap ' . esc_attr( $align ) . '"';
		echo ' style="background: url(' . esc_url( $pic[0] ) . ') no-repeat center center; width:' . absint( $pic[1] ) . 'px; height:' . absint( $pic[2] ) . 'px;"';
		echo ' href="' . esc_url( get_permalink() ) . '" rel="bookmark" title="' . sprintf( esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ) . '">';
		echo '<span>' . $default_txt . '</span>';
		echo '</a>';
		do_action( 'xtreme_after_background_post_thumbnail' );
	}
}

function xtreme_post_class() {
	global $postcounter;
	$cls = '';
	if ( current_theme_supports( 'post-formats' ) ) {
		$cls = 'xf-postformat';
	}
	echo join (' ', get_post_class( array( 'xf-post-' . $postcounter, $cls ) ) );
}

function xtreme_post_format_icon( $tag, $title ) {
	if ( current_theme_supports( 'post-formats' ) ) {
		$tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span');
		if ( !in_array( $tag, $tags ) ) {
			$tag = 'h3';
		}
		if ( empty ( $title ) ) {
			$title = __( 'Standard', XF_TEXTDOMAIN );
		}
		$f = get_post_format();
		if ( empty( $f ) ) {
			$f = 'standard';
		}
		$open_link = sprintf( '<a class="post-format-icon icon-%1$s" href="%2$s" rel="bookmark" title="%3$s">', $f, get_permalink(), sprintf( esc_attr__( 'Permalink to %s', XF_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ) );
		$close_link = '</a>';
		printf( '<%1$s class="entry-format">%2$s%3$s%4$s</%1$s>', $tag, $open_link, $title, $close_link );
	}
}