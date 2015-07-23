<?php

if (!function_exists ('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
} 

require_once( ABSPATH . '/wp-admin/includes/file.php' );

class xtreme_file_writer {

	function check_filesystem() {
		$result = true;
		ob_start();
		if ( false === ($credentials = request_filesystem_credentials('')) ) {
			$data = ob_get_contents();
			ob_end_clean();
			if( ! empty($data) ){
				$result = false;
			}
		} else {
			ob_end_clean();
		}
		return $result;
	}
	
	function mkdir_recursive($pathname, $mode){
		global $wp_filesystem;
		$wp_filesystem->is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
		return $wp_filesystem->is_dir($pathname) || $wp_filesystem->mkdir($pathname, $mode);
	}

	function write_file($filename, $content, $dir) {
		global $wp_filesystem;

		ob_start();
		if ( false === ($credentials = request_filesystem_credentials('')) ) {
			$data = ob_get_contents();
			ob_end_clean();
			if( !empty($data) ){
				trigger_error(__('Credentials required.', XF_TEXTDOMAIN), E_USER_ERROR);
			}
		}
		ob_end_clean();

		if ( ! WP_Filesystem($credentials) ) {
			trigger_error(__('Credentials are wrong or mistyped.', XF_TEXTDOMAIN), E_USER_ERROR);
		}

		if ( ! is_object($wp_filesystem) ) {
			trigger_error(__('Could not access filesystem.', XF_TEXTDOMAIN), E_USER_ERROR);
		}

		if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() ) {
			trigger_error($wp_filesystem->errors->get_error_message(), E_USER_ERROR);
		}

		//work arround because of bad implementation at WP core
		if($wp_filesystem->method == 'direct'){
			$d = $dir;
		} else {
			//FTP root may not be the root of this install, so prepend it qualified
			//no longer using direct XF_BLOG_ID
			//ATTENTION: fix WP core issue of doubled slashes
			$d = str_replace('//', '/', $wp_filesystem->wp_themes_dir() . XF_FTP_OUTPUT_DIR);
		}

		if (!$wp_filesystem->is_dir($d)) {
			$d = str_replace("\\", '/', $d);
			if ($this->mkdir_recursive($d, FS_CHMOD_DIR) === false) {
				trigger_error(sprintf(__('Directory %s could not be created.', XF_TEXTDOMAIN), $d), E_USER_ERROR);
			}
		}

		$done = $wp_filesystem->put_contents(trailingslashit($d).$filename, $content, ($wp_filesystem->method == 'direct' ? FS_CHMOD_FILE : ''));
		if (!$done) {
			trigger_error(sprintf(__('File %s could not be written.', XF_TEXTDOMAIN), $d.'/'.$filename), E_USER_ERROR);
		}
	}
}