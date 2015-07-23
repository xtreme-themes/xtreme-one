<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Xtreme_backend {
	public $measures;
	public $metaboxes;
	public $requires_credentials;
	public $last_error;
	public $version;
	public $options;
	public $layouts;
	public $documentation_object;
	public $pagehook;


	function __construct() {
		$this->measures	= new xtreme_option_measures();
		$this->metaboxes = array();
		$this->requires_credentials = false;
		add_action('xtreme_setup_theme', array(&$this, 'on_setup_theme'));
		add_action('init',array(&$this, 'on_init'));
		add_action('admin_head', array(&$this, 'on_admin_head'));
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		add_action('load-theme-editor.php', array(&$this, 'on_load_theme_editor_php'));
		add_action('admin_post_save_xtreme_backend_settings', array(&$this, 'on_save_backend_settings'));
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		$this->last_error = false;
		if ( isset($_COOKIE['XTREME_ERROR']) ) {
			$this->last_error = new WP_Error('xtreme', $_COOKIE['XTREME_ERROR']);
			setcookie('XTREME_ERROR', '', time() - 3600, '/');
		}
		// add documentation menu at the bottom of the xtreme menu
		add_filter( 'admin_menu', array( $this, 'add_documentation_menu' ), 100 );
	}

	function get_measures() { return $this->measures; }
	
	function on_setup_theme() {
		//useful for conditional the requires
	}
	
	function on_init() {

		$this->metaboxes = xtreme_build_metaboxes(apply_filters('xtreme_metaboxes_default', array()), $this);

		$this->version = get_option( XF_VERSION_FIELD );
		$this->options = get_option( XF_OPTIONS , array() );
		$this->layouts = get_option( XF_LAYOUTS , array() );

		//templates done at different page but checked for consistency
		$templates = get_option( XF_TEMPLATES , array() );
		if (empty($templates)) $templates = array();
		$files = xtreme_load_templates();
		if ( count( $templates ) !== count( $files ) ) {
			foreach ( $files as $file => $data ) {
				if ( !array_key_exists( $file, $templates ) ) {
					$templates[$file] = 'xf_layout-default';
				}
			}
			update_option(XF_TEMPLATES, $templates);
		}

		$this->apply_options( $this->options );

		add_action('wp_ajax_xtreme_check_user_credentials', array(&$this, 'on_check_user_credentials'));
		
		if (current_theme_supports('post-thumbnails')) {
			add_filter( 'manage_posts_columns', array(&$this,'on_thumbnail_column') );
			add_filter( 'manage_pages_columns', array(&$this,'on_thumbnail_column') );
			add_action( 'manage_posts_custom_column', array(&$this, 'on_thumbnail_column_value'), 10, 2 );
			add_action( 'manage_pages_custom_column', array(&$this, 'on_thumbnail_column_value'), 10, 2 );		
		}
		if (is_admin()) {
			wp_enqueue_style('xtreme-hooks', XF_ADMIN_URL.'/css/xtreme-hooked.css');
		}
		
		global $wp_version;

		// advanced gallery legacy support
		if ( current_theme_supports( 'xtreme-advanced-wpgallery-legacy' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
			add_action( 'wp_enqueue_media', array( $this, 'wp_enqueue_media_advanced_gallery_legacy' ) );
			add_action( 'print_media_templates', array( $this, 'print_media_templates_advanced_gallery_legacy' ) );
		}

		//advanced tinymce support
		if (current_theme_supports('xtreme-advanced-tinymce')) {
			add_editor_style('css/screen/editor-style.css');
			add_filter('mce_css', array(&$this, 'on_mce_css'), 999);
			add_filter('mce_buttons', array(&$this, 'on_mce_buttons'), 999);
		}
		
		//advanced color style support
		if (current_theme_supports('xtreme-color-styles')) {
			add_filter('after_theme_css', array(&$this, 'after_theme_css'));
		}

		//css compression
		add_action('admin_notices', array(&$this, 'on_admin_notice'));
	}
	
	function on_admin_notice() {

		if(!current_user_can('manage_options')) {
			return;
		}

		$errors = get_option(XF_UPDATE_ERRORS);
		if ( is_array( $errors ) && count( $errors ) > 0) {
			echo '<div id="message" class="error">';
			foreach( $errors as $error ) {
				echo '<p>'.$error.'</p>';
			}
			echo '</div>';
		}
		if( isset( $_GET[ 'xtreme_msg' ] ) ){

			if( $_GET[ 'xtreme_msg' ]  === 'xtreme_save' ){
				echo '<div class="updated">';
				echo '<p>' . __('Xtreme One was successfully updated and generated.', XF_TEXTDOMAIN) . '</p>';
				echo '</div>';
			}
			else if( $_GET[ 'xtreme_msg' ] === 'xtreme_default' ){
				echo '<div class="updated">';
				echo '<p>' . __('Xtreme One the default settings were successfully restored.', XF_TEXTDOMAIN) . '</p>';
				echo '</div>';
			}

		}


	}
	function on_mce_css($css) {
		$uri = XF_THEME_URI. '/css/screen/editor-style.css';
		$uri_dyn = XF_ABS_OUTPUT_URI_THEME_BASED. '/editor-style.css';
		$file_dyn = XF_ABS_OUTPUT_DIR_THEME_BASED. '/editor-style.css';
		if (is_readable($file_dyn) && stripos($css, $uri) !== false) {
			$css = str_replace($uri, $uri_dyn, $css);
		}
		return $css;
	}
	
	function on_mce_buttons($buttons) {
		return explode('|',str_replace('wp_adv', 'styleselect|wp_adv', implode('|', $buttons)));
	}
	
	function after_theme_css($css) {
		if( current_theme_supports('xtreme-color-styles') && isset($this->options['xc_general']['color_styles'])) {
			$support = get_theme_support('xtreme-color-styles');
			$colors = $support[0];
			$color = $this->options['xc_general']['color_styles']['value'];
			if (array_key_exists($color, $colors))
				$css[] = "/css/screen/$color.css";
		}
		return $css;
	}
	
	function on_thumbnail_column($cols) {
		$cols['thumbnail-image'] = __('Featured Image', XF_TEXTDOMAIN);
		return $cols;
	}
	
	function on_thumbnail_column_value($column_name, $post_id){
		if ($column_name != 'thumbnail-image') return;
		$thumb = get_the_post_thumbnail( $post_id, array(48, 48) , array("class" => 'thumbnail-column-image'));
		if ($thumb) 
			echo $thumb;
		else
			_e('undefined', XF_TEXTDOMAIN);
	}
	
	function on_admin_menu() {
		$this->pagehook = add_menu_page(  'Xtreme One', 'Xtreme One', 'manage_options', 'xtreme_backend', array( $this, 'on_show_theme_page' ), get_template_directory_uri() . '/admin/images/xtreme-menu-icon.png', 61 );
		add_submenu_page( 'xtreme_backend', __( 'Settings', XF_TEXTDOMAIN ), __( 'Settings', XF_TEXTDOMAIN ), 'manage_options', 'xtreme_backend', array( $this, 'on_show_theme_page') );

		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		xtreme_repair_metaboxes_sort_order(apply_filters('xtreme_metaboxes_default', array()), $this->pagehook);
	}
	
	/**
	 * 
	 * Add the Documentation menu element
	 * 
	 * @since 1.5.3
	 * @uses add_submenu_page
	 * 
	 */
	public function add_documentation_menu() {
		global $submenu;
		$submenu[ 'xtreme_backend' ][] = array( __( 'Documentation', XF_TEXTDOMAIN ), 'manage_options' , _x( 'https://github.com/xtreme-themes/xtreme-one/wiki', 'Documentation Link for Xtreme', XF_TEXTDOMAIN ) );
	}

	function on_admin_head() {
		global $plugin_page;
		if ($plugin_page != 'xtreme_backend') return;
	}

	
	function on_load_theme_editor_php() {
		wp_enqueue_script('xtreme-admin-theme-editor', XF_ADMIN_URL . '/js/xtreme-theme-editor'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);
	}
	
	function on_load_page() {
		load_theme_textdomain(XF_TEXTDOMAIN, XF_LANG_DIR);
		wp_enqueue_script('xtreme-user-credentials', XF_ADMIN_URL . '/js/user_credentials'.XF_ADMIN_SCRIPTS, array('jquery', 'jquery-ui-dialog'), false, false);
		wp_enqueue_style('widgets');
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		wp_enqueue_script('common');
		wp_enqueue_script('postbox');
		wp_enqueue_script(array( 'wp-lists', 'admin-widgets'));
		wp_enqueue_script('xtreme-admin-backend', XF_ADMIN_URL . '/js/xtreme-backend'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);
		
		foreach($this->metaboxes as $box) {
			add_meta_box(XF_METABOX_SLUG.$box->name, $box->desc, array(&$this, 'on_print_metabox_section'), $this->pagehook, 'normal', 'core');
		}
		//now lets check the file system access
		ob_start();
		if ( false === ($credentials = request_filesystem_credentials('')) ) {
			$data = ob_get_contents();
			ob_end_clean();
			if( ! empty($data) ){
				$this->requires_credentials = true;
			}
		} else {
			ob_end_clean();
		}
	}

	function on_print_metabox_section($xtreme, $box) {
		$section = str_replace(XF_METABOX_SLUG, '', $box['id']);
		$obj = $this->metaboxes[$section];
		echo $obj->as_table();
		?><br/><input type="submit" name="xtreme_save" id="xtreme_save" value="<?php _e('Generate Theme', XF_TEXTDOMAIN); ?>" class="button-primary" /><?php
	}

	/**
	 * Detects if the production-min.css is outdated and prints an admin-notice
	 *
	 * @return  Void
	 */
	function detect_outdated_production_stylesheet(){

		$outdated   = false;
		$output     = '';
		$stylesheets= array(
			XF_CHILD_THEME_DIR . '/css/screen/theme.css',
			XF_CHILD_THEME_DIR . '/css/screen/theme.responsive.css',
		);

		$current_version    = xtreme_get_production_stylesheet_version();
		if( $current_version === '' ){
			return;
		}
		foreach( $stylesheets as $stylesheet ) {

			if( file_exists( $stylesheet ) ) {

				$file_version = filemtime( $stylesheet );

				if( $file_version > $current_version ){
					$outdated = true;
					$output .= '<li>»<code>' . basename( $stylesheet ) . '</code> (' . date( 'd.m.Y h:i', $file_version ) . ')</li>';
				}

			}

		}

		if( $outdated ){
			echo '<div class="updated">';
			echo '<p>';
			printf(
				__(' Following Stylesheets are newer than your <code>production-min.css</code> (%s):', XF_TEXTDOMAIN ),
				date( 'd.m.Y h:i', $current_version )
			);
			echo '</p>';
			echo '<ul>' . $output . '</ul>';
			echo '<p>' . __( 'Please regenerate your Theme to see the changes.', XF_TEXTDOMAIN ) . '</p>';
			echo '</div>';
		}

	}

	function on_show_theme_page() {
		global $screen_layout_columns;
		if (is_wp_error($this->last_error)) {
		?>
		<div id="message" class="error"><p><?php echo $this->last_error->get_error_message(); ?></p></div>
		<?php $this->last_error = false;
		} ?>
		<div id="xtreme-metaboxes-all" class="wrap">
			<?php screen_icon('themes'); ?>
			<h2><?php _e('Xtreme Configuration Center', XF_TEXTDOMAIN);?></h2>
			<?php xtreme_backend_header() ?>
			<?php $this->detect_outdated_production_stylesheet(); ?>
			<form action="admin-post.php" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field('xtreme_backend_settings'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				<input type="hidden" name="action" value="save_xtreme_backend_settings" />
				<?php $this->the_button_bar(); ?>

				<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes($this->pagehook, 'side',''); ?>
					</div>
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php do_meta_boxes($this->pagehook, 'normal', ''); ?>
							<?php
							require_once(dirname(__FILE__).'/file_access.php');
							if($this->requires_credentials) {
								ob_start();
								if ( false === ($credentials = xtreme_request_filesystem_credentials(__('Connection information might be requiered to Generate Theme',XF_TEXTDOMAIN), '')) ) {
									$data = (string)ob_get_contents();
									ob_end_clean();
									echo $data;
								} else {
									ob_end_clean();
									$this->requires_credentials = false;
								}
							} ?>
							<?php $this->the_button_bar(); ?>
						</div>
					</div>
					<br class="clear"/>
				</div>
				<br/>
			</form>
		</div>
		<div id="dialog"></div>
		<div id="credentials_dialog"></div>
		<div id="help_dialog"></div>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			$('form').attr('autocomplete', 'off');
		});
		//]]>
		</script>
	<?php
	}

	/**
	 * Interal function to print the save-buttons, uninstall-buttons und default-buttons
	 * @return void
	 */
	private function the_button_bar() {
		?>
		<div>
			<input type="submit" name="xtreme_save" id="xtreme_save" value="<?php _e('Generate Theme', XF_TEXTDOMAIN); ?>" class="button-primary" />
			<input type="submit" name="xtreme_defaults" value="<?php _e('Load Default Options', XF_TEXTDOMAIN); ?>" class="button-secondary" />
			<input type="submit" name="xtreme_uninstall" value="<?php _e('Uninstall Theme', XF_TEXTDOMAIN); ?>" class="button-secondary" />
		</div>
		<?php
	}

	function on_screen_layout_columns($columns, $screen) {
		//bugfix: $this->pagehook is not valid because it will be set at hook 'admin_menu' but 
		//multisite pages or user dashboard pages calling different menu an menu hooks!
		if (
			(!defined( 'WP_NETWORK_ADMIN') || WP_NETWORK_ADMIN == false) 
			&& 
			(!defined( 'WP_USER_ADMIN' ) || WP_USER_ADMIN == false)
		){
			if ($screen == $this->pagehook) {
				$columns[$this->pagehook] = 2;
			}
		}
		return $columns;
	}

	function on_save_backend_settings() {
		if ( !current_user_can('manage_options') )
			wp_die( __('Cheatin&#8217; uh?', XF_TEXTDOMAIN) );

		check_admin_referer('xtreme_backend_settings');
		$redirect = admin_url( 'admin.php?page=xtreme_backend' );
		$msg = '';

		if (isset($_POST['xtreme_uninstall'])) {
			xtreme_cleanup_framework();
			$theme = function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme();
			do_action('switch_theme', $theme);
			$redirect = 'themes.php';
		}

		if (isset($_POST['xtreme_defaults'])) {
			xtreme_reset_framework();
			$this->generate_theme();
			$msg = 'xtreme_defaults';
		}

		if ( isset($_POST['xtreme_save']) ) {
			//load prior saved options and patch it by incomming post values
			$saved_options = $this->patched_post_options();

			//ensure validation by apply and collect again
			$this->apply_options($saved_options);
			$saved_options = $this->collect_options();

			//save options
			update_option(XF_OPTIONS, $saved_options);
			$this->save_default_layout();
			$this->update_layout_mode();

			$this->generate_theme();
			$msg = 'xtreme_save';
		}

		if( $msg !== '' ){
			$redirect = $redirect . '&xtreme_msg=' . $msg;
		}
		//lets redirect the post request into get request
		wp_redirect($redirect);
	}
	function update_layout_mode() {
		$layoutmode = (int) xtreme_is_layout_2();
		$layouts = get_option(XF_LAYOUTS);
		foreach ($layouts as $layoutname => $layoutvalue){
			$layouts[$layoutname]['is_layout_2']['value'] = $layoutmode;
		}
		update_option(XF_LAYOUTS, $layouts);
	}

	function save_default_layout() {
		$this->layouts = get_option(XF_LAYOUTS);
		$this->options = get_option(XF_OPTIONS);

		$this->desc = xtreme_description_array();
		$this->classes = xtreme_classes_array();
		$this->layoutmode = (int) xtreme_is_layout_2();

		$default = $this->options['xc_layout'];
		$this->layouts['xf_layout-default'] = $default;
		$this->layouts['xf_layout-default']['description']['value'] = $this->desc[$this->options['xc_layout']['columnlayout']['value']];
		$this->layouts['xf_layout-default']['mainclass']['value'] = $this->classes[$this->options['xc_layout']['columnlayout']['value']];
		$this->layouts['xf_layout-default']['is_layout_2']['value'] = $this->layoutmode;
		update_option(XF_LAYOUTS, $this->layouts);
	}

	function generate_theme() {
		require_once XF_ADMIN_DIR . '/xtreme-basemod-css.php';
		require_once XF_ADMIN_DIR . '/xtreme-patch-css.php';
		require_once XF_ADMIN_DIR . '/xtreme-production-css.php';
		require_once XF_ADMIN_DIR . '/xtreme-editor-css.php';

		$old_error_handler = set_error_handler(array(&$this, "on_writing_error"));

		$basemod = new Xtreme_Basemod_CSS();
		$basemod->write();

		$patch = new Xtreme_Patch_CSS();
		$patch->write();

		$production = new Xtreme_Production_CSS();
		$production->write();

		$editor = new Xtreme_Editor_Style();
		$editor->write();

		if ($old_error_handler)
			set_error_handler($old_error_handler);
			
		delete_option(XF_UPDATE_PENDING);
		delete_option(XF_UPDATE_ERRORS);
	}

	function collect_options() {
		$options = array();
		$keys = array_keys($this->metaboxes);
		foreach($keys as $key){
			$this->metaboxes[$key]->save($options);
		}
		return $options;
	}

	function apply_options(&$options) {
		$keys = array_keys($this->metaboxes);
		foreach($keys as $key){
			$this->metaboxes[$key]->load($options);
		}
	}

	function patched_post_options() {
		$saved_options = $this->collect_options();
		//processing incomming $_POST and replace our data
		foreach($_POST as $key => $value) {
			$parts = explode('-', $key);
			if (preg_match('/^xc_/', $parts[0])) {
				$t = &$saved_options;
				$count = count($parts);
				for ($i=0; $i<$count; $i++) {
					if (!@is_array($t[$parts[$i]])) $t[$parts[$i]] = array();
					$t = &$t[$parts[$i]];
				}
				$t = $value;
			}
		}
		return $saved_options;
	}

	function on_check_user_credentials() {
		//user permission check
		if ( current_user_can('manage_options') ) {
			global $wp_filesystem, $parent_file;
			$current_parent  = $parent_file;
			$parent_file 	 = 'tools.php'; //needed for screen icon :-)

			//check the file system
			ob_start();
			$url = 'admin-ajax.php';
			if ( false === ($credentials = request_filesystem_credentials($url)) ) {
				$data = ob_get_contents();
				ob_end_clean();
				if( ! empty($data) ){
					header('Status: 401 Unauthorized');
					header('HTTP/1.1 401 Unauthorized');
					echo $data;
					exit;
				}
				return;
			}

			if ( ! WP_Filesystem($credentials) ) {
				request_filesystem_credentials($url, '', true); //Failed to connect, Error and request again
				$data = ob_get_contents();
				ob_end_clean();
				if( ! empty($data) ){
					header('Status: 401 Unauthorized');
					header('HTTP/1.1 401 Unauthorized');
					echo $data;
					exit;
				}
				return;
			}
			ob_end_clean();
			$parent_file = $current_parent;
			exit(); //all is fine
		}

		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to configure this theme.",XF_TEXTDOMAIN);
		exit();
	}

	function on_writing_error($errorcode, $errortext, $file, $line) {
		
		switch ($errorcode) {
			case E_USER_ERROR:
				setcookie('XTREME_ERROR', $errortext, 0, '/');
				if ( is_ssl() )
					$protocol = 'https://';
				else
					$protocol = 'http://';
				$url = esc_url( $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				$_wp_http_referer = isset( $_POST['_wp_http_referer'] ) ? $_POST['_wp_http_referer'] : $url;
				wp_redirect( $_wp_http_referer );
				exit();
				break;
		}
		
		return false;
	}


	/**
	 * advanced gallery legacy: Enqueues the script
	 */
	public function wp_enqueue_media_advanced_gallery_legacy() {
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;

		wp_enqueue_script(
			'custom-gallery-fields-legacy',
			 XF_ADMIN_URL . '/js/custom-gallery-fields-legacy' . XF_ADMIN_SCRIPTS,
			array( 'media-views' )
		);

	}

	/**
	 * advanced gallery legacy: Outputs the view template with the custom fields
	 */
	function print_media_templates_advanced_gallery_legacy() {
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;

		?>
		<script type="text/html" id="tmpl-custom-gallery-legacy">
			<label class="setting">
				<span><?php _e( 'Order images by:', XF_TEXTDOMAIN ); ?></span>
				<select class="type" name="xtorderby" data-setting="xtorderby">
					<option value=""></option>
					<option value="menu_order"><?php _e( 'Menu order', XF_TEXTDOMAIN ); ?></option>
					<option value="title"><?php _e( 'Title', XF_TEXTDOMAIN ); ?></option>
					<option value="post_date"><?php _e( 'Date/Time', XF_TEXTDOMAIN ); ?></option>
				</select>
			</label>
			<label class="setting">
				<span><?php _e( 'Order:', XF_TEXTDOMAIN ); ?></span>
				<select class="type" name="xtorder" data-setting="xtorder">
					<option value=""></option>
					<option value="asc"><?php _e( 'Ascending', XF_TEXTDOMAIN ); ?></option>
					<option value="desc"><?php _e( 'Descending', XF_TEXTDOMAIN ); ?></option>
				</select>
			</label>
		</script>
		<?php
	}
}
$xtreme_backend = new Xtreme_backend();
