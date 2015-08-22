<?php

global $wp_version;

define('XF_VERSION', '1.7.1');
define('XF_WP_MIN_VERSION', '3.8');

define('XF_THEME_DIR', apply_filters('template_directory', get_template_directory()));
define('XF_THEME_URI', apply_filters('template_directory_uri', get_template_directory_uri()));
define('XF_CHILD_THEME_DIR', apply_filters('stylesheet_directory', get_stylesheet_directory()));
define('XF_CHILD_THEME_URI', apply_filters('stylesheet_directory_uri', get_stylesheet_directory_uri()));
define('XF_ADMIN_DIR',  XF_THEME_DIR .'/admin');
define('XF_ADMIN_URL',  XF_THEME_URI .'/admin');
define('XF_WIDGETS_DIR', XF_THEME_DIR .'/widgets');
define('XF_WIDGETS_URL', XF_THEME_URI .'/widgets');

define('XF_LOW_BARRIER_CSS_EXISTS', file_exists(XF_CHILD_THEME_DIR.'/css/screen/low-barrier.css'));

define('XF_TEXTDOMAIN', 'xtreme-one');

define('XF_VERSION_FIELD', 'xtreme-one_version');
define('XF_UPDATE_PENDING', 'xtreme-one_updated');
define('XF_UPDATE_ERRORS', 'xtreme-one_errors');
define('XF_OPTIONS', 'xtreme-one');
define('XF_LAYOUTS', 'xtreme-one_layouts');
define('XF_TEMPLATES', 'xtreme-one_templates');
define('XF_WIDGET_PERMISSIONS', 'xtreme-one_widget-settings');
define('XF_WIDGET_BURNING_REG', 'xtreme-one_burning-reg');


define('XF_OUTPUT_DIR', XF_THEME_DIR .'/output');
define('XF_OUTPUT_URI', XF_THEME_URI .'/output');
define('XF_LANG_DIR', XF_THEME_DIR .'/languages');

define('XF_STICKY_HANDLING', version_compare( $wp_version, '3.1', '>=' ) ? 'ignore_sticky_posts' : 'caller_get_posts');
define('XF_METABOX_SLUG', 'xtreme-meta-box-');

define('XF_ADMIN_SCRIPTS', '.min.js');
define('XF_FRONT_SCRIPTS', '.min.js');

if (preg_match('/MSIE (\d+)\.(\d+)/',$_SERVER['HTTP_USER_AGENT'], $version)){
	define('XF_IS_IE', true);
	define('XF_IE_MAJOR', $version[1]);
	define('XF_IE_MINOR', $version[2]);
}else{
	define('XF_IS_IE', false);
	define('XF_IE_MAJOR', 0);
	define('XF_IE_MINOR', 0);
}

load_theme_textdomain(XF_TEXTDOMAIN, XF_LANG_DIR, 'xtreme-one');

require_once XF_THEME_DIR . '/lib/xtreme-functions.php';

if ( !version_compare( $wp_version, XF_WP_MIN_VERSION, '>=' ) ) {
	update_option( 'template', 'default' );
	update_option( 'stylesheet', 'default' );
	delete_option( 'current_theme' );
	$theme = function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme();
	do_action( 'switch_theme', $theme );
	add_action( 'admin_notices', 'xtreme_show_theme_notices' );
}

define('XF_CURRENT_THEME_NAME', function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme());

