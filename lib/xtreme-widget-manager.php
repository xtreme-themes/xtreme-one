<?php

function xtreme_monitor_blog_switch($blog_id, $prev_blog_id) {
	/*
	//patch known issues for other world used plugins
	//1.) next gen gallery
	global $wpdb;
	$wpdb->nggpictures		= $wpdb->prefix . 'ngg_pictures';
	$wpdb->nggallery		= $wpdb->prefix . 'ngg_gallery';
	$wpdb->nggalbum			= $wpdb->prefix . 'ngg_album';
	*/
}
add_action('switch_blog', 'xtreme_monitor_blog_switch', 99, 2);

class Xtreme_Widget_Instance_Helper {
	
	function __construct($class, $widget_id) {
		
		static $counter = 0;
		
		$this->id = $widget_id;
		$this->classname = $class;
		preg_match('/(.*)\-(\d+)$/', $this->id, $hits);
		$this->id_base = $hits[1];
		$this->number = $hits[2];
		$widget_instances = get_blog_option(XF_SITE_ID, 'widget_'.$this->id_base, array());		
		$this->instance = $widget_instances[$this->number];
		//make burnings unique
		$counter += 1;
		$this->number = $counter;
		$this->id = $this->id_base . '-' . $this->number;
	}
	
	function Xtreme_Widget_Instance_Helper($class, $widget_id) {
		$this->__construct($class, $widget_id);
	}
}
 
class Xtreme_Widget_Base extends WP_Widget {
	
	function __construct( $file = false, $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct($id_base, $name, $widget_options, $control_options);
		$this->path_part = '';
		if ($file !== false) {
			$this->path_part = str_replace('.php', '', basename($file));
		}
	}
	
	function Xtreme_Widget_Base( $file = false, $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		$this->__construct($file, $id_base, $name, $widget_options, $control_options );
	}
	
	function convert_old_widget($instance) {
		return $instance;
	}
	
	function get_ajax_action($suffix) {
		return strtolower(get_class($this)).'_'.$suffix;
	}
	
	function ensure_widget_scripts($instance) {
		//override it for widget needs
	}
};
 
class Xtreme_Widget_Manager {
	 
	function __construct() {
		//setup the WordPress build in list
		$this->widgets_wordpress = array(
			'WP_Widget_Pages', 
			'WP_Widget_Calendar', 
			'WP_Widget_Archives', 
			'WP_Widget_Links', 
			'WP_Widget_Meta', 
			'WP_Widget_Search', 
			'WP_Widget_Text', 
			'WP_Widget_Categories', 
			'WP_Widget_Recent_Posts', 
			'WP_Widget_Recent_Comments', 
			'WP_Widget_RSS', 
			'WP_Widget_Tag_Cloud', 
			'WP_Nav_Menu_Widget'
		);
		$this->widgets_xtreme 			= array();
		$this->widgets_custom			= array();
		$this->widgets_deprecated		= array();
		$this->widgets_deprecated_off	= array();
		$this->widgets_all				= array();
		
		$this->is_frontpage_blog = ('page' == get_option( 'show_on_front'));
		
		$this->filters = array();
		if($this->is_frontpage_blog) {
			$this->filters['is_front_page'] =  __('Front Page', XF_TEXTDOMAIN);
		}
		$this->filters['is_home'] = __('Blog Index', XF_TEXTDOMAIN);
		$this->filters['is_singular'] = __('All', XF_TEXTDOMAIN);
		$this->filters['is_single'] = __('Post', XF_TEXTDOMAIN);
		$this->filters['is_page'] = __('Page', XF_TEXTDOMAIN);
		$this->filters['is_attachment'] = __('Media', XF_TEXTDOMAIN);
		$this->filters['is_paged'] = __('Paging', XF_TEXTDOMAIN);
		$this->filters['is_archive'] = __('All', XF_TEXTDOMAIN);
		$this->filters['is_category'] = __('Category', XF_TEXTDOMAIN);
		$this->filters['in_category'] = __('Category', XF_TEXTDOMAIN);
		$this->filters['is_tag'] = __('Tag', XF_TEXTDOMAIN);
		$this->filters['has_tag'] = __('Tag', XF_TEXTDOMAIN);
		$this->filters['is_author'] = __('Author', XF_TEXTDOMAIN);
		$this->filters['has_author'] = __('Author', XF_TEXTDOMAIN);
		$this->filters['is_date'] = __('Date', XF_TEXTDOMAIN);
		$this->filters['is_day'] = __('Day', XF_TEXTDOMAIN);
		$this->filters['is_month'] = __('Month', XF_TEXTDOMAIN);
		$this->filters['is_year'] = __('Year', XF_TEXTDOMAIN);
		$this->filters['is_time'] = __('Time', XF_TEXTDOMAIN);
		$this->filters['is_search'] = __('Search', XF_TEXTDOMAIN);
		$this->filters['is_404'] = __('404 Error', XF_TEXTDOMAIN);
		$this->filters['post-format'] = _x('Format', 'post format', XF_TEXTDOMAIN);
		$this->filters['in_postformat'] = _x('Format', 'post format', XF_TEXTDOMAIN);
		$this->filters['has_role'] = __('Roles', XF_TEXTDOMAIN);
		$this->filters['has_cap'] = __('Capabilities', XF_TEXTDOMAIN);
		$this->filters['is_mobile_device'] = __('Mobile Device', XF_TEXTDOMAIN);
		$this->filters['is_mobile_device_archive'] = __('Mobile Device', XF_TEXTDOMAIN);
				
		$this->filter_archives = array();
		$this->filter_archives[] = 'is_archive';
		$this->filter_archives[] = 'is_date';
		$this->filter_archives[] = 'is_day';
		$this->filter_archives[] = 'is_month';
		$this->filter_archives[] = 'is_year';
		$this->filter_archives[] = 'is_time';
		$this->filter_archives[] = 'is_search';
		$formats = get_theme_support('post-formats');
		$post_format_count = count($formats[0]);
		if($post_format_count > 0) {
			$this->filter_archives[] = 'post-format';
		}
		$this->filter_archives[] = 'is_category';
		$this->filter_archives[] = 'is_tag';
		$this->filter_archives[] = 'is_author';

		$this->filter_post_types = array();
		$this->filter_post_types[] = 'is_singular';
		if ($this->is_frontpage_blog) {
			$this->filter_post_types[] = 'is_front_page';
		}
		$this->filter_post_types[] = 'is_home';
		$this->filter_post_types[] = 'is_404';
		$this->filter_post_types[] = 'is_mobile_device';
		$this->filter_post_types[] = 'is_attachment';
		$this->filter_post_types[] = 'is_single';
		$this->filter_post_types[] = 'is_page';
			
		$this->filter_specials = array(
			'is_home',
			'is_404'
		);
		
		if ($this->is_frontpage_blog) {
			$this->filter_specials[] = 'is_front_page';
		}
	
		$this->filters_with_exceptions = array(
			'is_single', 
			'is_attachment', 
			'is_page', 
			'is_category', 
			'in_category',
			'is_tag', 
			'has_tag',
			'is_author',
			'has_author',
			'is_paged',
			'in_postformat',
			'has_role',
			'has_cap',
			'is_mobile_device',
			'is_mobile_device_archive'
		);
		if($post_format_count > 0) {
			$this->filters_with_exceptions[] = 'post-format';
		}

		$this->built_in_post_types = array('post', 'page', 'attachment', 'revision', 'nav_menu_item');
		$this->built_in_post_types = apply_filters( 'xtreme-built-in-post-types', $this->built_in_post_types );
		
		$this->built_in_taxonomies = array('category', 'post_tag', 'nav_menu', 'link_category', 'post_format');
		$this->built_in_taxonomies = apply_filters( 'xtreme-built-in-taxonomies', $this->built_in_taxonomies );
		
		// Load potential widget files and collect classes
		$files = array_diff( scandir( XF_WIDGETS_DIR ), array( '.', '..' ) );
		
		//load potential widget files and collect classes
		$files = array_diff( scandir( XF_WIDGETS_DIR ), array( '.', '..' ) );
		
		// Filter hook for filter include widgets from core
		$files = apply_filters( 'xtreme-include-core-widgets', $files );
		
		foreach($files as $file) {
			
			if (is_file(XF_WIDGETS_DIR.'/'.$file) && preg_match('|\.php$|', $file) ) {
				require_once XF_WIDGETS_DIR.'/'.$file;
			}elseif(is_dir(XF_WIDGETS_DIR.'/'.$file) && is_file(XF_WIDGETS_DIR.'/'.$file.'/'.$file.'.php')) {
				require_once XF_WIDGETS_DIR.'/'.$file.'/'.$file.'.php';
			}
		}
		$this->widgets_xtreme = apply_filters( 'xtreme-collect-widget-classes', array() );
		
		//initial permission options
		$this->options = $this->__get_defaults();

		//waiting until option router is ready
		add_action('xtreme_setup_theme', array(&$this, 'on_load_options'));
		add_action('admin_post_save_xtreme_widgets', array(&$this, 'on_save_options'));

		
		//attention: global mapping of widgets will be performed at priority 100,
		//so any higher priority will avoid register/unregister of widgets!
		add_action('widgets_init', array(&$this, 'on_deactivate_widgets'), 99);

		add_action('load-widgets.php', array(&$this, 'on_enqueue_widgets_php_stuff'));		
		
		//extensions for all widgets base on new widget system (no deprecated widgets)
		add_action('in_widget_form', array(&$this, 'on_form_ext_main'),9 ,3);
		add_action('in_widget_form', array(&$this, 'on_form_ext_tabs'),12 ,3);
		add_action('in_widget_form', array(&$this, 'on_form_ext_filters'),11 ,3);
		add_filter('widget_update_callback', array(&$this, 'on_form_ext_save_extensions'), 10, 4);
		add_action('admin_footer-widgets.php', array(&$this, 'on_form_ext_print_footer_helper'));
		
		//force widgets at multiside sub sites
		if (is_multisite() && XF_IS_MAIN_BLOG === true) {
			add_action('in_widget_form', array(&$this, 'on_form_ext_burnings'),10 ,3);
			add_filter('widget_update_callback', array(&$this, 'on_form_ext_save_burnings'), 10, 4);
		}
		
		if(!is_admin()){
			add_filter('dynamic_sidebar_params', array(&$this, 'on_form_ext_tabs_patch_widgets'), 98, 1);
		}
		
		if (is_multisite() && XF_IS_MAIN_BLOG === false) {
			if(is_admin()) {
				add_action('admin_footer-widgets.php', array(&$this, 'on_form_ext_burnings_childblog_info'));
			}else {
				add_filter('dynamic_sidebar_params', array(&$this, 'on_form_ext_burnings_force_widgets'), 99, 1);
				add_filter('sidebars_widgets', array(&$this, 'on_form_ext_burnings_extend_frontend_sidebars'));
			}
		}	
		
		add_action('init', array(&$this, 'initialize_custom_post_types_and_taxonomies'),9999);
		
		//global widget output filter
		add_filter('widget_display_callback', array(&$this, 'on_filter_widget_output'), 0, 3);
		
		//inplace widgets
		$this->shortcode_group = 0;
		add_action('xtreme_before_header', array( &$this, 'on_xcontentwidget_shortcode_grouping'));
		add_action('xtreme_before_teaser', array( &$this, 'on_xcontentwidget_shortcode_grouping'));
		add_action('xtreme_before_main', array( &$this, 'on_xcontentwidget_shortcode_grouping'));
		add_action('xtreme_before_footer', array( &$this, 'on_xcontentwidget_shortcode_grouping'));
		add_action('xtreme_before_siteinfo', array( &$this, 'on_xcontentwidget_shortcode_grouping'));
		add_shortcode('xwidgetxtreme', array( &$this, 'on_xcontentwidget_shortcode') );
		add_shortcode('xwidgetwordpress', array( &$this, 'on_xcontentwidget_shortcode') );
		add_shortcode('xwidget3rdparty', array( &$this, 'on_xcontentwidget_shortcode') );
	}
	
