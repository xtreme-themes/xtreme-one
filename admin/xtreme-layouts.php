<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Xtreme_Layouts {

	protected $error;
	public $pagehook;

	function __construct() {
		$this->theme_data = wp_get_theme();
		$this->measures	= new xtreme_option_measures();
		$this->metaboxes = array();
		$this->requires_credentials = false;
		$this->message = array();
		add_action('init', array(&$this, 'on_admin_init'));
		add_action('admin_head', array(&$this, 'on_admin_head'));
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		add_action('admin_notices', array(&$this, 'on_admin_notices'));
		add_action('admin_post_save_xtreme_layout_settings', array(&$this, 'on_save_layout_settings'));
		$this->last_error = false;
		if ( isset($_COOKIE['XTREME_ERROR']) ) {
			$this->last_error = new WP_Error('xtreme', $_COOKIE['XTREME_ERROR']);
			setcookie('XTREME_ERROR', '', time() - 3600, '/');
		}
	}

	function get_measures() { return $this->measures; }

	function on_admin_init() {
		$this->metaboxes = xtreme_build_metaboxes(apply_filters('xtreme_metaboxes_layout', array()), $this);
		$this->error = array();
		$redirect = admin_url( 'admin.php?page=xtreme_layouts' );

		add_action('wp_ajax_xtreme_check_user_credentials', array(&$this, 'on_check_user_credentials'));
		if(isset($_GET['error'])) {
			switch ((int) $_GET['error']) {
				case 1:
					$this->error[] = __('No files applied to the new layout.', XF_TEXTDOMAIN);
					break;
				case 2:
					$this->error[] = __('You can not apply all files to a new Layout.', XF_TEXTDOMAIN);
					break;
				case 3:
					$this->error[] = __('You can not remove the only one remaining file from layout.', XF_TEXTDOMAIN);
					break;
			}
		}

		if ( isset($_GET['mode']) ) {
			$options = get_option(XF_LAYOUTS);
			$files = xtreme_load_templates();
			$temp_options['xc_templayout'] = $options['xf_layout-default'];
			$temp_options['xc_templayout']['nicename']['value'] = 'new Layout';
			foreach ($files as $file => $value) {
				$field = substr($value['metavalue'], 0, -4);
				$temp_options['xc_templayout'][$field]['value'] = false;
			}
			$temp_options['xc_templayout']['mode']['value'] = 'add';
			$this->apply_options($temp_options);
		}

		if ( isset($_GET['mode']) && isset($_GET['layout']) && 'edit_layout' == $_GET['mode'] ) {
			$layout = strip_tags($_GET['layout']);
			$options = get_option(XF_LAYOUTS);
			$templates = get_option(XF_TEMPLATES);
			$files = xtreme_load_templates();

			if (array_key_exists($layout, $options)) {
				$temp_options['xc_templayout'] = $options[$layout];
				foreach ($templates as $filename => $layoutname) {
					$field = substr($files[$filename]['metavalue'], 0, -4);
					if( $layoutname === $layout ) {
						$temp_options['xc_templayout'][$field]['value'] = true;
					} else {
						$temp_options['xc_templayout'][$field]['value'] = false;
					}
				}
				$temp_options['xc_templayout']['mode']['value'] = 'edit';
				$this->apply_options($temp_options);
			}
		}

		if ( isset($_GET['mode']) && isset($_GET['layout']) && 'delete_layout' == $_GET['mode']  && $_GET['layout'] !== 'xf_layout-default' ) {
			$layout = strip_tags($_GET['layout']);
			$options = get_option(XF_LAYOUTS);
			$templates = get_option(XF_TEMPLATES);
			if (array_key_exists($layout, $options)) {
				unset($options[$layout]);
				foreach($templates as $filename => $layoutname) {
					if( $layoutname === $layout ) {
						$templates[$filename] = 'xf_layout-default';
					}
				}
				update_option(XF_LAYOUTS, $options);
				update_option(XF_TEMPLATES, $templates);
				$this->generate_theme();
				$redirect = $redirect . '&xtreme_msg=layout_deleted&layout=' . $layout;
				wp_redirect( $redirect );
			}
		}
	}

	function on_admin_notices(){

		if( isset( $_GET[ 'xtreme_msg' ] ) ){

			if( $_GET[ 'xtreme_msg' ] === 'layout_saved' ){
				echo '<div class="updated">';
				echo '<p>';
				echo sprintf(
					__('Your Layout was successfully saved.', XF_TEXTDOMAIN),
					$_GET[ 'layout' ]
				);
				echo '</p>';
				echo '</div>';
			}

			if( $_GET[ 'xtreme_msg' ] === 'layout_deleted' ){
				echo '<div class="updated">';
				echo '<p>';
				echo sprintf(
					__( 'Your Layout <strong>%s</strong> was successfully deleted.', XF_TEXTDOMAIN),
					$_GET[ 'layout' ]
				);
				echo '</p>';
				echo '</div>';
			}

		}
	}

	function on_admin_head() {
		global $plugin_page;
		if ($plugin_page != 'xtreme_layouts') return;
	}

	function on_admin_menu() {
		$this->pagehook = add_submenu_page( 'xtreme_backend', __( 'Layouts', XF_TEXTDOMAIN ), __( 'Layouts', XF_TEXTDOMAIN ), 'manage_options', 'xtreme_layouts', array( $this, 'on_show_theme_page') ); 
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		xtreme_repair_metaboxes_sort_order(apply_filters('xtreme_metaboxes_layout', array()), $this->pagehook);
	}

	function on_load_page() {
		load_theme_textdomain(XF_TEXTDOMAIN, XF_LANG_DIR);
		wp_enqueue_style('widgets');
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		wp_enqueue_script('xtreme-user-credentials', XF_ADMIN_URL . '/js/user_credentials'.XF_ADMIN_SCRIPTS, array('jquery', 'jquery-ui-dialog'), false, false);
		wp_enqueue_script('common');
		wp_enqueue_script('postbox');
		wp_enqueue_script(array( 'wp-lists', 'admin-widgets'));
		wp_enqueue_script('xtreme-admin-backend', XF_ADMIN_URL . '/js/xtreme-backend'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);

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

	function load_temp_metabox() {
		foreach($this->metaboxes as $box) {
			add_meta_box(XF_METABOX_SLUG.$box->name, $box->desc, array(&$this, 'on_print_metabox_section'), $this->pagehook, 'normal', 'core');
		}
	}

	function on_print_metabox_section($xtreme, $box) {
		$section = str_replace(XF_METABOX_SLUG, '', $box['id']);
		$obj = $this->metaboxes[$section];
		echo $obj->as_table();
	}

	function on_show_theme_page() {
		if ($this->error ) : ?>
			<div id="error" class="error fade">
				<?php foreach ($this->error as $err): ?>
					<p><?php echo esc_html( $err ) ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ($this->message) : ?>
			<div id="message" class="updated fade">
				<?php foreach ($this->message as $msg): ?>
					<p><?php echo esc_html( $msg ) ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif;
		if (is_wp_error($this->last_error)) {
		?>
			<div id="message" class="error"><p><?php echo $this->last_error->get_error_message(); ?></p></div>
			<?php
			$this->last_error = false;
			}
			?>
			<div id="xtreme-metaboxes-all" class="wrap">
				<?php screen_icon('themes'); ?>
				<h2><?php _e('Xtreme Layouts', XF_TEXTDOMAIN);?></h2>
				<?php xtreme_backend_header() ?>
				<form action="admin-post.php" method="post" enctype="multipart/form-data">
					<div id="poststuff" class="metabox-holder">
					<?php
					if ( isset($_GET['mode'] ) ) {
						if ( 'add_layout' == $_GET['mode'] || 'edit_layout' == $_GET['mode'] ) {
							$this->load_temp_metabox();?>
							<?php wp_nonce_field('xtreme_layout_settings'); ?>
							<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
							<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
							<input type="hidden" name="action" value="save_xtreme_layout_settings" />
							<?php do_meta_boxes($this->pagehook, 'normal', $this->theme_data); ?>
							<?php
							require_once(dirname(__FILE__).'/file_access.php');
							if($this->requires_credentials) {
								ob_start();
								if ( false === ($credentials = xtreme_request_filesystem_credentials(__('Connection information might be required to Modify Layouts', XF_TEXTDOMAIN), '')) ) {
									$data = (string)ob_get_contents();
									ob_end_clean();
									echo $data;
								} else {
									ob_end_clean();
									$this->requires_credentials = false;
								}
							}
							?>
						<div>
							<input type="submit" name="xtreme_save_layout" id="xtreme_save_layout" value="<?php _e('Save', XF_TEXTDOMAIN); ?>" class="button-primary" />
							<input type="submit" name="xtreme_cancel_layout" id="xtreme_cancel_layout" value="<?php _e('Cancel', XF_TEXTDOMAIN); ?>" class="button-secondary" />
						</div>
						<?php
						}
					}
					?>
					</div>
				</form>
				<?php
				if ( !isset($_GET['mode'] ) ) {
					$this->print_layouts_table();
				}
				?>
				<br />
			</div>
			<div id="dialog"></div>
			<div id="credentials_dialog"></div>
			<div id="help_dialog"></div>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
	});
	//]]>
	</script>
	<?php
	}

	function print_layouts_table() {
		?>
		<form action="" id="xtreme-layout-form" method="post">
			<table class="widefat x-layout-box">
				<thead>
					<tr>
						<th class="x-layout"><?php esc_attr_e('Layout', XF_TEXTDOMAIN) ?></th>
						<th class="x-desc"><?php esc_attr_e('Description', XF_TEXTDOMAIN) ?></th>
						<th class="x-applied"><?php esc_attr_e('Applied to', XF_TEXTDOMAIN) ?></th>
						<th class="x-action"><?php esc_attr_e('Action', XF_TEXTDOMAIN) ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$layouts = get_option(XF_LAYOUTS, array());
				$templates = get_option(XF_TEMPLATES, array());
				foreach ($layouts as $layoutname => $layoutvalue):
				?>
					<tr>
					<td><?php echo esc_attr($layoutvalue['nicename']['value']);?></td>
					<td><?php echo esc_attr($layoutvalue['description']['value']);?></td>
					<td>
				<?php
					foreach($templates as $k => $v ) {
						if($v === $layoutname) {
							echo esc_attr($k) . ', ';
						}
					}
					?>
					</td>
					<td>
					<?php if ( $layoutname === 'xf_layout-default' ): ?>
						<span>&nbsp;</span>
					<?php else: ?>
						<a href="<?php echo admin_url( 'admin.php?page=xtreme_layouts&mode=edit_layout&layout=' .esc_attr($layoutname) ); ?>"><?php esc_attr_e( 'edit', XF_TEXTDOMAIN ) ?></a>
						<span> | </span>
						<a href="<?php echo admin_url( 'admin.php?page=xtreme_layouts&mode=delete_layout&layout=' .esc_attr($layoutname) ); ?>"><?php esc_attr_e( 'delete', XF_TEXTDOMAIN ) ?></a>
					<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr class="nodrag">
					<td colspan="6"><a href="<?php echo admin_url( 'admin.php?page=xtreme_layouts&mode=add_layout' ); ?>"><?php esc_attr_e( 'Add New Layout', XF_TEXTDOMAIN ) ?></a></td>
				</tr>
			</tfoot>
		</table>
	</form>
	<?php
	}

	function on_save_layout_settings() {
		if ( !current_user_can('manage_options') )
			wp_die( __('Cheatin&#8217; uh?', XF_TEXTDOMAIN) );

		check_admin_referer('xtreme_layout_settings');
		$redirect = admin_url( 'admin.php?page=xtreme_layouts' );
		$msg = array();

		if ( isset( $_POST['xtreme_save_layout'] ) ) {
			$saved_options = $this->patched_post_options();
			//ensure validation by apply and collect again
			$this->apply_options($saved_options);
			$saved_options = $this->collect_options();
			$this->save_layout_options($saved_options);
			$this->generate_theme();

			$msg[ 'xtreme_msg' ] = 'layout_saved';
		}

		if( count( $msg ) > 0 ){
			$redirect = add_query_arg( $msg, $redirect);
		}

		wp_safe_redirect( $redirect );
	}

	function generate_theme() {
		require_once XF_ADMIN_DIR . '/xtreme-basemod-css.php';
		require_once XF_ADMIN_DIR . '/xtreme-patch-css.php';
		require_once XF_ADMIN_DIR . '/xtreme-production-css.php';
		$old_error_handler = set_error_handler(array(&$this, "on_writing_error"));
		$basemod = new Xtreme_Basemod_CSS();
		$basemod->write();

		$patch = new Xtreme_Patch_CSS();
		$patch->write();

		$production = new Xtreme_Production_CSS();
		$production->write();
		if ($old_error_handler)
			set_error_handler($old_error_handler);
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
		foreach($_POST as $key => $value) {
			$parts = explode('-', $key);
			if (preg_match('/^xc_/', $parts[0])) {
				$t = &$saved_options;
				$count = count($parts);
				for ($i=0; $i<$count; $i++) {
					if (!is_array($t[$parts[$i]])) $t[$parts[$i]] = array();
						$t = &$t[$parts[$i]];
				}
				$t = $value;
			}
		}
		return $saved_options;
	}

	function save_layout_options($saved_options) {
		$layouts = get_option(XF_LAYOUTS);
		$templates = get_option(XF_TEMPLATES);
		$files = xtreme_load_templates();
		$desc = xtreme_description_array();
		$this->classes = xtreme_classes_array();
		$sanitized = sanitize_title($saved_options['xc_templayout']['nicename']['value']);
		$name = strtolower($sanitized);
		if($saved_options['xc_templayout']['mode']['value'] === 'add') {
			$key = 'xf_layout-' .$name;
		}

		if($saved_options['xc_templayout']['mode']['value'] === 'edit') {
			$key = $saved_options['xc_templayout']['layoutname']['value'];
		}

		$title = strip_tags($saved_options['xc_templayout']['nicename']['value']);
		$title = apply_filters('wptexturize', $title);
		$title = apply_filters('convert_chars', $title);
		$title = apply_filters('trim', $title);

		$layouts[$key] = $saved_options['xc_templayout'];
		$layouts[$key]['layoutname']['value'] = $key;
		$layouts[$key]['nicename']['value'] = $title;
		$layouts[$key]['description']['value'] = $desc[$saved_options['xc_templayout']['columnlayout']['value']];
		$layouts[$key]['mainclass']['value'] = $this->classes[$saved_options['xc_templayout']['columnlayout']['value']];

		$i = 0;
		$templ = false;
		foreach ( $files as $file => $data ) {
			$field = substr( $data['metavalue'], 0, -4 );
			if ( 1 == $_POST['xc_templayout-'. $field .'-value'] ) {
				$i++;
				$templ[$file] = $key;
			} elseif( 0 == $_POST['xc_templayout-'. $field .'-value'] ) {
				if($templates[$file] === $key) {
					$templ[$file] = 'xf_layout-default';
				}
			}
		}
		if($templ) {
			$templates = array_merge($templates,$templ);
		} else {
			wp_redirect( admin_url( 'admin.php?page=xtreme_layouts&mode=add_layout&layout='.esc_attr($key).'&error=1' ) );
			exit();
		}

		foreach($templates as $tpl => $f) {
			if(!array_key_exists($tpl, $files)) {
				unset($templates[$tpl]);
			}
		}
		$clear = (array_unique(array_values($templates)));
		$unique_templates = array_count_values($templates);
		$count_layouts = count($layouts);

		if($count_layouts > count($unique_templates)) {
			foreach($layouts as $layout => $data) {
				if (!in_array($layout, $clear)) {
					if( $layout === 'xf_layout-default') {
						wp_redirect( admin_url( 'admin.php?page=xtreme_layouts&mode=edit_layout&layout='.esc_attr($key).'&error=2' ) );
						exit();
					} else {
						unset($layouts[$layout]);
					}
				}
			}
		}
		if ( !isset($layouts[$key]) ) {
			wp_redirect( admin_url( 'admin.php?page=xtreme_layouts&mode=edit_layout&layout='.esc_attr($key).'&error=3' ) );
			exit();
		}
		if ( $layouts[$key]['mode']['value'] === 'add' || $layouts[$key]['mode']['value'] === 'edit') {
			if ( $i === 0 ) {
				$redirect = admin_url( 'admin.php?page=xtreme_layouts&mode=add_layout&error=1' );
				wp_redirect( $redirect );
				exit();
			}
		}
		update_option(XF_LAYOUTS, $layouts);
		update_option(XF_TEMPLATES, $templates);
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
		_e("You do not have the permission to configure this theme.", XF_TEXTDOMAIN);
		exit();
	}

	function on_writing_error($errorcode, $errortext, $file, $line) {
		switch ($errorcode) {
			case E_USER_ERROR:
				setcookie('XTREME_ERROR', $errortext, 0, '/');
				wp_redirect($_POST['_wp_http_referer']);
				exit();
				break;
			}
		return false;
	}
}
$xtremelayouts = new Xtreme_Layouts();