add_theme_support( 'automatic-feed-links' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menus' );
add_theme_support( 'xtreme-advanced-tinymce' );

if ( !is_multisite() ) {
	define( 'XF_BLOG_ID', 1 );
	define( 'XF_SITE_ID', 1 );
} else {
	global $current_site, $current_blog;
	define( 'XF_BLOG_ID', $current_blog->blog_id);
	define( 'XF_SITE_ID', $current_site->blog_id);
}
define( 'XF_IS_MAIN_BLOG', XF_BLOG_ID == XF_SITE_ID ? true : false);

define('XF_IS_THEME_PREVIEW', isset($_GET['template']) && isset($_GET['preview']) && isset($_GET['stylesheet']) && current_user_can( 'switch_themes' ));

if (XF_IS_THEME_PREVIEW) {
	define('XF_ACTIVE_STYLESHEET', preg_replace('|[^a-z0-9_./-]|i', '', $_GET['stylesheet']));
} else {
	define('XF_ACTIVE_STYLESHEET', get_option('stylesheet'));
}

define('XF_ABS_OUTPUT_DIR_THEME_BASED',	XF_OUTPUT_DIR . '/' . XF_BLOG_ID . '/' . XF_ACTIVE_STYLESHEET);
define('XF_ABS_OUTPUT_URI_THEME_BASED',	XF_OUTPUT_URI . '/' . XF_BLOG_ID . '/' . XF_ACTIVE_STYLESHEET);
define('XF_REL_OUTPUT_DIR_THEME_BASED',	'/output' . '/' . XF_BLOG_ID . '/' . XF_ACTIVE_STYLESHEET);
define('XF_ABS_OUTPUT_DIR_COMPATIBILITY', is_dir(XF_ABS_OUTPUT_DIR_THEME_BASED) ? XF_ABS_OUTPUT_DIR_THEME_BASED : XF_OUTPUT_DIR . '/' . XF_BLOG_ID );
define('XF_ABS_OUTPUT_URI_COMPATIBILITY', is_dir(XF_ABS_OUTPUT_DIR_THEME_BASED) ? XF_ABS_OUTPUT_URI_THEME_BASED : XF_OUTPUT_URI . '/' . XF_BLOG_ID );
define('XF_REL_OUTPUT_DIR_COMPATIBILITY', is_dir(XF_ABS_OUTPUT_DIR_THEME_BASED) ? XF_ABS_OUTPUT_DIR_THEME_BASED : '/output' . '/' . XF_BLOG_ID );
define('XF_FTP_OUTPUT_DIR',	'/' . get_option('template') . XF_REL_OUTPUT_DIR_THEME_BASED);

require_once XF_THEME_DIR . '/lib/xtreme-theme-router.php';

require_once XF_THEME_DIR . '/lib/xtreme-template-loader.php';
require_once XF_THEME_DIR . '/lib/xtreme-template-functions.php';
require_once XF_THEME_DIR . '/lib/xtreme-navigation.php';
require_once XF_THEME_DIR . '/lib/xtreme-post-functions.php';
require_once XF_THEME_DIR . '/lib/xtreme-image-functions.php';
require_once XF_THEME_DIR . '/lib/xtreme-comment-functions.php';
require_once XF_THEME_DIR . '/lib/xtreme-javascripts.php';
require_once XF_THEME_DIR . '/lib/xtreme-widget-manager.php';
require_once XF_THEME_DIR . '/lib/xtreme-3rd-party-fixes.php';

if  ( is_admin() ) {
	require_once(XF_ADMIN_DIR . '/includes/xtreme-backend-functions.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-template-finder.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-options.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-metaboxes.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-metaboxes-default.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-metaboxes-layout.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-dialogs.php');
	require_once(XF_ADMIN_DIR . '/includes/xtreme-github-updater.php');

	require_once(XF_ADMIN_DIR . '/xtreme-admin-functions.php');
	require_once(XF_ADMIN_DIR . '/xtreme-backend.php');
	require_once(XF_ADMIN_DIR . '/xtreme-layouts.php');
	require_once(XF_ADMIN_DIR . '/xtreme-backup.php');
	require_once(XF_ADMIN_DIR . '/xtreme-widgets-admin.php');

	add_action('admin_head', 'xtreme_favicon_for_admin');
	add_action('wp_dashboard_setup', 'xtreme_dashboard_rss_widget_setup');
	add_filter('avatar_defaults', 'xtreme_add_gravatar');
}

require_if_theme_supports( 'xtreme-fancybox', XF_THEME_DIR . '/lib/xtreme-fancybox.php' );
require_if_theme_supports( 'xtreme-subtitles', XF_THEME_DIR . '/lib/xtreme-subtitles.php' );