	function Xtreme_Widget_Manager() {
		$this->__construct();
	}
	
	function initialize_custom_post_types_and_taxonomies() {
		//collect custom post types and apply them to the list
		$posttypes = get_post_types(array(), 'objects');
		$register = array();
		foreach($posttypes as $name => $object) {
			if (!in_array($name, $this->built_in_post_types)) {
				$this->filters['x-cpt-'.$name] = $object->labels->singular_name;
				$this->filters['x-cpta-'.$name] = $object->labels->singular_name;
				$this->filter_post_types[] = 'x-cpt-'.$name;
				$this->filter_archives[] = 'x-cpta-'.$name;
				$this->filters_with_exceptions[] = 'x-cpt-'.$name;
			}
		}
		
		//collect taxonomies to be supported
		$taxonomies = get_taxonomies(array(), 'objects');
		$register = array();
		foreach($taxonomies as $name => $object) {
			if (!in_array($name, $this->built_in_taxonomies)) {
				
				/*
				$key = "taxa|$name|";
				$terms = get_terms($name);
				if (count($terms) > 0) {
					foreach($terms as $term) {
						$this->filters[$key.$term->slug] = $object->labels->singular_name . ': ' . $term->name;
						$this->filter_archives[] = $key.$term->slug;
					}
				}
				*/
				
				$this->filters['x-tax-'.$name] = $object->labels->singular_name;
				$this->filters['x-taxa-'.$name] = $object->labels->singular_name;
//				$this->filter_post_types[] = 'x-tax-'.$name;
				$this->filter_archives[] = 'x-taxa-'.$name;
				$this->filters_with_exceptions[] = 'x-tax-'.$name;
				$this->filters_with_exceptions[] = 'x-taxa-'.$name;
			}
		}
				
		
	}

	//general methods
	
	function is_general_disabled() {
		return !$this->options['general_permission'];
	}
	
	function is_general_enabled() {
		return !$this->is_general_disabled();
	}
	
	function is_filtersystem_enabled() {
		return $this->options['enable_filter_system'];
	}

	function is_filtersystem_disabled() {
		return !$this->is_filtersystem_enabled();
	}
	
	function is_auto_tabs_enabled() {
		return isset($this->options['enable_auto_tabs']) ? $this->options['enable_auto_tabs'] : false;
	}
	
	function current_content_widget_role() {
		return isset($this->options['content_widget_role']) ? $this->options['content_widget_role'] : 'administrator';
	}

	/**
	 * added a new helper function to enable the widgets also to admins, when the select role is not admin
	 *
	 * @since   1.5.999
	 * @uses    current_user_can(), $this->current_content_widget_role()
	 * @return  bool $has_right
	 */
	function current_user_has_right(){
		$role = $this->current_content_widget_role();

		if( $role !== 'administrator' && current_user_can( 'administrator' ) ){
			return true;
		}
		else if( current_user_can( $role ) ) {
			return true;
		}

		return false;

	}

	function get_filters() {
		return $this->options['filter'];
	}
	
