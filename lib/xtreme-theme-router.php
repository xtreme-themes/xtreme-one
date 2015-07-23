<?php

require_once XF_ADMIN_DIR . '/includes/xtreme-xmlprocessor.php';
 
function xtreme_merge_options($n, $o) {
	foreach($o as $key => $value) {
		if(array_key_exists($key, $n) && is_array($value)) {
			$n[$key] = xtreme_merge_options($n[$key], $o[$key]);
		}else{
			$n[$key] = $value;
		}
	}
	return $n;
} 

function xtreme_validate_framework() {
	//check if we activated or previewed the first time and attached the missing options
	$needed_options = array(XF_OPTIONS, XF_LAYOUTS, XF_TEMPLATES, XF_WIDGET_PERMISSIONS);
	$install_options = array();
	foreach($needed_options as $option) {
		if (get_option($option) === false)
			$install_options[] = $option;
	}
	if (count($install_options) > 0 || version_compare( get_option( XF_VERSION_FIELD ), XF_VERSION, '<' )) {		
		$file_xml = XF_ADMIN_DIR . '/install/xtreme-one-install-config.xml';
		$defaults = false;
		if(file_exists($file_xml)) {
			$processor = new xtreme_xml_processor();
			$xml = file_get_contents($file_xml);
			$xml = str_replace('[install]', XF_ACTIVE_STYLESHEET, $xml);
			$defaults = $processor->_convert_to_array(XF_VERSION, $xml);
		}
		if ($defaults !== false && is_array($defaults)) {
			//perform install first
			foreach($install_options as $option) {
				if (isset($defaults[$option])) {
					update_option($option, $defaults[$option]);
				}
			}			
			//perform upgrade secondly
			if( version_compare( get_option( XF_VERSION_FIELD ), XF_VERSION, '<' ) ){
				foreach($needed_options as $option) {
					if (!in_array($option, $install_options)) {
						$o = get_option($option);
						$n = $defaults[$option];
						if(is_array($n) || is_array($o)){
							if(!is_array($n)) {
								$n = xtreme_merge_options(array(), $o);
							}elseif(!is_array($o)) {
								if (!empty($o)) {
									$n = xtreme_merge_options($n, (array)$o);
								}
							}else {
								$n = xtreme_merge_options($n, $o);
							}
						}
						else
							$n = $o;
						update_option($option, $n);
					}
				}

				update_option( XF_VERSION_FIELD, XF_VERSION);
				if ( is_admin() ) {
    				    global $xtreme_backend;
    				    $xtreme_backend->generate_theme();
    				}
			}
		}
		else {
			$errors = get_option(XF_UPDATE_ERRORS, array());
			$error = __('There was an error during install/upgrade of <b>Xtreme One</b> Theme Framework. The default value definition file is missing or damaged.', XF_TEXTDOMAIN);
			
			if ( ! in_array($error, $errors) )
				$errors[] = $error;
			
			update_option(XF_UPDATE_ERRORS, $errors);
		}
	}
}

function xtreme_reset_framework() {
	delete_option(XF_VERSION_FIELD);
	delete_option(XF_UPDATE_ERRORS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_OPTIONS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_LAYOUTS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_TEMPLATES);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_WIDGET_PERMISSIONS);
	//regenerate the options adhoc now
	xtreme_validate_framework();
}

function xtreme_cleanup_framework() {
	//cleanup child theme based options only
	delete_option(XF_VERSION_FIELD);
	delete_option(XF_UPDATE_ERRORS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_OPTIONS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_LAYOUTS);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_TEMPLATES);
	delete_option(XF_ACTIVE_STYLESHEET.'_'.XF_WIDGET_PERMISSIONS);
	delete_option(XF_ACTIVE_STYLESHEET.'_sidebars_widgets');
	update_option('template', WP_DEFAULT_THEME);
	update_option('stylesheet', WP_DEFAULT_THEME);
	delete_option('current_theme');
}

function xtreme_route_get_option($class, $r) {
	$name = XF_ACTIVE_STYLESHEET.'_'.$class;
	$routed = get_option($name);
	return ($routed !== false ? $routed : $r);
}

function xtreme_route_update_option($class, $n, $o) {
	$name = XF_ACTIVE_STYLESHEET.'_'.$class;
	if(get_option($name) !== false)
		update_option($name, $n);
	else
		add_option($name, $n);
	return $o;
}

function xtreme_re_route_sidebars() {
	$options = array('sidebars_widgets', XF_OPTIONS, XF_LAYOUTS, XF_TEMPLATES, XF_WIDGET_PERMISSIONS);
	$marker = XF_ACTIVE_STYLESHEET . '_';
	foreach($options as $option) {
		$key = $marker . $option;
		$o = get_option($option);
		$n = get_option($key);
		if($n === false && $o !== false) {
			//perform a initial copy first time for backward compatibility
			//and cleanup old values from 1.1.1 version
			add_option($key, $o); 
			if($option != 'sidebars_widgets')
				delete_option($option);
		}
		add_filter('pre_option_'.$option, create_function('$r', 'return xtreme_route_get_option("'.$option.'", $r);'));
		add_filter('pre_update_option_'.$option, create_function('$n,$o', 'return xtreme_route_update_option("'.$option.'", $n, $o);' ), 0, 2);
	}
	xtreme_validate_framework();
	do_action('xtreme_setup_theme');
}
add_action('after_setup_theme', 'xtreme_re_route_sidebars');
