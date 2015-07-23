<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Xtreme_Widgets_Admin {

	public $pagehook;

	function __construct() {
		
		add_action('widgets_admin_page', array(&$this, 'on_widgets_admin_page'));
		add_action('admin_init', array(&$this, 'on_admin_init'));
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		
		/* wp import correction */
		add_action('import_post_meta', array(&$this, 'on_repair_wp_import'), 10, 3);
	}

	function on_widgets_admin_page() {
		
		if (!current_user_can('manage_options'))
			return;
		
		$link = admin_url( 'admin.php?page=xtreme_widgets' );
		?>
		<p>
			<?php 
				$link_text = sprintf('<a class="button" href="%s"><span class="settings">&nbsp;</span>%s - %s</a>',$link,  __( 'Xtreme One', XF_TEXTDOMAIN ), __( 'Widgets', XF_TEXTDOMAIN ));
				printf(__('If you want to limit the amount of visible or allowed Widgets, you can limit them at the page %s.', XF_TEXTDOMAIN),$link_text); 
			?>
		</p>
		<?php
	}
	
	function on_admin_init() {
		global $xtreme_widget_manager;

		/* tinymce widgets */
		add_filter('mce_external_plugins',  array(&$this, 'on_mce_external_plugin'));
		add_filter('mce_css',  array(&$this, 'on_mce_css'));
		add_filter('tiny_mce_before_init', array(&$this, 'on_tiny_mce_before_init'));
		add_action('save_post', array(&$this, 'on_mce_save_post_cleanup'), 10, 2);
		if( $xtreme_widget_manager->current_user_has_right() ) {
			add_filter('mce_buttons',  array(&$this,'on_mce_buttons'));
			add_action('wp_ajax_mce_xcontentwidget', array(&$this, 'on_tiny_mce_ajax_xcontentwidget'));
		}
	}

	function on_admin_menu() {
		
		$this->pagehook = add_submenu_page( 'xtreme_backend', __( 'Widgets', XF_TEXTDOMAIN ), __( 'Widgets', XF_TEXTDOMAIN ), 'manage_options', 'xtreme_widgets', array( $this, 'on_show_options_page') ); 
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
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
	
	function on_load_page() {
		
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		wp_enqueue_script('common');
		wp_enqueue_script('postbox');
		wp_enqueue_script(array( 'wp-lists', 'admin-widgets'));
		wp_enqueue_script('xtreme-widgets-admin', XF_ADMIN_URL . '/js/xtreme-widgets-admin'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);
		
		load_theme_textdomain(XF_TEXTDOMAIN, XF_LANG_DIR);
		add_meta_box(XF_METABOX_SLUG.'widgets-general', __("Widgets General", XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_general'), $this->pagehook, 'normal', 'core');
		add_meta_box(XF_METABOX_SLUG.'widgets-wordpress', __("WordPress Core Widgets", XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_wordpress'), $this->pagehook, 'normal', 'core');
		add_meta_box(XF_METABOX_SLUG.'widgets-xtreme', __("Xtreme One Widgets", XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_xtreme'), $this->pagehook, 'normal', 'core');
		add_meta_box(XF_METABOX_SLUG.'widgets-custom', __("Custom Plugin Widgets", XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_custom'), $this->pagehook, 'normal', 'core');
		add_meta_box(XF_METABOX_SLUG.'widgets-deprecated', __("Custom Deprecated Widgets", XF_TEXTDOMAIN), array(&$this, 'on_print_metabox_deprecated'), $this->pagehook, 'normal', 'core');
	}
	
	function _print_box_content($groupname, $widgets, $is_deprecated = false) {
		global $xtreme_widget_manager, $wp_widget_factory, $wp_registered_widgets;
		
		ob_start();
		$on = 0; $off = 0;
		foreach($widgets as $widget) {
			$name = __("unknown", XF_TEXTDOMAIN);
			$desc = __("unknown", XF_TEXTDOMAIN);
			if ($is_deprecated) {
				if (isset($wp_registered_widgets[$widget])) {
					$name = $wp_registered_widgets[$widget]['name'];
					$desc = $wp_registered_widgets[$widget]['description'];
				}else {
					$name = $xtreme_widget_manager->widgets_deprecated_off[$widget]['name'];
					$desc = $xtreme_widget_manager->widgets_deprecated_off[$widget]['description'];
				}
			}else{
				if(isset($wp_widget_factory->widgets[$widget])) {
					$name = $wp_widget_factory->widgets[$widget]->name;
					$desc = $wp_widget_factory->widgets[$widget]->widget_options['description'];
				}else {
					$w = new $widget();
					$name = $w->name;
					$desc = $w->widget_options['description'];
				}
			}
			if ($xtreme_widget_manager->is_widget_enabled($widget)) $on++;
			else $off++;
			?>
			<div class="x-admin-widget"><b><?php echo $name;?></b>
				<span class="alignright"><input class="checkbox-js checkbox-inverse" name="disabled_widgets[]" type="checkbox" value="<?php echo $widget;?>"<?php checked(true, $xtreme_widget_manager->is_widget_disabled($widget)); ?> /></span>
				<div class="x-admin-widget-desc"><?php echo $desc; ?></div>
				<?php if(!$is_deprecated) : ?>
					<div class="x-filter-wrapper">
						<div class="x-filter-actions"><a class="x-edit-filter" href="#"><?php _e('Edit Filter',XF_TEXTDOMAIN); ?></a><a class="x-done-filter x-hidden" href="#"><?php _e('Collapse',XF_TEXTDOMAIN); ?></a><strong style="color:#ddd;"> | </strong><a class="x-remove-all-filter" href="#"><?php _e('Reset All Filter',XF_TEXTDOMAIN);?></a></div>
						<?php $xtreme_widget_manager->__print_filter_section_xtreme_widget_admin($widget); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
		$data = (string)ob_get_contents();
		ob_end_clean();
		?>
		<p>
			<label><input class="checkbox-js" name="<?php echo $groupname; ?>" type="checkbox" value="x-yes"<?php checked(true, $off <= $on); ?> />&nbsp;<?php _e("change all widgets at this section at once", XF_TEXTDOMAIN);?></label>
		</p>
		<div>
		<?php echo $data; ?>
		<br class="clear"></div><?php
	}
	
	function on_print_metabox_general($xtreme, $box) {
		global $xtreme_widget_manager;
		?>
		<div class="alignleft">
			<p>
				<label><input class="checkbox-js" name="general_permission" type="checkbox" value="x-yes"<?php checked(true, $xtreme_widget_manager->is_general_enabled()); ?>/>&nbsp;<?php _e("automatic permission for any new widget", XF_TEXTDOMAIN);?></label>
			</p>
			<p>
				<label><input class="checkbox-js" name="enable_filter_system" type="checkbox" value="x-yes"<?php checked(true, $xtreme_widget_manager->is_filtersystem_enabled())?> />&nbsp;<?php _e('enable advanced widget filter capabilities', XF_TEXTDOMAIN); ?></label>
			</p>
			<p>
				<label><input class="checkbox-js" name="enable_auto_tabs" type="checkbox" value="x-yes"<?php checked(true, $xtreme_widget_manager->is_auto_tabs_enabled())?> />&nbsp;<?php _e('enable automatic tabbed widgets feature', XF_TEXTDOMAIN); ?></label>
			</p>
		</div>
		<div class="alignleft" style="margin-left: 25px;">
			<p>
			<label for="content_widget_role"><?php _e('Content Widgets:', XF_TEXTDOMAIN); ?>
				<select name="content_widget_role" id="content_widget_role">
				<?php wp_dropdown_roles($xtreme_widget_manager->current_content_widget_role()); ?>
				</select>
			</label>
			</p>
		</div>
		<br class="clear" />
		<?php
	}
	
	function on_print_metabox_wordpress($xtreme, $box) {
		global $xtreme_widget_manager;
		
		$this->_print_box_content("wordpress_group", $xtreme_widget_manager->widgets_wordpress);
	}
	
	function on_print_metabox_xtreme($xtreme, $box) {
		global $xtreme_widget_manager;
		
		$this->_print_box_content("xtreme_group", $xtreme_widget_manager->widgets_xtreme);
	}
		
	function on_print_metabox_custom($xtreme, $box) {
		global $xtreme_widget_manager;
		
		$this->_print_box_content("custom_group", $xtreme_widget_manager->widgets_custom);
	}
	
	function on_print_metabox_deprecated($xtreme, $box) {
		global $xtreme_widget_manager;
		
		$this->_print_box_content("deprecated_group", $xtreme_widget_manager->widgets_deprecated, true);
	}


	function on_show_options_page() {
		global $screen_layout_columns;
		
		$link = "widgets.php";
		?>
		<div id="xtreme-metaboxes-all" class="wrap">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Widget Permission Settings', XF_TEXTDOMAIN);?></h2>
			<?php xtreme_backend_header() ?>
			<p>
				<?php 
					$link_text = sprintf('<a class="x-widget-page-button button" href="%s"><span class="appearance">&nbsp;</span>%s - %s</a>',$link,  __('Appearance', XF_TEXTDOMAIN), __('Widgets', XF_TEXTDOMAIN));
					printf(__("You want to limit the amount of available widgets at the page %s, you can allow or deny the utilization of each specific widget in detail here.", XF_TEXTDOMAIN), $link_text); 
				?>
				<br/><small><em><strong><?php _e('Attention:', XF_TEXTDOMAIN) ?></strong> <?php _e("If you switch off widgets here, please be sure, that you are not using it. Used widgets switched off, will loose all their settings and will not be parked.", XF_TEXTDOMAIN); ?></em></small>				
			</p>
			<form action="admin-post.php" method="post" enctype="multipart/form-data">
				<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes($this->pagehook, 'side',''); ?>
					</div>
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">			
							<?php wp_nonce_field('xtreme_backend_settings'); ?>
							<input type="hidden" name="action" value="save_xtreme_widgets" />
							<?php do_meta_boxes($this->pagehook, 'normal', ''); ?>
							<?php
							global $xtreme_widget_manager, $wp_widget_factory;
							?>
							<div>
								<input type="submit" name="xtreme_save" id="xtreme_save" value="<?php _e('Save Settings', XF_TEXTDOMAIN); ?>" class="button-primary" />
								<input type="submit" name="xtreme_reset" id="xtreme_reset" value="<?php _e('Reset to general permission', XF_TEXTDOMAIN); ?>" class="button-secondary" />
							</div>
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
					
	function on_mce_external_plugin($plugin_array) {
		global $wp_version;

		if( version_compare( $wp_version, '3.9', '<' ) ) {
			$plugin_array['xtreme_tinymce_widgets'] = XF_ADMIN_URL.'/js/tinymce/widget_plugin.tinymce3'.XF_ADMIN_SCRIPTS;
		}
		else {
			$plugin_array['xtreme_tinymce_widgets'] = XF_ADMIN_URL.'/js/tinymce/widget_plugin'.XF_ADMIN_SCRIPTS;
		}

		return $plugin_array;
	}
	
	function on_mce_css($css) {
		
		return $css.','.XF_ADMIN_URL.'/js/tinymce/css/editor.css';
	}
	
	function on_mce_buttons($buttons) {
		$buttons[] = 'xtreme_tinymce_widgets';
		return $buttons;
	}
	
	function on_tiny_mce_before_init($params) {
		global $xtreme_widget_manager, $wp_widget_factory;

		$avail = array(
			'mainmenu' => array(
				'title' =>  __('Widget Collections', XF_TEXTDOMAIN),
				'submenus' => array(
					array(
						'title' => __('Xtreme One Framework', XF_TEXTDOMAIN),
						'subtitle' => '',
						'items' => array()
					),
					array(
						'title' => __('Custom Plugin Widgets', XF_TEXTDOMAIN),
						'subtitle' => '',
						'items' => array()
					),
					array(
						'title' => __('WordPress (Default)', XF_TEXTDOMAIN),
						'subtitle' => '',
						'items' => array()
					)
				)
			),
			'buttons' => array(
				'edit' 	=> __("Edit Widget Parameter", XF_TEXTDOMAIN),
				'del'	=> __("Delete Widget", XF_TEXTDOMAIN),
				'margin_r' => __("enable/disable right margin", XF_TEXTDOMAIN),
				'margin_l' => __("enable/disable left margin", XF_TEXTDOMAIN)
			),
			'permission' => $xtreme_widget_manager->current_user_has_right()
		);
		if( $xtreme_widget_manager->current_user_has_right() ) {
			foreach($xtreme_widget_manager->widgets_xtreme as $widget) {
				if(!$xtreme_widget_manager->is_widget_enabled($widget)) continue;
				if(isset($wp_widget_factory->widgets[$widget])) {
					$name = $wp_widget_factory->widgets[$widget]->name;
					$desc = $wp_widget_factory->widgets[$widget]->widget_options['description'];
				}		
				$avail['mainmenu']['submenus'][0]['items'][] = array('title' => $name, 'widget' => $widget);
			}
			$avail['mainmenu']['submenus'][0]['subtitle'] = sprintf(__('Count Widgets: %d', XF_TEXTDOMAIN),count($avail['mainmenu']['submenus'][0]['items']));
			foreach($xtreme_widget_manager->widgets_custom as $widget) {
				if(!$xtreme_widget_manager->is_widget_enabled($widget)) continue;
				if(isset($wp_widget_factory->widgets[$widget])) {
					$name = $wp_widget_factory->widgets[$widget]->name;
					$desc = $wp_widget_factory->widgets[$widget]->widget_options['description'];
				}		
				$avail['mainmenu']['submenus'][1]['items'][] = array('title' => $name, 'widget' => $widget);
			}
			$avail['mainmenu']['submenus'][1]['subtitle'] = sprintf(__('Count Widgets: %d', XF_TEXTDOMAIN),count($avail['mainmenu']['submenus'][1]['items']));
			foreach($xtreme_widget_manager->widgets_wordpress as $widget) {
				if(!$xtreme_widget_manager->is_widget_enabled($widget)) continue;
				if(isset($wp_widget_factory->widgets[$widget])) {
					$name = $wp_widget_factory->widgets[$widget]->name;
					$desc = $wp_widget_factory->widgets[$widget]->widget_options['description'];
				}		
				$avail['mainmenu']['submenus'][2]['items'][] = array('title' => $name, 'widget' => $widget);
			}
			$avail['mainmenu']['submenus'][2]['subtitle'] = sprintf(__('Count Widgets: %d', XF_TEXTDOMAIN),count($avail['mainmenu']['submenus'][2]['items']));
		}
		$params['xtreme_tinymce_widgets_supported'] = json_encode($avail);

		$post = get_post();
		if( $post !== null ){
			$params['post_ID'] = $post->ID;
		}

		return $params;
	}
	
	function on_tiny_mce_ajax_xcontentwidget() {
		global $xtreme_widget_manager, $wp_widget_factory;

		if ( !$xtreme_widget_manager->current_user_has_right() ) {
			_e( 'Cheatin&#8217; uh?', XF_TEXTDOMAIN );
			exit();
		}
	
		//input values
		$post_id = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
		$widget_id = isset($_REQUEST['widget_id']) ? absint($_REQUEST['widget_id']) : 0;
		$widget_class = isset($_REQUEST["widget_class"]) ? $_REQUEST["widget_class"] : false;
		$align = isset($_REQUEST["align"]) && in_array($_REQUEST["align"], array('none','left','right','center')) ? $_REQUEST["align"] : 'none';
		$width = isset($_REQUEST["width"]) ? $_REQUEST["width"] : 'auto';
		$sidemargins = isset($_REQUEST["sidemargins"]) && in_array($_REQUEST["sidemargins"], array('on', 'off')) ? $_REQUEST["sidemargins"] : 'on';
				
		//meta data
		$meta = get_post_meta($post_id, "_xcontentwidgets", true);
		$val = unserialize(base64_decode($meta));
		
		//meta initial assignment
		if (empty($val)) {
			$val = array( 'next_id' => 1 , 'widgets' => array());
			$meta = base64_encode(serialize($val));
			update_post_meta($post_id, "_xcontentwidgets", $meta );
		}
		
		//standard data
		$data = array();
		
		//update - load existing data
		if ($_REQUEST['action2'] == 'update') {
			if (isset($val['widgets'][$widget_id])) {
				$saved = $val['widgets'][$widget_id];
				$widget_class = $saved['class'];
				$data = $saved['data'];	
			} else {
				set_current_screen('iframe');
				wp_enqueue_script('tinymce-popup-script', site_url('wp-includes/js/tinymce/tiny_mce_popup.js'));
				iframe_header(__('Content Widget - Error', XF_TEXTDOMAIN));
				echo '<p>'. __('The requested Widget data can not be found. This may be the result while you deleted the widget but reloaded the content without prior save. Please remove this Widget and save the content.', XF_TEXTDOMAIN) . '</p>';
				iframe_footer(); 
				exit();
			}
		}
		
		//save - overwrite data
		if ($_REQUEST['action2'] == 'save') {
				
			$data = stripslashes_deep($_REQUEST["widget-".$wp_widget_factory->widgets[$widget_class]->id_base][1]);
			if ($widget_id === 0) {
				$widget_id = absint($val['next_id']);
				$val['next_id'] = $widget_id + 1;
			}
			
			$w = new $widget_class();
			ob_start();
			$instance = $w->update($data, array());
			$instance = apply_filters('widget_update_callback', $instance, $data, array(), $w);
			ob_end_clean();
			
			//sample the new value for meta save
			$val['widgets'][$widget_id] = array(
				'class' => $widget_class,
				'data' => $instance
			);		
				
			$meta = base64_encode(serialize($val));
			update_post_meta($post_id, "_xcontentwidgets", $meta );
			
			clean_post_cache( $post_id );
			
			$shortcode = 'xwidget3rdparty';
			if (in_array($widget_class, $xtreme_widget_manager->widgets_xtreme)){
				$shortcode = 'xwidgetxtreme';
			}
			if (in_array($widget_class, $xtreme_widget_manager->widgets_wordpress)){
				$shortcode = 'xwidgetwordpress';
			}
			echo "[$shortcode id=$post_id.$widget_id align=$align width=$width sidemargins=$sidemargins]";
			exit();
			
		}
	
		set_current_screen('iframe');
	
		$w = new $widget_class();
		$w->_set(1);
	
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		wp_enqueue_style('xconententwidgets-style', XF_ADMIN_URL.'/js/tinymce/css/iframe.css');
		wp_enqueue_script('tinymce-popup-script', site_url('wp-includes/js/tinymce/tiny_mce_popup.js'));
		wp_enqueue_script('xconententwidgets-script', XF_ADMIN_URL.'/js/tinymce/js/widget_iframe'.XF_ADMIN_SCRIPTS, array('jquery'));
		wp_enqueue_script('xtreme-widgets-admin', XF_ADMIN_URL . '/js/xtreme-widgets-admin'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);
		wp_enqueue_script('xtreme-widgets-config', XF_ADMIN_URL . '/js/xtreme-widgets-config'.XF_ADMIN_SCRIPTS, array('jquery', 'xtreme-widgets-admin','jquery-ui-dialog'), false, false);
		wp_enqueue_script('xtreme-widgets-filter', XF_ADMIN_URL . '/js/xtreme-widgets-filter'.XF_ADMIN_SCRIPTS, array('jquery','xtreme-widgets-admin','jquery-ui-dialog','jquery-ui-sortable'));
		
		global $hook_suffix;
		$hook_suffix = 'widgets.php';
		
		iframe_header(__('Content Widget', XF_TEXTDOMAIN));
		?>
		<form method="post" action="" autocomplete="off">
			<h2><?php echo $w->name; ?></h2>
			<h4><i><?php echo $w->widget_options["description"] ?></i></h4>
			<hr/>
			<div class="alignleft">
				<h3><?php  _e('Alignment', XF_TEXTDOMAIN) ?></h3>
				<p id="widget-alignment">
					<input id="xfw-none" type="radio" <?php checked($align, 'none'); ?> value="none" name="align"><label for="xfw-none" class="align image-align-none-label"><?php _e('None', XF_TEXTDOMAIN) ?></label>
					<input id="xfw-left" type="radio" <?php checked($align, 'left'); ?> value="left" name="align"><label for="xfw-left" class="align image-align-left-label"><?php _e('Left', XF_TEXTDOMAIN) ?></label>
					<input id="xfw-center" type="radio" <?php checked($align, 'center'); ?> value="center" name="align"><label for="xfw-center" class="align image-align-center-label"><?php _e('Center', XF_TEXTDOMAIN) ?></label>
					<input id="xfw-right" type="radio" <?php checked($align, 'right'); ?> value="right" name="align"><label for="xfw-right" class="align image-align-right-label"><?php _e('Right', XF_TEXTDOMAIN) ?></label>
				</p>
			</div>
			<div class="alignleft">
				<h3><?php  _e('Width', XF_TEXTDOMAIN) ?></h3>
				<p id="widget-sizes">
					<label><input id="widget-width" type="text" value="<?php echo $width; ?>" name="width" size="5"></label>
				</p>
			</div>
			<br class="clear" />
			<div id="widget-<?php echo $w->id; ?>" class="widget mce-widget">
				<input type="hidden" name="post_id" value="<?php echo absint($_REQUEST['post_id']) ?>" />
				<input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>" />
				<input type="hidden" name="widget_class" value="<?php echo get_class($w); ?>" />
				<?php 
					$instance = apply_filters('widget_form_callback', $data, $w);
					$return = @$w->form($instance);
					do_action_ref_array( 'in_widget_form', array(&$w, &$return, $instance) );
				?>
			</div>
			<input type="hidden" name="sidemargins" value="<?php echo $sidemargins;?>" />
		</form>
		<div class="mceActionPanel">
			<div style="margin: 8px auto; text-align: center;padding-bottom: 10px;">
				<?php if ($_GET['action2'] == 'update') : ?>
				<input type="button" class="mceButton" title="<?php _e('Update Content Widget Parameter', XF_TEXTDOMAIN) ?>" value="<?php _e('Update', XF_TEXTDOMAIN) ?>" name="update" id="xf_mce_update">
				<?php else : ?>
				<input type="button" class="mceButton" title="<?php _e('Insert New Content Widget', XF_TEXTDOMAIN) ?>" value="<?php _e('Insert', XF_TEXTDOMAIN) ?>" name="insert" id="xf_mce_insert">
				<?php endif; ?>
			</div>
		</div>		
		<?php
		iframe_footer(); 
		exit();
	}
	
	function on_mce_save_post_cleanup($post_ID, $post) {
		
		if (wp_is_post_revision($post_ID))
			return;
		
		$post_id = absint($post_ID);
		$content = $post->post_content;
		if (preg_match_all('/\[(xwidgetwordpress|xwidget3rdparty|xwidgetxtreme)\s+id=(\d+)\.(\d+)/i', $content, $hits)) {
			$contained = array();
			foreach($hits[3] as $widget_id) {$contained[] = (int)$widget_id; };
			
			$meta = get_post_meta($post_id, "_xcontentwidgets", true);
			$val = unserialize(base64_decode($meta));
			
			if (!empty($val)) {
				$keys = array_keys($val['widgets']);
				$do_cleanup = false;
				foreach($keys as $key) {
					if (!in_array($key, $contained)) {
						$do_cleanup = true;
						unset($val['widgets'][$key]);
					}
				}
				if ($do_cleanup) {
					$meta = base64_encode(serialize($val));
					update_post_meta($post_id, "_xcontentwidgets", $meta );
				}
			}				
		}
	}
	
	function on_repair_wp_import($post_id, $key, $value) {
		
		if($key == '_xcontentwidgets' && !empty($value)) {
			$val = unserialize(base64_decode($value));
			if(count($val['widgets']) > 0) {
				//we have to patch the shortcodes now
				$post = &get_post($post_id);
				
				$search 	= '/\[(xwidgetwordpress|xwidget3rdparty|xwidgetxtreme)\s+id=(\d+)\.(\d+)/i';
				$replace 	= '[$1 id='.$post_id.'.$3';
				
				$content = preg_replace($search, $replace, $post->post_content);
				
				//update database now
				global $wpdb;			
				$where = array( 'ID' => $post_id );
				$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), $where );
				
			}
		}
	}
}

$xtreme_widgets_admin = new Xtreme_Widgets_Admin();