	function is_widget_disabled($class) {
		if(in_array($class, $this->options['widgets-disabled']))
			return true;
		if($this->is_general_disabled() && !in_array($class, $this->options['widgets-general-enabled']))
			return true;
		return false;
	}

	function is_widget_enabled($class) {
		return !$this->is_widget_disabled($class);
	}
	
	// internal methods
	
	function __get_defaults() {
		$res = array(
			'general_permission' => true,
			'widgets-disabled' => array(),
			'widgets-general-enabled' => array(),
			'enable_filter_system' => false,
			'enable_auto_tabs' => false,
			'content_widget_role' => 'administrator',
			'filter' => array()
		);
		foreach($this->filters as $filter => $name) {
			$res['filter'][$filter] = array();
		}
		return $res;
	}
	
	function __print_filter_section_xtreme_widget_admin_subset($subset, $widget, $warning = false) {
		$filters = $this->get_filters();
		$w_c = '';
		$w_cr = '';
		if($warning) {
			if (in_array('is_archive', $subset)) {
				$w_c = 'warning-is_archive';
			}
			elseif(in_array('is_singular', $subset)) {
				$w_c = 'warning-is_singular';
			}
		}
		foreach($subset as $value) : 
			$label = $this->filters[$value];
			if(
				(
					(isset($filters['is_archive']) && in_array($widget, $filters['is_archive']))
					||
					(isset($filters['is_singular']) && in_array($widget, $filters['is_singular']))
				)
				&& 
				!in_array($value, array('is_archive','is_singular'))
			){
				$w_cr = $w_c.' x-warning';
			}
			else{
				$w_cr = $w_c;
			}
		?>
			<div style="border-bottom: 1px solid #CDCDCD;" class="<?php if($warning && !in_array($value, array('is_archive','is_singular'))) echo "$w_cr" ?>" >
				<label>
					<input class="checkbox-js checkbox-inverse" type="checkbox" name="filter-<?php echo $value;?>[]" value="<?php echo $widget;?>"<?php checked(true, isset($filters[$value]) && in_array($widget, $filters[$value])); ?>/>&nbsp;<?php echo $label; ?>
				</label>
			</div>
		<?php endforeach;
	}
	
	function __print_filter_section_xtreme_widget_admin($widget) {
		?>
		<div class="x-filter-section x-hidden">
			<p><?php echo sprintf(__('Filter for: <strong>%s</strong>', XF_TEXTDOMAIN), __('Archive Pages', XF_TEXTDOMAIN));?></p>
			<?php $this->__print_filter_section_xtreme_widget_admin_subset($this->filter_archives, $widget, true); ?>
			<p><?php echo sprintf(__('Filter for: <strong>%s</strong>', XF_TEXTDOMAIN), __('Single Pages', XF_TEXTDOMAIN));?></p>
			<?php $this->__print_filter_section_xtreme_widget_admin_subset($this->filter_post_types, $widget, true); ?>
			<p>
				<small class="x-remark"><b><?php _e('Remark:', XF_TEXTDOMAIN);?></b> <?php _e('Switching off a template type here will suppress output of this widget at this template type in any case during page delivery.',XF_TEXTDOMAIN); ?></small>
			</p>
		</div>
		<?php
	}
	
	function __print_filter_section_form_ext_subset($subset, $widget_obj, $instance, $warning = false, $do_joins = false) {
		$global_filters = $this->get_filters();
		$widget_filters = isset($instance['xtreme-filters']) ? $instance['xtreme-filters'] : array();
		$name = get_class($widget_obj);
		$w_c = '';
		if($warning) {
			if (in_array('is_archive', $subset)) {
				$w_c = 'warning-is_archive';
				if(
					(isset($global_filters['is_archive']) && in_array($name, $global_filters['is_archive']))
					||
					(in_array('is_archive', $widget_filters))
				) {
					$w_c .= ' x-warning';
				}
			}
			elseif(in_array('is_singular', $subset)) {
				$w_c = 'warning-is_singular';
				if(
					(isset($global_filters['is_singular']) && in_array($name, $global_filters['is_singular']))
					||
					(in_array('is_singular', $widget_filters))
				){
					$w_c .= ' x-warning';
				}
			}
		}
		foreach($subset as $value) : 
			$label = $this->filters[$value];
			$locked = (isset($global_filters[$value]) && in_array($name, $global_filters[$value])) ? ' x-no-locked' : '';
			$count = 0;
			$fex = isset($instance['xtreme-fex_'.$value]) ? $instance['xtreme-fex_'.$value] : "";
			$fec = isset($instance['xtreme-fec_'.$value]) ? $instance['xtreme-fec_'.$value] : "";
			if(!empty($fex)) {
				$count = count(explode(',',$fex));
			}
			if(!empty($fec)) {
				$count++;
			}
		?>
			<div class="x-filter<?php if($count > 0) echo " fex-selected"; ?> <?php if($warning && !in_array($value, array('is_archive','is_singular'))) echo "$w_c" ?>" style="border-bottom: 1px solid #cdcdcd;">
				<label>
					<input class="checkbox-js checkbox-inverse<?php echo $locked; ?>" type="checkbox" name="<?php echo $widget_obj->get_field_name('xtreme-filters');?>[]" value="<?php echo $value;?>"<?php checked(true, in_array($value, $widget_filters)); ?>/>&nbsp;<span><?php echo $label; ?></span>
				</label>
				<?php if (in_array($value, $this->filters_with_exceptions)) : ?>
					<div class="xtreme-fex">
						<?php if (!empty($locked)) : ?>
							<small><em>( <?php _e("exceptions denied by general setting", XF_TEXTDOMAIN); ?> )</em></small>
						<?php else: ?>
							<?php if($do_joins && $value != 'is_singular' && $value != 'is_attachment' && $value != 'is_mobile_device' && $value != 'is_mobile_device_archive') : ?>
								<small><a class="x-dlg-expressions" href="#"><?php _e("Condition", XF_TEXTDOMAIN); ?></a></small>&nbsp;|&nbsp;
							<?php endif; ?>
							<small><a class="x-dlg-exceptions" href="#"><?php _e("Exceptions", XF_TEXTDOMAIN); ?></a></small>&nbsp;&nbsp;
						<?php endif; ?>
						<input type="hidden" class="fec-input" name="<?php echo $widget_obj->get_field_name('xtreme-fec_'.$value);?>" value="<?php echo $fec; ?>" />						
						<input type="hidden" class="fex-input" name="<?php echo $widget_obj->get_field_name('xtreme-fex_'.$value);?>" value="<?php echo $fex; ?>" />						
					</div>
				<?php endif; ?>
			</div>
		<?php 
		endforeach;
		echo "<br/>";
	}
	
	function __print_filter_section_form_ext($widget_obj, $instance) {
		$name = get_class($widget_obj);
		?>
		<div class="x-filter-section" style="display:none;">
			<p><?php echo sprintf(__('Filter for: <strong>%s</strong>', XF_TEXTDOMAIN), __('Archive Pages', XF_TEXTDOMAIN));?></p>
			<?php $this->__print_filter_section_form_ext_subset($this->filter_archives, $widget_obj, $instance, true); ?>
			<p><?php echo sprintf(__('Filter for: <strong>%s</strong>', XF_TEXTDOMAIN), __('Single Pages', XF_TEXTDOMAIN));?></p>
			<?php $this->__print_filter_section_form_ext_subset($this->filter_post_types, $widget_obj, $instance, true, true); ?>
			<br class="clear"/>
		<p>
			<small class="x-remark"><b><?php _e('Remark:', XF_TEXTDOMAIN);?></b> <?php _e('Switching off a template type here will suppress output of this special widget at this template type during page delivery.',XF_TEXTDOMAIN); ?></small>
		</p>
		</div>
		<?php
	}
	
