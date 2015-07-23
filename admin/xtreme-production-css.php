<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once XF_ADMIN_DIR . '/writer.php';

class Xtreme_Production_CSS {

	protected $content;

	function __construct() {
		
		$this->options = get_option(XF_OPTIONS);
		//no longer using direct XF_BLOG_ID
		$this->dir = XF_ABS_OUTPUT_DIR_THEME_BASED;
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.min' : '';
		$this->stylesheets[] = '/yaml/core/base' . $suffix . '.css';
		//no longer using direct XF_BLOG_ID
		$this->stylesheets[] = XF_REL_OUTPUT_DIR_THEME_BASED. '/xtreme_basemod.css';

		if ( (int) $this->options['xc_navigation']['primary_position']['value'] !== 0 ) {
			$primary = '/css/navigation/' . esc_attr($this->options['xc_navigation']['primary_stylesheet']['value'].'.css');
		}
		if ( (int) $this->options['xc_navigation']['secondary_position']['value'] !== 0 ) {
			$secondary = '/css/navigation/' . esc_attr($this->options['xc_navigation']['secondary_stylesheet']['value'].'.css');
		}
		if ( isset( $primary ) && !isset( $secondary ) ) {
			$this->stylesheets[] = $primary;
		}
		if ( isset( $secondary ) && !isset( $primary ) ) {
			$this->stylesheets[] = $secondary;
		}
		if ( isset ($primary) && isset($secondary)&& $primary === $secondary ) {
			$this->stylesheets[] = $primary;
		}
		if ( isset ($primary) && isset($secondary)&& $primary !== $secondary ) {
			$this->stylesheets[] = $primary;
			$this->stylesheets[] = $secondary;
		}
		if ( true === $this->options['xc_general']['nav_vlist']['value']) {
			$this->stylesheets[] = '/yaml/navigation/vlist.css';
		}
		$this->stylesheets[] = '/css/screen/layout.css';
		$this->stylesheets = apply_filters('xtreme_after_layout_css', $this->stylesheets, 10, 2);
		$this->stylesheets[] = '/css/forms/gray-theme.css';
		$this->stylesheets[] = '/css/screen/content.css';
		$this->stylesheets = apply_filters('xtreme_after_content_css', $this->stylesheets, 10, 2);
		$this->stylesheets[] = '/css/screen/theme.css';
		$this->stylesheets = apply_filters('after_theme_css', $this->stylesheets, 10, 2);
		if (XF_LOW_BARRIER_CSS_EXISTS) {
			$this->stylesheets[] = '/css/screen/low-barrier.css';
		}
		
		if (xtreme_is_layout_2()) {
			$this->stylesheets[] = '/css/print/print_100.css';
		} else {
			$this->stylesheets[] = '/css/print/print_003.css';
		}

		if( xtreme_is_responsive() ){
			// parent theme css-file
			$this->stylesheets[] = '/css/screen/responsive.css';
			// adding child theme css-file if exists
			$file = '/css/screen/theme.responsive.css';
			if( file_exists( XF_CHILD_THEME_DIR . $file ) ){
				$this->stylesheets[] = $file;;
			}
		}

		
	}

	function _readable_css() {

		$this->content = '';
		foreach ( $this->stylesheets as $file ) {
			if ( is_child_theme() ) {
				if ( file_exists( XF_CHILD_THEME_DIR . $file ) ) {
					$theme = get_stylesheet();
					$file = "../../../../" . $theme. $file;

				} elseif ( file_exists( XF_THEME_DIR . $file ) ) {
					$file = "../../.." . $file;
				}
			}
			else {
				if ( file_exists( XF_THEME_DIR . $file ) ) {
					$file = "../../.." . $file;
				}
			}
			$this->content .= "@import url(" . $file . ");\n";

		}
		return $this->content;
	}

	function _compress_css() {
		$this->content = '';
		$lines = array();
		foreach ($this->stylesheets as $key => $file) {

			if ( is_child_theme() ) {
				if ( file_exists( XF_CHILD_THEME_DIR . $file ) ) {
					$theme = get_stylesheet();
					$childcss = file_get_contents(XF_CHILD_THEME_DIR . $file);
					$lines[] = str_replace('../../', '../../../../' . $theme . '/' , $childcss);
				} elseif ( file_exists( XF_THEME_DIR . $file ) ) {
					$f = file_get_contents( XF_THEME_DIR . $file );
					$lines[] = str_replace('../../', '../../../', $f);
				}
			} else {
				if ( file_exists( XF_THEME_DIR . $file ) ) {
					$f = file_get_contents( XF_THEME_DIR . $file );
					$lines[] = str_replace('../../', '../../../', $f);
				}
			}

		}

		$charset = '/^\A\s*\@charset\s*[\'\"]+[UTFutf]+-8[\'\"]+\s*;/';
		$search = array(': ', ' {', '} ' ,'{ ', ' }',' ;', '; ');
		$replace = array(':','{','}', '{', '}', ';', ';');
		foreach ($lines as $key => $line) {
			// remove comments
			$line = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $line);
			// remove tabs, newlines, etc.
			$line = str_replace(array("\r\n", "\r", "\n", "\t"), "", $line);
			//remove multiple spaces
			$line = preg_replace('/\s\s+/', ' ', $line);
			// remove additional whitespace
			$line = str_replace($search, $replace, $line);
			$line = trim($line);
			$line = preg_replace($charset,'', $line);
			$this->content .= $line;
		}
		return $this->content;
	}

	function write() {
		$writer = new xtreme_file_writer();
		$writer->write_file('production-min.css', $this->_compress_css(), $this->dir);
		$writer->write_file('production.css', $this->_readable_css(), $this->dir);
	}
}
