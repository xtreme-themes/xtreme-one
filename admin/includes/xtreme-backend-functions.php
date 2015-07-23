<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

function xtreme_backend_header() {
	$str = '';
	$child = function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme();
	if ($child !== 'Xtreme One') {
		$str = sprintf(__('and %s.' , XF_TEXTDOMAIN), $child);
	}else{
		$str = __('without any child theme (not recommended).', XF_TEXTDOMAIN);
	}
?>
<div class="xtreme-header">
	<a href="https://github.com/xtreme-themes/xtreme-one" title="Xtreme Premium WordPress Themes">
		<img src="<?php echo XF_ADMIN_URL; ?>/images/xtreme-logo.png" alt="Xtreme One Logo" class="alignleft logo"/>
	</a>
	<ul>
		<li class="home"><a href="https://github.com/xtreme-themes/xtreme-one" title="<?php _e('Theme Homepage', XF_TEXTDOMAIN) ?>"><?php _e('Theme Homepage', XF_TEXTDOMAIN) ?></a></li>
		<li class="docu"><a href="<?php echo _x( 'https://github.com/xtreme-themes/xtreme-one/wiki', 'Documentation Link for Xtreme', XF_TEXTDOMAIN ); ?>" title="<?php _e('Documentation', XF_TEXTDOMAIN) ?>"><?php _e('Documentation', XF_TEXTDOMAIN) ?></a></li>
		<li class="version"></li>
	</ul>
	</div>
<h4 class="version"><?php printf(__('You are using Xtreme One Version %1s %2s', XF_TEXTDOMAIN ), XF_VERSION, esc_attr($str) ) ?></h4>
<?php
	$version = xtreme_get_production_stylesheet_version();
	if( $version !== '' ):
		echo "<p><em>";
		printf(
			__( 'Your Caching-File was regenerated at %s', XF_TEXTDOMAIN ),
			date( 'd.m.Y h:i', $version )
		);
		echo "</em></p>";
	endif;
?>
<br class="clear" />
	<?php
}

function xtreme_description_array() {
	$desc = array(
		__('1 column', XF_TEXTDOMAIN),
		__('2 columns - right sidebar', XF_TEXTDOMAIN),
		__('2 columns - left sidebar', XF_TEXTDOMAIN),
		__('3 columns - left and right sidebars', XF_TEXTDOMAIN),
		__('3 columns - 2 right sidebars', XF_TEXTDOMAIN),
		__('3 columns - 2 left sidebars', XF_TEXTDOMAIN)
	);
	return $desc;
}

function xtreme_classes_array() {
	$classes = array(
		'one',
		'two-right',
		'two-left',
		'three-left-right',
		'three-right-right',
		'three-left-left'
	);
	return $classes;
}

function xtreme_get_nav_menus() {
	$nav_menus = wp_get_nav_menus();
	$menus = array(
		'pages' => __('Pages', XF_TEXTDOMAIN),
		'categories' => __('Categories', XF_TEXTDOMAIN)
	);
	if (isset($nav_menus) && !empty($nav_menus)) {
		foreach ($nav_menus as $nav_menu) {
			$menus[$nav_menu->slug] = $nav_menu->name;
		}
	}
	return $menus;
}

function xtreme_left_center_right() {
	$pos = array(
		'left' => __('left', XF_TEXTDOMAIN),
		'center' => __('center', XF_TEXTDOMAIN),
		'right' => __('right', XF_TEXTDOMAIN),
	);
	return $pos;
}

function xtreme_html5_tags() {
	$tags = array( 'aside', 'footer', 'header', 'nav', 'section' );
	return $tags;
}

/**
 * Returns the current Version of the production-min.css
 * @return  Integer
 */
function xtreme_get_production_stylesheet_version(){
	$live_file          = XF_ABS_OUTPUT_DIR_THEME_BASED . '/production-min.css';
	if( file_exists( $live_file ) ){
		$current_version    = filemtime( $live_file );
	}
	else {
		$current_version = '';
	}
	return $current_version;
}