	//engine methods

	function on_load_options() {
			
		$this->options = get_option(XF_WIDGET_PERMISSIONS);
		if ($this->options == false) {
			$this->options = $this->__get_defaults();
			update_option(XF_WIDGET_PERMISSIONS, $this->options);
		}
		else{
			//check and update filters
			foreach($this->filters as $filter => $name) {
				if (!isset($this->options['filter'][$filter])) {
					$this->options['filter'][$filter] = array();
				}
			}
			//check role for content widgets
			if (!isset($this->options['content_widget_role']))
				$this->options['content_widget_role'] = 'administrator';
		}
	}
	
	function on_save_options() {
		if ( !current_user_can('edit_themes') )
            wp_die( __('Cheatin&#8217; uh?', XF_TEXTDOMAIN) );

        check_admin_referer('xtreme_backend_settings');
        $redirect = $_POST['_wp_http_referer'];
		
		$this->options = $this->__get_defaults();
		$this->options['general_permission'] = isset($_POST['general_permission']) ? true : false;
		$this->options['widgets-disabled'] = isset($_POST['disabled_widgets']) && is_array($_POST['disabled_widgets']) ? array_intersect($_POST['disabled_widgets'], $this->widgets_all) : array();
		$this->options['widgets-general-enabled'] = array_diff($this->widgets_all, $this->options['widgets-disabled']);
		$this->options['enable_filter_system'] = isset($_POST['enable_filter_system']) ? true : false;
		$this->options['enable_auto_tabs'] = isset($_POST['enable_auto_tabs']) ? true : false;
		$this->options['content_widget_role'] = (isset($_POST['content_widget_role']) && in_array($_POST['content_widget_role'], array_keys(get_editable_roles()))) ? $_POST['content_widget_role'] : 'administrator';
		foreach($this->filters as $filter => $label) {
			$entry = 'filter-'.$filter;
			$this->options['filter'][$filter] = isset($_POST[$entry]) && is_array($_POST[$entry]) ? array_intersect($_POST[$entry], $this->widgets_all) : array();
		}
		if(isset($_POST['xtreme_reset'])) {
			if($this->options['general_permission']) {
				$this->options['widgets-disabled'] = array();
				$this->options['widgets-general-enabled'] = $this->widgets_all;
			}else{
				$this->options['widgets-disabled'] = array();
				$this->options['widgets-general-enabled'] = array();
			}
		}
		update_option(XF_WIDGET_PERMISSIONS, $this->options);
				
		wp_redirect($redirect);
	}
	
	function on_deactivate_widgets() {
		global $wp_widget_factory, $wp_registered_widgets;

		array_map('register_widget', $this->widgets_xtreme); 	
		foreach($wp_widget_factory->widgets as $class => $value) {
			$this->widgets_all[] = $class;
			//register custom widgets for later handling
			if (!in_array($class, $this->widgets_wordpress) && !in_array($class, $this->widgets_xtreme))
				$this->widgets_custom[] = $class;
			
			if(
				in_array($class, $this->options['widgets-disabled']) 
				|| 
				($this->options['general_permission'] === false && !in_array($class, $this->options['widgets-general-enabled']))
			) {
				unregister_widget($class);
				continue;
			}
		}
		foreach($wp_registered_widgets as $id => $value) {
			if (!in_array($id, $this->widgets_wordpress) && !in_array($id, $this->widgets_xtreme)) {
				$this->widgets_all[] = $id;
				$this->widgets_deprecated[] = $id;
			}
				
			if(
				in_array($id, $this->options['widgets-disabled']) 
				|| 
				($this->options['general_permission'] === false && !in_array($id, $this->options['widgets-general-enabled']))
			) {
				$this->widgets_deprecated_off[$id] = array('name' => $value['name'], 'description' => $value['description'] );
				wp_unregister_sidebar_widget($id);
			}
		}
	}

	function on_enqueue_widgets_php_stuff() {
		wp_enqueue_style('xtreme-admin', XF_ADMIN_URL.'/css/xtreme-backend.css');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('xtreme-widgets-admin', XF_ADMIN_URL . '/js/xtreme-widgets-admin'.XF_ADMIN_SCRIPTS, array('jquery'), false, false);
		wp_enqueue_script('xtreme-widgets-config', XF_ADMIN_URL . '/js/xtreme-widgets-config'.XF_ADMIN_SCRIPTS, array('jquery', 'jquery-ui-sortable', 'xtreme-widgets-admin','jquery-ui-dialog'), false, false);
		wp_enqueue_script('xtreme-widgets-filter', XF_ADMIN_URL . '/js/xtreme-widgets-filter'.XF_ADMIN_SCRIPTS, array('jquery', 'xtreme-widgets-admin','jquery-ui-dialog'), false, false);
		wp_localize_script( 'xtreme-widgets-config', 'xtreme_widgets_config_l10n', 
			array(
				'erase_button' => __('Remove all widgets', XF_TEXTDOMAIN),
				'cancel_button' => __('Cancel', XF_TEXTDOMAIN)
			)
		);
		add_thickbox();
	}
	
	function on_form_ext_main($widget_obj, &$return, $instance) {
		if($this->is_auto_tabs_enabled() || $this->is_filtersystem_enabled() || (is_multisite() && XF_IS_MAIN_BLOG)) : ?>
		<h3><?php _e('Xtreme - System Extensions', XF_TEXTDOMAIN); ?></h3>
		<?php endif;
	}
	
	function on_form_ext_tabs($widget_obj, &$return, $instance) {
		if (!$this->is_auto_tabs_enabled()) return;
		$auto_tabs_id = isset( $instance['auto_tabs_id'] ) ? $instance['auto_tabs_id'] : '';
		?>
		<div>
			<p>
			<label><?php _e("Arrangement in jQuery Tabs:", XF_TEXTDOMAIN); ?></label>
			<input name="<?php echo $widget_obj->get_field_name('auto_tabs_id'); ?>" type="text" value="<?php echo esc_html($auto_tabs_id); ?>" size="10" class="widefat"/>
			</p>
			<p>
			<small class="x-remark"><b><?php _e('Remark', XF_TEXTDOMAIN) ?>: </b><?php  _e('If you set an identifier here, each widget with the same identifier will be wrapped automatically into an accessible tabber during page creation. The Title field is essential for proper working tabs.',XF_TEXTDOMAIN); ?></small><br>
			</p>
		</div>
		<?php
	}

	function on_form_ext_filters($widget_obj, &$return, $instance) {
		if($this->is_filtersystem_enabled()) {
			echo '<p class="x-filterbutton"><span>'.__('Widget Filter Settings:', XF_TEXTDOMAIN).'</span> <a class="x-filter-expander button-secondary" href="#"><span><em class="ui-icon ui-icon-arrowthickstop-1-s"></em>'.__("Show", XF_TEXTDOMAIN).'</span><span style="display:none;"><em class="ui-icon ui-icon-arrowthickstop-1-n"></em>'.__("Hide", XF_TEXTDOMAIN).'</span></a></p>';
			$this->__print_filter_section_form_ext($widget_obj, $instance);
		}
	}

