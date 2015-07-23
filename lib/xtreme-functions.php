<?php

function xtreme_get_post_types() {
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'object' );
	if ( !$post_types ) {
		return false;
	} else {
		return $post_types;
	}
}

function xtreme_show_theme_notices() {
	echo sprintf(
		'<div class="error"><p><strong>Xtreme One</strong> ' . __( 'requires at least WordPress %s!', XF_TEXTDOMAIN ) . ' ' . __( 'Please upgrade your WordPress.', XF_TEXTDOMAIN ) . '</p></div>',
		XF_WP_MIN_VERSION
	);
}

function xtreme_get_content_files( $directory, $type ) {
	$dirs = array( STYLESHEETPATH . $directory , TEMPLATEPATH . $directory );
	$files = array();
	foreach ( $dirs as $dir ) {
		if ( is_dir ( $dir ) && is_readable ( $dir ) ) {
			$dh  = opendir ( $dir );
			while ( false !== ( $filename = readdir ( $dh ) ) ) {
				if ( preg_match ( "/(.*)\.$type$/", $filename, $hits ) ) {
					$files[] = $hits[1];
				}
			}
			closedir ( $dh );
		}
	}
	if ( isset( $files ) && !empty ( $files ) ) {
		$files = array_unique( $files );
		return $files;
	} else {
		return false;
	}
}

function xtreme_meta_description() {
	global $post;
	if ( !is_singular() ) {
		return;
	}
	$meta = strip_tags( $post->post_content );
	$meta = strip_shortcodes( $meta );
	$meta = preg_replace('/\s\s+/', ' ', $meta);
	$meta = trim($meta);
	if(!empty ($meta) || $meta !== '' ) {
		$meta = str_replace( array( "\n", "\r", "\t" ), ' ', $meta );
		$meta = substr( $meta, 0, 125 );
		echo "<meta name='description' content='" . esc_attr( $meta ) . "' />\n";
	}
}

function xtreme_title() {
	echo '<title>' . wp_title( '-', false, 'right' ) . get_bloginfo( 'name' ) . '</title>'."\n";
}
function xtreme_set_viewport() {

	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
}

function xtreme_meta_robots() {
	global $wp_query;
	if ( is_paged() || is_archive() || is_tax() || is_author() || is_search() || is_category() ) {
		echo '<meta name="robots" content="noindex, follow" />';
	}
}

function xtreme_wordpress_general() {
	
	$options = get_option( XF_OPTIONS );
	
	if ( ( int ) $options['xc_wordpress']['xtreme_title']['value'] === 0 ) {
		add_action( 'xtreme_meta','xtreme_title', 0 );
	}
	if ( isset( $options['xc_wordpress']['xtreme_set_viewport']['value'] ) && ( int ) $options['xc_wordpress']['xtreme_set_viewport']['value'] === 0 ) {
		add_action( 'xtreme_meta','xtreme_set_viewport', 0 );
	}
	if ( ( int ) $options['xc_wordpress']['xtreme_meta_description']['value'] === 0 ) {
		add_action( 'xtreme_meta', 'xtreme_meta_description', 1 );
	}
	if ( ( int ) $options['xc_wordpress']['xtreme_meta_robots']['value'] === 0 ) {
		add_action( 'xtreme_meta', 'xtreme_meta_robots', 3 );
	}
	if ( ( int ) $options['xc_wordpress']['feed_links_extra']['value'] === 1 ) {
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}
	if ( ( int ) $options['xc_wordpress']['feed_links']['value'] === 1 ) {
		remove_action( 'wp_head', 'feed_links', 2 );
	}
	if ( ( int ) $options['xc_wordpress']['rsd_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'rsd_link' );
	}
	if ( ( int ) $options['xc_wordpress']['wlwmanifest_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}
	if ( ( int ) $options['xc_wordpress']['index_rel_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'index_rel_link' );
	}
	if ( ( int ) $options['xc_wordpress']['parent_post_rel_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	}
	if ( ( int ) $options['xc_wordpress']['start_post_rel_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	}
	if ( ( int ) $options['xc_wordpress']['adjacent_posts_rel_link']['value'] === 1 ) {
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
		add_filter( 'previous_post_rel_link', 'xtreme_return_false' );
		add_filter( 'next_post_rel_link', 'xtreme_return_false' );
	}
	if ( ( int ) $options['xc_wordpress']['wp_generator']['value'] === 1 ) {
		remove_action( 'wp_head', 'wp_generator' );
	}
	if ((int) $options['xc_wordpress']['wp_shortlink_wp_head']['value'] === 1) {
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	}
}
add_action( 'init', 'xtreme_wordpress_general' );

function xtreme_return_false( $data ) {
	return false;
}
