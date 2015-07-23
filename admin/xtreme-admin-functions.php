<?php

function xtreme_dashboard_rss_widget() {
	
	$url  = 'https://github.com/xtreme-themes/xtreme-one/releases.atom';
	$news = fetch_feed( $url );
	
	if ( is_wp_error($news) ) {
		if ( is_admin() || current_user_can('manage_options') ) {
			echo '<p>';
			printf(__('<strong>RSS Error</strong>: %s', XF_TEXTDOMAIN), $news->get_error_message());
			echo '</p>';
		}
		return;
	}
	
	if ( ! $news->get_item_quantity() ) {
		echo '<p>' . __( 'An error has occurred; the feed is probably down. Try again later.', XF_TEXTDOMAIN ) . '</p>';
		return;
	}
	
	echo '<ul>';
	foreach ( $news->get_items(0, 5) as $item ) {
		$link = $item->get_link();
		while ( stristr($link, 'http') != $link )
			$link = substr($link, 1);
			$link = esc_url(strip_tags($link));
		$title = esc_attr(strip_tags($item->get_title()));

		if ( empty($title) ){
			$title = __('Untitled', XF_TEXTDOMAIN);
		}
		$desc = str_replace(array("\n", "\r"), ' ', esc_attr(strip_tags(@html_entity_decode($item->get_description(), ENT_QUOTES, get_option('blog_charset')))));
		$desc = wp_html_excerpt( $desc, 360 ) . ' [&hellip;]';
		$desc = esc_html( $desc );
		$summary = "<div class='rssSummary'>$desc</div>";
		$date = $item->get_date();
		if ( $date ) {
			if ( $date_stamp = strtotime( $date ) ){
				$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
			}else{
				$date = '';
			}
		}

		if ( $link == '' ) {
			echo "<li>$title{$date}{$summary}{$author}</li>";
		} else {
			echo "<li><a class='rsswidget' href='$link' title='$desc'>$title</a>{$date}{$summary}</li>";
		}
	}
	echo '</ul>';
}

function xtreme_dashboard_rss_widget_setup() {
	
	wp_add_dashboard_widget( 'xtreme_dashboard_rss_widget', __('The Latest News From Xtreme Themes', XF_TEXTDOMAIN), 'xtreme_dashboard_rss_widget' );
}

function xtreme_favicon_for_admin() {
	
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . XF_ADMIN_URL . '/images/favicon.ico" />';
}

/**
 * adds a default-gravatar
 * 
 */
function xtreme_add_gravatar( $avatar_defaults ) {
	
	$xavatar = xtreme_locate_file_from_uri( array('images/xtreme-avatar.png') );
	$avatar_defaults[$xavatar] = __('Xtreme Default Avatar', XF_TEXTDOMAIN);
	
	return $avatar_defaults;
}