	function on_form_ext_save_extensions($instance, $new_instance, $old_instance, $widget_obj) {
		//automatic tabs saving
		$who = $this->is_auto_tabs_enabled() ? $new_instance : $old_instance;
		if(isset($who['auto_tabs_id'])){
			$instance['auto_tabs_id'] = $who['auto_tabs_id'];
		}
		else{
			if(isset($instance['auto_tabs_id'])){
				unset($instance['auto_tabs_id']);
			}
		}
		//filter saving
		$who = $this->is_filtersystem_enabled() ? $new_instance : $old_instance;	
		if(isset($who['xtreme-filters'])){
			$instance['xtreme-filters'] = $who['xtreme-filters'];
		}
		else{
			if(isset($instance['xtreme-filters'])){
				unset($instance['xtreme-filters']);
			}
		}		
		//TODO: filter exceptions saving (xtreme-fex_....)
		foreach($this->filters_with_exceptions as $filter) {
			//first the exceptions
			if(isset($who['xtreme-fex_'.$filter])) {
				$instance['xtreme-fex_'.$filter] = $who['xtreme-fex_'.$filter];
			}else{
				if(isset($instance['xtreme-fex_'.$filter])){
					unset($instance['xtreme-fex_'.$filter]);
				}
			}
			//second the conditions
			if(isset($who['xtreme-fec_'.$filter])) {
				$instance['xtreme-fec_'.$filter] = $who['xtreme-fec_'.$filter];
			}else{
				if(isset($instance['xtreme-fec_'.$filter])){
					unset($instance['xtreme-fec_'.$filter]);
				}
			}
		}
		return $instance;
	}

	function on_form_ext_print_footer_helper() {
		?>
		<p class="x-duplicate" id="x-duplicate"><a href="#"><?php _e('Duplicate Widget',XF_TEXTDOMAIN); ?></a></p>
		<a id="x-erase" style="display:none" class="x-erase" href="#" title="<?php _e('Empty this Sidebar...',XF_TEXTDOMAIN); ?>"></a>
		<div id="dialog-confirm" style="display:none" title="<?php _e('Empty this Sidebar?',XF_TEXTDOMAIN); ?>">
			<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php _e('Assigned widgets will be permanently deleted and cannot be recovered.',XF_TEXTDOMAIN); ?></p>
			<p class="question"><?php _e('Are you sure?',XF_TEXTDOMAIN); ?></p>
			<div class="progressbar" style="display:none">
				<div class="widget" style="height:17px; border:1px solid #DDDDDD; background:#F9F9F9;width:100%; margin: 3px 0;">
					<div class="widget" style="width: 0%;height:100%;background:#21759B!important;border-width:0px;text-shadow:0 1px 0 #000000;color:#FFFFFF;text-align:right;font-weight:bold;font-size:12px;margin-bottom:4px;"><div style="padding:2px 6px; white-space:nowrap;word-wrap:normal!important;overflow: hidden;">0&nbsp;%</div></div>
				</div>
			</div>
		</div>
		<?php
	}
	
	function on_form_ext_tabs_patch_widgets($sidebar_params) {	
		$option_name = 'widget_'.preg_replace('/-\d+$/','', $sidebar_params[0]['widget_id']);
		$widgets = get_option($option_name);
		if ($widgets !== false && isset($widgets[$sidebar_params[1]['number']])) {
			$widget = $widgets[$sidebar_params[1]['number']];
			if(
				isset($widget['auto_tabs_id'])
				&&
				!empty($widget['auto_tabs_id'])
				&&
				(
					(
						isset($widget['title'])
						&&
						!empty($widget['title'])
					)
					||
					($option_name == "widget_links")
				)
			) {
				//now look for patching the tab groups inside
				$replacement = 'widget widget_xtreme_grouped widget_group_id_'.strtolower(sanitize_title($widget['auto_tabs_id'])).' ';				
				$sidebar_params[0]['before_widget'] = str_replace('widget ', $replacement, $sidebar_params[0]['before_widget']);
				
				//WordPress is semi-intelligent! it doesn't handle the later enqueue of scripts well, even if it could successful resolved at footer!
				//So we do a workaround therefor here.
				//check for safety for jquery first (should not happen because global jquery enqueue by xtreme framework)
				global $xtreme_script_manager;
				$xtreme_script_manager->ensure_jquery();
				$xtreme_script_manager->ensure_accessible_tabs();
				$xtreme_script_manager->ensure_widget_groups();
			}
		}
				
		return $sidebar_params;
	}

	
	function on_form_ext_burnings($widget_obj, &$return, $instance) {
		$class = get_class($widget_obj);
		if(!in_array($class, $this->widgets_wordpress) && !in_array($class, $this->widgets_xtreme)) return; //avoid 3rd party widgets
		$burn_it = (isset($instance['xtreme_burn_it']) && $instance['xtreme_burn_it'] == 1) ? 1 : 0;
		$burn_it_context = isset($instance['xtreme_burn_it_context']) ? $instance['xtreme_burn_it_context'] : 'main';
		$burn_it_childonly = isset($instance['xtreme_burn_it_childonly']) && $instance['xtreme_burn_it_childonly'] == 1 ? 1 : 0;
		?>
		<div class="xtreme-widget-burn-it">
		<p>
			
			<input id="<?php echo $widget_obj->get_field_id( 'xtreme_burn_it' ) ?>" name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it' ) ?>" type="checkbox" value="1" <?php checked(1, $burn_it); ?> />
			<label style="color: red;" for="<?php echo $widget_obj->get_field_id( 'xtreme_burn_it' ) ?>"><?php  _e( 'Enforce this widget site wide', XF_TEXTDOMAIN ) ?></label>
			<input name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_sidebar' ) ?>" type="hidden" value="" />
			<input name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_id_base' ) ?>" type="hidden" value="" />
			<input name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_widget_number' ) ?>" type="hidden" value="" />
			<input name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_class' ) ?>" type="hidden" value="<?php echo $class; ?>" />
			<br/><small><em><b><?php _e('Remark', XF_TEXTDOMAIN) ?>: </b><?php _e('If you enable this option at your main site as super admin, this widget will show up at any sub site at your multisite install using the same sidebar!', XF_TEXTDOMAIN); ?></em></small>
		</p>
		<p>
			<label style="color: red;" for="<?php echo $widget_obj->get_field_id( 'xtreme_burn_it_context' ) ?>"><?php  _e( 'Widget Display Context:', XF_TEXTDOMAIN ) ?></label>
			<select class="widefat" name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_context' ) ?>" >
				<option value="main"<?php selected('main', $burn_it_context); ?>><?php _e('Use content from Main Site',XF_TEXTDOMAIN); ?></option>
				<option value="current"<?php selected('current', $burn_it_context); ?>><?php _e('Use content from Current Site',XF_TEXTDOMAIN); ?></option>
			</select>
			<small><em><b><?php _e('Remark', XF_TEXTDOMAIN) ?>: </b><?php _e('If content comes from current site, it may produce unexpected results, because identifier of categories, tags and posts may be different.', XF_TEXTDOMAIN); ?></em></small>
			<br/><br/>
			<input id="<?php echo $widget_obj->get_field_id( 'xtreme_burn_it_childonly' ) ?>" name="<?php echo $widget_obj->get_field_name( 'xtreme_burn_it_childonly' ) ?>" type="checkbox" value="1" <?php checked(1, $burn_it_childonly); ?> />
			<label style="color: red;" for="<?php echo $widget_obj->get_field_id( 'xtreme_burn_it_childonly' ) ?>"><?php  _e( 'Display on child sites only', XF_TEXTDOMAIN ) ?></label>
		</p>

		</div>
		<br/>
		<?php
	}
	
