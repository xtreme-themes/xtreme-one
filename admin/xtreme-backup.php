<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once(XF_ADMIN_DIR . '/includes/xtreme-backend-functions.php');

class Xtreme_Backup {

	public $pagehook;
	public $error;

	function __construct() {
		$this->requires_credentials = false;
		$this->message = array();
		add_action('init', array(&$this, 'on_admin_init'));
		add_action('admin_head', array(&$this, 'on_admin_head'));
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		add_action('admin_post_save_xtreme_backup', array(&$this, 'on_save_backup'));
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		$this->last_error = false;
		if ( isset($_COOKIE['XTREME_ERROR']) ) {
			$this->last_error = new WP_Error('xtreme', $_COOKIE['XTREME_ERROR']);
			setcookie('XTREME_ERROR', '', time() - 3600, '/');
		}
	}
	
	function on_admin_init() {}

	function on_admin_head() {}

	function on_admin_menu() {
		$this->pagehook = add_submenu_page( 'xtreme_backend', __( 'Backup', XF_TEXTDOMAIN ), __( 'Backup', XF_TEXTDOMAIN ), 'manage_options', 'xtreme_backup', array( $this, 'on_show_theme_page') ); 
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
	}

	function on_screen_layout_columns($columns, $screen) {
		//bugfix for multisite overviews, $this is not valid because of admin menu has not been called prior (order problem!)
		if (!defined('WP_NETWORK_ADMIN')) {
			if ($screen == $this->pagehook) {
				$columns[$this->pagehook] = 1;
			}
		}
		return $columns;
	}
	
