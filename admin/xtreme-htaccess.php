<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once XF_ADMIN_DIR . '/writer.php';

class Xtreme_HTAccess {

	function  __construct() {
		global $is_apache;
		$options = get_option(XF_OPTIONS);
		$this->browser_caching = isset($options['xc_performance']['browser_caching']) ? (bool)$options['xc_performance']['browser_caching']['value'] : false;
		$this->dir = @ file_exists( ABSPATH . '.htaccess' ) ? ABSPATH : dirname( ABSPATH ). '/';
		if( $this->browser_caching && ( !$is_apache || ! @ file_exists( $this->dir . '.htaccess' ) ) ) {
			$this->browser_caching = false;
		}
	}
	
	function strip_marker_content( $filename, $marker ) {
		$lines = array();
		if ( @ file_exists( $filename )) {
			$markerdata = explode( "\n", implode( '', file( $filename ) ) );
			if ( $markerdata ) {
				$state = true;
				foreach ( $markerdata as $n => $markerline ) {
					if (strpos($markerline, '# BEGIN ' . $marker) !== false)
						$state = false;
					if ( $state ) {
						if ( $n + 1 < count( $markerdata ) )
							$lines[] = "{$markerline}\n";
						else
							$lines[] = "{$markerline}";
					}
					if (strpos($markerline, '# END ' . $marker) !== false) {
						$state = true;
					}
				}
			}
		}
		
		if (count($lines)) {
			 $t = trim($lines[0]);
			 while(empty($t)) {
				array_shift($lines);
				if (count($lines) == 0) {
					break;
				}
				$t = trim($lines[0]);
			 }
		}
		return implode('', $lines);
	}
		
	function htaccess_content() {
		$lead = 
<<<EOD
# BEGIN xtreme-one
<IfModule mod_expires.c>
	<FilesMatch "\.(jpg|jpeg|gif|png|mp3|flv|mov|avi|3pg|html|htm|swf|js|ico)$">
		ExpiresActive on
		ExpiresDefault "access plus 7 day"
		ExpiresByType image/ico "access plus 1 years"
		ExpiresByType image/icon "access plus 1 years"
		ExpiresByType image/x-icon "access plus 1 years"
		ExpiresByType image/vnd.microsoft.icon "access plus 1 years"
		Header unset ETag 
		FileETag none
	</FilesMatch>
</IfModule>
# END xtreme-one


EOD;
		$result = $this->strip_marker_content($this->dir.'.htaccess', 'xtreme-one');
		if($this->browser_caching === true) {
			$result = $lead.$result;
		}
		return $result;
	}
	

	function write(){
		if(!XF_IS_MAIN_BLOG) return;
		if( @ file_exists($this->dir.'.htaccess')) {
			$writer = new xtreme_file_writer();
			$writer->write_file('.htaccess', $this->htaccess_content(), $this->dir);
		}
	}
}