	function on_form_ext_save_burnings($instance, $new_instance, $old_instance, $widget_obj) {	
		$registry = get_blog_option(XF_SITE_ID, XF_WIDGET_BURNING_REG, array());
	
		$burn_it = (isset($new_instance['xtreme_burn_it']) && $new_instance['xtreme_burn_it'] == 1) ? 1 : 0;
		$burn_it_context = isset($new_instance['xtreme_burn_it_context']) ? $new_instance['xtreme_burn_it_context'] : 'main';
		$burn_it_childonly = isset($new_instance['xtreme_burn_it_childonly']) && $new_instance['xtreme_burn_it_childonly'] == 1 ? 1 : 0;
		
		$sidebar = isset($new_instance['xtreme_burn_it_sidebar']) ? $new_instance['xtreme_burn_it_sidebar'] : '';
		$id_base = isset($new_instance['xtreme_burn_it_id_base']) ? $new_instance['xtreme_burn_it_id_base'] : '';
		$number = isset($new_instance['xtreme_burn_it_widget_number']) ? $new_instance['xtreme_burn_it_widget_number'] : '';
		$class = isset($new_instance['xtreme_burn_it_class']) ? $new_instance['xtreme_burn_it_class'] : '';
		$reg_key = $id_base.'-'.$number;
		if($burn_it == 1) {
			//register it
			$registry[$reg_key] = $class;
		}else{
			//unregister it
			if(isset($registry[$reg_key])) unset($registry[$reg_key]);
		}
		update_blog_option(XF_SITE_ID, XF_WIDGET_BURNING_REG, $registry);
		$instance['xtreme_burn_it'] = $burn_it;
		$instance['xtreme_burn_it_context'] = $burn_it_context;
		$instance['xtreme_burn_it_childonly'] = $burn_it_childonly;
		return $instance;
	}
		
	function on_form_ext_burnings_force_widgets($sidebar_params) {
		global $wp_widget_factory, $wp_registered_sidebars;
		static $sidebars = array();
		
		$registry = get_blog_option(XF_SITE_ID, XF_WIDGET_BURNING_REG, array());
		$reg_keys = array_keys($registry);
		$current_sidebar = $sidebar_params[0]['id'];
		
		$site_sidebars = get_blog_option(XF_SITE_ID, get_blog_option(XF_SITE_ID, 'stylesheet').'_sidebars_widgets', array());
		if (!isset($site_sidebars[$current_sidebar]) || in_array($current_sidebar, $sidebars) ) return $sidebar_params; //make it short
		$sidebars[] = $current_sidebar;
		
		$site_content = $site_sidebars[$current_sidebar];
		
		if (count($reg_keys) > 0) {
			//full deep copy
			$backup = unserialize(serialize($sidebar_params)); 
			$sidebar_params = $wp_registered_sidebars[$current_sidebar];

			foreach($reg_keys as $sitewidget_id) {
				
				if(in_array($sitewidget_id, $site_content)) {
					
					$class = $registry[$sitewidget_id];
					$ih = new Xtreme_Widget_Instance_Helper($class, $sitewidget_id);
					
					if (!class_exists($class)) continue; //fallback for deactivated widgets!
					
					$w = ($this->is_widget_disabled($class) ? new $class() : $wp_widget_factory->widgets[$class]);
					$classname = 'widget_xtreme_mainsite '.$w->widget_options['classname'];
					
					if(
						isset($ih->instance['auto_tabs_id'])
						&&
						!empty($ih->instance['auto_tabs_id'])
						&&
						$this->is_auto_tabs_enabled()
						&&
						(
							(
								isset($ih->instance['title'])
								&&
								!empty($ih->instance['title'])
							)
							||
							($class == "WP_Widget_Links")
						)
					) {
						$classname .= ' widget_xtreme_grouped widget_group_id_'.strtolower(sanitize_title($ih->instance['auto_tabs_id']));
						//WordPress is semi-intelligent! it doesn't handle the later enqueue of scripts well, even if it could successful resolved at footer!
						//So we do a workaround therefor here.
						//check for safety for jquery first (should not happen because global jquery enqueue by xtreme framework)
						
						global $xtreme_script_manager;
						$xtreme_script_manager->ensure_jquery();
						$xtreme_script_manager->ensure_accessible_tabs();
						$xtreme_script_manager->ensure_widget_groups();
					}
					
					$sb_before = $sidebar_params['before_widget'];
					$sidebar_params['before_widget'] = sprintf($sidebar_params['before_widget'], 'widget_xtreme_mainsite-'.$ih->number, $classname);
					$sidebar_params['widget_id'] = 'xtreme-main-site-'.$ih->number;					
					$w->id = $sidebar_params['widget_id']; //make it work unique too.
										
					if($ih->instance['xtreme_burn_it_context'] != 'current') {
						switch_to_blog(XF_SITE_ID);
					}
								
					if(method_exists($w, 'ensure_widget_scripts')) {
						$w->ensure_widget_scripts( $ih->instance );
					}
					$w->widget($sidebar_params, $ih->instance);
					
					if($ih->instance['xtreme_burn_it_context'] != 'current') {
						restore_current_blog();
					}									
					$sidebar_params['before_widget'] = $sb_before;
				}
			}

			$sidebar_params = $backup;
		}
		
		return $sidebar_params;
	}
	
	function on_form_ext_burnings_extend_frontend_sidebars($value) {
		global $wp_registered_widgets, $wp_registered_sidebars; 
		static $counter = 0;
		
		//fill up missings sidebars but registered (if user never used widgets)
		$sidebars = array_keys($wp_registered_sidebars);
		foreach($sidebars as $sidebar) {
			if(!isset($value[$sidebar]) || count($value[$sidebar]) == 0) {
				//register a dummy widget for empty sidebars first
				$wp_registered_widgets['xtreme-widget-dummy-'.$counter] = array(
					'name' => 'Dummy',
					'params' => array( 0 => $wp_registered_sidebars[$sidebar] ),
					'classname' => 'xtreme-widget-dummy',
					'callback' => '__return_false'
				);
				$value[$sidebar] = array( 0 => 'xtreme-widget-dummy-'.$counter );
				$counter++;
			}			
		}
		return $value;
	}
	
	function on_form_ext_burnings_childblog_info() {
		global $wp_widget_factory;
		$registry = get_blog_option(XF_SITE_ID, XF_WIDGET_BURNING_REG, array());
		$reg_keys = array_keys($registry);
		
		$site_sidebars = get_blog_option(XF_SITE_ID, get_blog_option(XF_SITE_ID, 'stylesheet').'_sidebars_widgets', array());		
		echo '<div style="display:none" id="xtreme-admin-burnings">';	
		foreach($site_sidebars as $current_sidebar => $site_content) {	
			$output  = '<div class="xtreme-admin-burnings-item">'.$current_sidebar.'</div>';
			$output .= '<div class="xtreme-admin-burnings">';
			$output .= '<p><span class="ui-icon ui-icon-locked"></span>'.__('Globally Assigned Widgets', XF_TEXTDOMAIN).'</p>';
			$output .= '<ul>';
			
			$done = false;
			
			foreach($reg_keys as $sitewidget_id) {
				
				if(in_array($sitewidget_id, $site_content)) {
					
					$class = $registry[$sitewidget_id];
					$name = (isset($wp_widget_factory->widgets[$class])) ? $wp_widget_factory->widgets[$class]->name : '';
					if(empty($name)) {
						//sub site may have disabled this widget, lookup hardly for name
						$w = new $class();
						$name = $w->name;
					}
					$output .= '<li>'.$name.'</li>';
					$done = true;
				}
				
			}
			
			$output .= '</ul><small><em>'.__('Global Widgets has been assigned by Multisite Domain Manager and will be displayed always.',XF_TEXTDOMAIN).'</em></small></div>';
			
			if($done) echo $output;
		}
		
		echo '</div>';
	}
	