	function on_load_page() {
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		$this->error = array();
		if(isset($_GET['error'])) {
			switch ((int) $_GET['error']) {
				case 1:
					$this->error[] = __('The <em>Xtreme One</em> versions does not match the XML file provided version, Import failed.', XF_TEXTDOMAIN);
					break;
				case 2:
					$this->error[] = __('The <em>Xtreme One</em> backup XML file is suited for a different Theme, Import failed.', XF_TEXTDOMAIN);
					break;
				case 3:
					$this->error[] = __('The <em>Xtreme One</em> backup XML file content is malformed, Import failed.', XF_TEXTDOMAIN);
					break;
				case 4:
					$this->error[] = __('The file is no suitable <em>Xtreme One</em> backup XML file, Import failed.', XF_TEXTDOMAIN);
					break;
			}
		}
		if(isset($_GET['message'])) {
			if(isset($_GET['message'])) {
				switch ((int) $_GET['message']) {
					case 1:
						$this->message[] = __('Xtreme One configuration import successful. Widget import permitted and done.', XF_TEXTDOMAIN);
						break;
					case 2:
						$this->message[] = __('Xtreme One configuration imported successful. Widget import was not permitted and has been skipped.', XF_TEXTDOMAIN);
						break;
				}
			}
		}

		load_theme_textdomain(XF_TEXTDOMAIN, XF_LANG_DIR);
		wp_enqueue_script('xtreme-user-credentials', XF_ADMIN_URL . '/js/user_credentials'.XF_ADMIN_SCRIPTS, array('jquery', 'jquery-ui-dialog'), false, false);
		wp_enqueue_script('common');
		wp_enqueue_script('postbox');
		wp_enqueue_script(array( 'wp-lists', 'admin-widgets'));
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
		
		add_meta_box(XF_METABOX_SLUG.'export', __('Xtreme One - Export', XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_export'), $this->pagehook, 'normal', 'core');
		add_meta_box(XF_METABOX_SLUG.'import', __('Xtreme One - Import', XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_import'), $this->pagehook, 'normal', 'core');
	}
	
	function on_print_metabox_export($data) {	
		?>
		<p class=""><?php _e('You can export your current theme configuration including the Widget Area Settings as *.xml file for safety reasons.<br/>You will be able to restore later on your current configuration based on the export file content in case of blog damage or host moving.', XF_TEXTDOMAIN); ?></p>
		<p><input type="submit" class="button-secondary" name="xtreme_export" id="xtreme_export" value="<?php _e('Export Current Configuration',XF_TEXTDOMAIN) ?>" autocomplete="off" /></p>
		<?php
	}
	
	function on_print_metabox_import() {
		require_once(dirname(__FILE__).'/file_access.php');
		if($this->requires_credentials) {
			ob_start();
			if ( false === ($credentials = xtreme_request_filesystem_credentials(__('Connection information might be required to Import Configurations',XF_TEXTDOMAIN), '')) ) {
				$data = (string)ob_get_contents();
				ob_end_clean();
				echo $data;
			} else {
				ob_end_clean();
				$this->requires_credentials = false;
			}
		}
		?>
		<p class=""><?php _e('You can import a saved configuration and re-generated the theme. Please keep in mind, that your current configuration will be lost.<br/>Furthermore the import will only succeed, if <em>Xtreme One</em> main version matches, child theme is the same and file content is not corrupt.', XF_TEXTDOMAIN); ?></p>
		<p><input type="checkbox" name="permit_widget_import" value="1"> <?php _e('permit widget import <small>(It could be possible that 3rd party widgets being imported may break your WordPress install.)</small>', XF_TEXTDOMAIN); ?></p>
		<p><input type="file" name="xml_file" id="xml_file" autocomplete="off"/> <input type="submit" class="button-secondary" name="xtreme_import" id="xtreme_import" value="<?php _e('Import Configuration',XF_TEXTDOMAIN) ?>" autocomplete="off" /></p>
		<?php
	}
	
	function on_show_theme_page() {
		global $screen_layout_columns;
		if (is_wp_error($this->last_error)) {
		?>
		<div id="message" class="error"><p><?php echo $this->last_error->get_error_message(); ?></p></div>
			<?php $this->last_error = false;
		}
		?>
		<?php if ($this->message) : ?>
		<div id="message" class="updated fade">
			<?php foreach ($this->message as $msg): ?>
				<p><?php echo esc_html( $msg ) ?></p>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php if ($this->error ) : ?>
		<div id="error" class="error fade">
			<?php foreach ($this->error as $err): ?>
			<p><?php echo $err; ?></p>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div id="xtreme-metaboxes-all" class="wrap">
			<?php screen_icon('themes'); ?>
			<h2><?php _e('Xtreme Backup Center', XF_TEXTDOMAIN);?></h2>
			<?php xtreme_backend_header() ?>
			<form action="admin-post.php" method="post" enctype="multipart/form-data">
				<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes($this->pagehook, 'side',''); ?>
					</div>
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
							<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
							<?php wp_nonce_field('xtreme_backend_settings'); ?>
							<input type="hidden" name="action" value="save_xtreme_backup" />
							<?php do_meta_boxes($this->pagehook, 'normal', ''); ?>
						</div>
					</div>
					<br class="clear"/>
				</div>
				<br/>
			</form>
		</div>
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
	
	function _collect_widgets(){
		global $wp_registered_widgets;
		$collection = array();
		$classes = array();
		foreach($wp_registered_widgets as $key => $widget) {
			$class = '-n.a.-';
			if(is_array($widget['callback'])){
				//new widget class based widget
				$class = 'widget_'.$widget['callback'][0]->id_base;
			}else{
				//deprecated widgets
				$class = $widget['callback'];
			}
			if (!in_array($class, $classes)) {
				$classes[] = $class;
				$collection[$class] = get_option($class);
			}
		}
		return $collection;
	}
	
	function on_save_backup() {
        if ( !current_user_can('manage_options') )
            wp_die( __('Cheatin&#8217; uh?', XF_TEXTDOMAIN) );

        check_admin_referer('xtreme_backend_settings');
        $redirect = $_POST['_wp_http_referer'];

        if ( isset($_POST['xtreme_export']) ) {
			require_once(XF_ADMIN_DIR . '/includes/xtreme-xmlprocessor.php');
			$xml = new xtreme_xml_processor();
			$xml->backup_as_xml(
				XF_VERSION, 
				get_option(XF_OPTIONS), 
				get_option(XF_LAYOUTS), 
				get_option(XF_TEMPLATES), 
				get_option(XF_WIDGET_PERMISSIONS),
				( is_multisite() ? get_blog_option( XF_SITE_ID, XF_WIDGET_BURNING_REG, array() ) : get_option( XF_WIDGET_BURNING_REG, array() ) ),
				get_option('sidebars_widgets'), 
				$this->_collect_widgets()
			);
		}
		
		//should we import an existing file ?
		if (isset($_POST['xtreme_import'])) {
			
			if ( !current_user_can('upload_files') )
				wp_die( __('Cheatin&#8217; uh?', XF_TEXTDOMAIN) );

			if (isset($_FILES) == true && empty($_FILES) == false) {
			
				if (!$_FILES['xml_file']['error'] && $_FILES['xml_file']['size'] > 0){
				
					require_once(XF_ADMIN_DIR . '/includes/xtreme-xmlprocessor.php');
					$xml = new xtreme_xml_processor();
					
					$saved_options = $xml->restore_from_xml(XF_VERSION, $_FILES['xml_file']['tmp_name']);

					@unlink($_FILES['xml_file']['tmp_name']);
										
					if (!is_array($saved_options)) {
						wp_redirect( admin_url( 'admin.php?page=xtreme_backup&error=' . $saved_options ) );
						exit();					
					}

					update_option(XF_OPTIONS, $saved_options[XF_OPTIONS]);
					update_option(XF_LAYOUTS, $saved_options[XF_LAYOUTS]);
					update_option(XF_TEMPLATES, $saved_options[XF_TEMPLATES]);
					update_option(XF_WIDGET_PERMISSIONS, $saved_options[XF_WIDGET_PERMISSIONS]);
					if(is_multisite() && XF_IS_MAIN_BLOG) {
						update_blog_option(XF_SITE_ID, XF_WIDGET_BURNING_REG, $saved_options[XF_WIDGET_BURNING_REG]);
					}
					
					if (isset($_POST['permit_widget_import']) && $_POST['permit_widget_import'] == 1) {
						update_option('sidebars_widgets', $saved_options['sidebars-widgets']);
						$widgets = $saved_options['widgets'];
						foreach($widgets as $key => $value) {
							update_option($key, $value);
						}
						$redirect .= '&message=1';
					}
					else {
						$redirect .= '&message=2';
					}
					
					if (isset($saved_options[XF_VERSION_FIELD])) {
						//we import an old (lower) version backup and have to patch it upto current version
						update_option(XF_VERSION_FIELD, $saved_options[XF_VERSION_FIELD]);
						xtreme_validate_framework();
					}				
					
					require_once XF_ADMIN_DIR . '/xtreme-basemod-css.php';
					require_once XF_ADMIN_DIR . '/xtreme-patch-css.php';
					require_once XF_ADMIN_DIR . '/xtreme-production-css.php';
					$basemod = new Xtreme_Basemod_CSS();
					$basemod->write();

					$patch = new Xtreme_Patch_CSS();
					$patch->write();

					$production = new Xtreme_Production_CSS();
					$production->write();
				} else {
					$redirect .= '&error=4';
				}
			} else{
				$redirect .= '&error=4';
			}
		}
        wp_redirect($redirect);
	}
}

$xtremebackup = new Xtreme_Backup();