	function on_filter_widget_output($instance, $widget, $args) {
		global $post, $xtreme_fancybox_manager, $xtreme_script_manager, $ngg;
		$class = get_class($widget);
		
		//check for NextGen Widget Class and activate the FancyBox here, if detected
		if ($class == 'nggWidget' && is_object($ngg) && isset($ngg->options['thumbEffect']) && $ngg->options['thumbEffect'] == 'custom') {
			$support = get_theme_support('xtreme-fancybox');
			$specials = (array)$support[0]['specials'];
			if (isset($specials['nggallery']) && $specials['nggallery']) {
				$xtreme_script_manager->ensure_FancyBox();
			}
		}
		//text widget with fancy box links
		if ($class == 'WP_Widget_Text') {
			if(is_object($xtreme_fancybox_manager) && $xtreme_fancybox_manager->_is_content_affected($instance['text'])) {
				$xtreme_script_manager->ensure_FancyBox();
			}
		}
		//text grid widgets with fancybox links
		if ($class == 'Xtreme_Grid_Text_Widget') {
			foreach($instance['text'] as $text) {
				if(is_object($xtreme_fancybox_manager) && $xtreme_fancybox_manager->_is_content_affected($text)) {
					$xtreme_script_manager->ensure_FancyBox();
				}
			}
		}
		//shortcode support
		if (current_theme_supports('xtreme-textwidget-shortcodes')) {
			if ($class == 'WP_Widget_Text') { $instance['text'] = do_shortcode($instance['text']); } 
			if ($class == 'Xtreme_Grid_Text_Widget') {
				$count = count($instance['text']);
				for ($i=0; $i<$count; $i++) {
					$instance['text'][$i] = do_shortcode($instance['text'][$i]);
				}
			}
		}
		
		
		if($this->is_filtersystem_enabled()){

			/*
			foreach($this->filters as $filter => $name) {
				if(is_callable($filter)) {
					echo($filter.': '.(@call_user_func($filter) ? 'true' : 'false'));
					echo "<br/>";
				}
			}
			echo('is_front_page(): '.(is_front_page() ? 'true' : 'false'));
			echo "<br/>";
			echo('is_singular("post"): '.(is_singular('post') ? 'true' : 'false'));
			echo "<br/>";
			echo('is_singular("page"): '.(is_singular('page') ? 'true' : 'false'));
			echo "<br/>";
			echo('is_singular("attachment"): '.(is_singular('attachment') ? 'true' : 'false'));
			echo "<br/>";
			echo('is_singular("example"): '.(is_singular('example') ? 'true' : 'false'));
			echo "<br/>";
			echo('is_tax("post_format"): '.(is_tax('post_format') ? 'true' : 'false'));
			echo "<br/>";
			*/
		
			if (is_archive() || is_search()) {
				//processing any type of archive
				foreach($this->filter_archives as $filter) {
				
					//check if artificial or real
					if(is_callable($filter) && call_user_func($filter)) {
					
						//1st shot - test it against global settings
						if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
							return false; //finished here, not visible!
						}
						//2nd shot - widgets own setting
						$blocked = false;
						if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
							//blocked by widget
							$blocked = true;
						}
						//3rd shot - widgets own exceptions
						if(isset($instance['xtreme-fex_'.$filter]) && !empty($instance['xtreme-fex_'.$filter])){
							//we have exceptions
							$terms = explode(',', $instance['xtreme-fex_'.$filter]);
							if(call_user_func($filter, $terms)) {
								//matches at least one of the exceptions
								$blocked = !$blocked;
							}
						}
						
						//exist for this archive ?
						if($blocked) return false;
					}
					else{
						if(preg_match("/^x\-cpta\-/", $filter)) {
							$posttype = preg_replace("/^x\-cpta\-/", '', $filter);
							if(is_post_type_archive($posttype)) {
								//1st shot - test it against global settings
								if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
									return false; //finished here, not visible!
								}
								//2nd shot - widgets own setting
								$blocked = false;
								if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
									//blocked by widget
									$blocked = true;
								}
								//exist for this archive ?
								if($blocked) return false;
							}
						}
						elseif(preg_match("/^x\-taxa\-/", $filter)) {
							$taxonomy = preg_replace("/^x\-taxa\-/", '', $filter);
							if (is_tax($taxonomy)) {
								//1st shot - test it against global settings
								if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
									return false; //finished here, not visible!
								}
								//2nd shot - widgets own setting
								$blocked = false;
								if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
									//blocked by widget
									$blocked = true;
								}
								//3rd shot - widgets own exceptions
								if(isset($instance['xtreme-fex_'.$filter]) && !empty($instance['xtreme-fex_'.$filter])){
									//we have exceptions
									$terms = explode(',', $instance['xtreme-fex_'.$filter]);
									if(is_tax($taxonomy, $terms)) {
										//matches at least one of the exceptions
										$blocked = !$blocked;
									}
								}
								//exist for this archive ?
								if($blocked) return false;
							}
						}elseif($filter == 'post-format') {
							if (is_tax('post_format')) {
								//1st shot - test it against global settings
								if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
									return false; //finished here, not visible!
								}
								//2nd shot - widgets own setting
								$blocked = false;
								if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
									//blocked by widget
									$blocked = true;
								}
								//3rd shot - widgets own exceptions
								if(isset($instance['xtreme-fex_'.$filter]) && !empty($instance['xtreme-fex_'.$filter])){
									//we have exceptions
									$data = explode(',', $instance['xtreme-fex_'.$filter]);
									$terms = array(); foreach($data as $d) $terms[] = 'post-format-'.$d;
									if(is_tax('post_format', $terms)) {
										//matches at least one of the exceptions
										$blocked = !$blocked;
									}
								}
								//exist for this archive ?
								if($blocked) return false;
							}
						}
					}
				}
			}
			elseif(is_singular() && !is_front_page()) {
			
				//-------------------------------- BAUSTELLE --------------------------------------
											
				//1st - processing any singular page for total deny
				$filter = 'is_singular';
				//1st shot - test it against global settings
				if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
					return false; //finished here, not visible!
				}
				//2nd shot - widgets own setting
				$blocked = false;
				if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
					//blocked by widget
					$blocked = true;
				}
				//exist for general singulars ?
				if($blocked) return false;					
					
				//3rd - process now complex logic
				foreach($this->filter_post_types as $filter) {
				
					$type = '-';
					$test_id = get_the_ID();
					switch($filter) {
						case 'is_single':
							$type = 'post';
							break;
						case 'is_page':
							$type = 'page';
							break;
						case 'is_attachment':
							$type = 'attachment';
							break;
						case 'is_mobile_device':
							continue; //processing as post step after widget passes
							break;
						default:
							//may be custom post type
							if(preg_match("/^x\-cpt\-/", $filter)) {
								$type = preg_replace("/^x\-cpt\-/", '', $filter);
							}
							break;
					}
					if (is_singular($type)) {
						//1st shot - test it against global settings
						if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
							return false; //finished here, not visible!
						}
						//2nd shot - widgets own setting
						$blocked = false;
						$match_by_fex = false;
						$match_by_fec = false;
						if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
							//blocked by widget
							$blocked = true;
						}
						//3rd shot - widgets own exceptions
						if(isset($instance['xtreme-fex_'.$filter]) && !empty($instance['xtreme-fex_'.$filter])){
							//we have exceptions
							$terms = explode(',', $instance['xtreme-fex_'.$filter]);
							if (in_array((string)$test_id, $terms)) {
								//matches at least one of the exceptions
								$match_by_fex = true;
							}
						}
						
						//4th shot - widgets own conditions
						if(isset($instance['xtreme-fec_'.$filter]) && !empty($instance['xtreme-fec_'.$filter])){
							//we have exceptions
							$terms = explode('|', $instance['xtreme-fec_'.$filter]);
							$func_string ='';
							foreach($terms as $term) {
								list($t, $v) = explode(':',$term);
								if (!empty($v)) {
									$e = explode(',', $v);
									$v=array(); foreach($e as $l) { $v[] = '"'.$l.'"'; };
									$v = "array(".implode(',', $v).")";
								}
								switch($t) {
									case "enter":
										$func_string .= " ( ";
										break;
									case "leave":
										$func_string .= " ) ";
										break;
									case "and":
										$func_string .= " && ";
										break;
									case "or":
										$func_string .= " || ";
										break;
									case "not":
										$func_string .= " !";
										break;
									case 'in_postformat':
										$func_string .= "is_tax(\"$t\",$v) ";
										break;
									case "has_author":
										$a = $post->post_author;
										$func_string .= "in_array(\"$a\" , $v) ";
										break;
									case "is_logged_in":
										$func_string .= "(get_current_user_id() != 0) ";
										break;
									case "has_role":
										$u = wp_get_current_user();
										if(is_object($u)) {
											$r = 'array("'.implode('","', $u->roles).'")';
											$func_string .= "(count(array_intersect($r,$v)) != 0) ";
										}else{
											$func_string .= "false ";
										}
										break;
									case "has_cap":
										$u = wp_get_current_user();
										if(is_object($u)) {
											$uc = array();
											foreach($u->allcaps as $cap => $state) {
												if($state) $uc[] = $cap;
											}
											$r = 'array("'.implode('","', $uc).'")';
											$func_string .= "(count(array_intersect($r,$v)) != 0) ";
										}else{
											$func_string .= "false ";
										}
										break;
									default:
										$func_string .= $t . "($v) ";
										break;
								}
								
							}
							$func_string = "return $func_string;";
							$func = create_function('', $func_string);
							$match_by_fec = $func();
						}
						
						//exist for this type of post ?
						//$match_by_fex (exception match)
						//$match_by_fec (condition match)
						if ($match_by_fex || $match_by_fec)
							$blocked = !$blocked;
							
						if($blocked) return false;						
					}
				}
				
			}
			else {
			
				foreach($this->filter_specials as $filter) {
				
					//check if artificial or real
					if(is_callable($filter) && call_user_func($filter)) {
								
						//1st shot - test it against global settings
						if (isset($this->options['filter'][$filter]) && in_array($class, $this->options['filter'][$filter])) {
							return false; //finished here, not visible!
						}
						//2nd shot - is switched off by singular override
						if (
							(isset($this->options['filter']['is_singular']) && in_array($class, $this->options['filter']['is_singular']))
							||
							(isset($instance['xtreme-filters']) && in_array('is_singular', $instance['xtreme-filters']))
						) {
							return false; //finished here, not visible!
						}					
						//3rd shot - widgets own setting
						$blocked = false;
						if(isset($instance['xtreme-filters']) && in_array($filter, $instance['xtreme-filters'])) {
							//blocked by widget
							$blocked = true;
						}
						//exist for this archive ?
						if($blocked) return false;
					
					}
				}
			}

		}
		
		//special for is_search() false but search only
		if(!is_search() && is_archive() && isset($instance['xtreme-filters']) && !in_array('is_search', $instance['xtreme-filters'])) {
			//blocked if all is blocked except is_search and is_archive
			if(count($this->filter_archives) == count($instance['xtreme-filters']) + 2)
				return false;
		}
		
		//check for child only
		if(
			isset($instance['xtreme_burn_it']) 
			&& 
			$instance['xtreme_burn_it'] == 1 
			&& 
			isset($instance['xtreme_burn_it_childonly']) 
			&& 
			$instance['xtreme_burn_it_childonly'] == 1 
			&& 
			XF_SITE_ID == XF_BLOG_ID
		) {
			return false;
		}
		
		if(method_exists($widget, 'ensure_widget_scripts')) {
			$widget->ensure_widget_scripts($instance);
		}
				
		return $instance;
	}
	
	// inplace widget shortcode support	
	function on_xcontentwidget_shortcode_grouping() {
		$this->shortcode_group += 1;
	}
	
	function on_xcontentwidget_shortcode($attr) {
		list($post_id, $widget_id) = explode('.', $attr['id']);
		
		if(empty($post_id) || empty($widget_id)) return '';
		
		$meta = get_post_meta($post_id, "_xcontentwidgets", true);
		$val = unserialize(base64_decode($meta));

		if(empty($val)) return '';
		
		$class = $val['widgets'][$widget_id]['class'];
		$instance = $val['widgets'][$widget_id]['data'];
	
		if(empty($class)) return '';
	
		$w = new $class();
		$w->_set("x".$widget_id);
		
		if(method_exists($w, 'ensure_widget_scripts')) {
			$w->ensure_widget_scripts($instance);
		}
		
		$align = ' align'.(isset($attr['align']) ? $attr['align'] : 'none');
		$styles = '';
		if(isset($attr['width']) && $attr['width'] != 'auto') {
			$styles = 'width:'.$attr['width'].';';
			if (isset($attr['sidemargins']) && $attr['sidemargins'] == 'off') {
				switch($align) {
					case ' alignleft':
						$styles .= 'margin-right:0;';
						break;
					case ' alignright':
						$styles .= 'margin-left:0;';
						break;
				}
			}
		}
		if (!empty($styles)) {
			$styles = ' style="'.$styles.'"';
		}
		
		$autotabs = '';
		$tab_id = '';
		if ($this->is_auto_tabs_enabled()) {
			if(
				isset($instance['auto_tabs_id'])
				&&
				!empty($instance['auto_tabs_id'])
				&&
				(
					(
						isset($instance['title'])
						&&
						!empty($instance['title'])
					)
					||
					($class == "WP_Widget_Links")
				)
			) {
				if ( ! empty($instance['auto_tabs_id'] )  &&  ! empty($instance['title'] ) ) {
					$tab_id = ' id="'. strtolower( sanitize_title( $instance['title'] ) ).'"';
				}

				$autotabs = ' widget_xtreme_grouped widget_group_id_'.$this->shortcode_group.'_'.strtolower(sanitize_title($instance['auto_tabs_id'])).' ';				
				//WordPress is semi-intelligent! it doesn't handle the later enqueue of scripts well, even if it could successful resolved at footer!
				//So we do a workaround therefor here.
				//check for safety for jquery first (should not happen because global jquery enqueue by xtreme framework)
				global $xtreme_script_manager;
				$xtreme_script_manager->ensure_jquery();
				$xtreme_script_manager->ensure_accessible_tabs();
				$xtreme_script_manager->ensure_widget_groups();
			}			
		}
		
		
		$before_widget = sprintf('<div id="%s" class="widget xcontentwidget %s"%s>', $w->id, $w->widget_options['classname'].$autotabs.$align, $styles );
		$args = array(
			'widget_id' => $w->id,
			'before_widget' => $before_widget,
			'after_widget' => "</div>",
			'before_title' => '<h2 class="widget-title" ' . $tab_id . '>',
			'after_title' => '</h2>'
		);
		
		$instance = apply_filters('widget_display_callback', $instance, $w, $args);
		if ( false !== $instance ) {
			ob_start();
			$w->widget($args, $instance);
			return ob_get_clean();
		}
			
		return '';
	}
	
}

$xtreme_widget_manager = new Xtreme_Widget_Manager();
