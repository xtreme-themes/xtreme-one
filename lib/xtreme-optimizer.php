<?php

define('XTOPT_SCRIPT_COMPRESSOR_URI_WP', get_option('siteurl').'/wp-admin/load-scripts.php');
define('XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_WP', XF_THEME_URI .'/js/load-wp.php');
define('XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_PLUGINS', XF_THEME_URI .'/js/load.php');
define('XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_CSS', XF_THEME_URI .'/css/load.php');
define('XTOPT_SCRIPT_COMPRESSOR_SITE_URI', get_option('siteurl') );
define('XTOPT_HTML5_IE_SCRIPT', XF_THEME_URI .'/js/ie-html5.js');
define('XTOPT_MARKER_CSS_HEAD', '<xtreme_css>');
define('XTOPT_MARKER_JS_BODY', '<xtreme_js>');
define('XTOPT_MARKER_JS_IE_HTML5', '<xtreme_html5_ie>');
define('XTOPT_MARKER_PERF_STATS', '<xtreme_perf_stats>');
/*
define('XTOPT_COMPRESSION_IS_SERVER_BASED', false);
define('XTOPT_COMPRESSION_HTML_NAME', '');
define('XTOPT_COMPRESSION_HTML_MODE', 0);
define('XTOPT_COMPRESSION_JS_NAME', '');
define('XTOPT_COMPRESSION_JS_MODE', '');
define('XTOPT_COMPRESSION_JS_INLINE_SIZE', 0);
define('XTOPT_COMPRESSION_JS_REPLACE_WP', false);
define('XTOPT_COMPRESSION_JS_MOVE_SCRIPTS', false);
define('XTOPT_COMPRESSION_JS_STRIP_IE_CONDITIONS', 99999);
define('XTOPT_COMPRESSION_WHITESPACE', false);
define('XTOPT_TIME_START_WORDPRESS', 0); 			//from server vars
define('XTOPT_TIME_START_RENDERING', 0); 			//from init hook
define('XTOPT_TIME_START_PROCESSING', 0);			//from begin ob_start callback
define('XTOPT_TIME_START_SENDING', 0);				//from end of ob_start callback
define('XTOPT_SIZE_ORIGINAL', 0);					//from begin ob_start callback
define('XTOPT_NUM_REORDERED_SCRIPTS', 0);
define('XTOPT_NUM_CONDITIONALS_CONTAINED', 0);
define('XTOPT_NUM_CONDITIONALS_REMOVED', 0);
define('XTOPT_NUM_NUM_SCRIPTS_INLINED', 0);
define('XTOPT_NUM_SQL_QUERIES', 0);					//from shutdown hook
define('XTOPT_RENDER_PERF_STATS', false);
*/

function xtopt_capture_current_header_vars() {
	$vars = array();
	$headers = headers_list();
	foreach($headers as $header) {
		list($field, $params) = explode(':', $header);
		$params = explode(';', $params);
		$params = array_map('trim', $params);
		$vars[$field] = $params;
	}
	return $vars;
}

function xtopt_get_current_content_type(&$vars) {
	if (isset($vars['Content-Type']))
		return $vars['Content-Type'][0];
	return 'text/html';
}


function xtopt_get_statistics() {
	return "\n<!-- Xtreme One - Framework Statistic\n\n"
	       .'Framework Version Number.......: '.XF_VERSION."\n"
	       .'Current Child Theme Name.......: '.XF_CURRENT_THEME_NAME."\n"
	       .'Original Content Output Size...: '.XTOPT_SIZE_ORIGINAL." Bytes\n"
	       .'Server Based Compression.......: '.(XTOPT_COMPRESSION_IS_SERVER_BASED ? "yes, modes adjusted!\n" : "no\n")
	       .'HTML Compression Mode used.....: '.XTOPT_COMPRESSION_HTML_NAME."\n"
	       .'HTML Compression Level.........: '.XTOPT_COMPRESSION_LEVEL_HTML."\n"
	       .'Remove HTML Markup Whitespaces.: '.(XTOPT_COMPRESSION_WHITESPACE ? "yes\n" : "no\n")
	       .'CSS Compression Mode used......: '.XTOPT_COMPRESSION_CSS_NAME."\n"
	       .'CSS Compression Level..........: '.XTOPT_COMPRESSION_LEVEL_CSS."\n"
	       .'Compress Plugin based CSS......: '.(XTOPT_COMPRESSION_CSS_PLUGINS ? "yes\n" : "no\n")
	       .'Script Compression Mode used...: '.XTOPT_COMPRESSION_JS_NAME."\n"
	       .'Script Compression Level.......: '.XTOPT_COMPRESSION_LEVEL_JS."\n"
	       .'Compress Plugin based Scripts..: '.(XTOPT_COMPRESSION_JS_PLUGINS ? "yes\n" : "no\n")
	       .'Move Scripts to End of Body Tag: '.(XTOPT_COMPRESSION_JS_MOVE_SCRIPTS ? "yes\n" : "no\n")
	       .'Number of re-ordered Scripts...: '.(XTOPT_COMPRESSION_JS_MOVE_SCRIPTS ? XTOPT_NUM_REORDERED_SCRIPTS : 0)."\n"
	       .'Small Script inline limit (<=).: '.(XTOPT_COMPRESSION_JS_INLINE_SIZE == 0 ? 'off' : XTOPT_COMPRESSION_JS_INLINE_SIZE.' Bytes')."\n"
	       .'Small Scripts auto-inlined.....: '.XTOPT_NUM_NUM_SCRIPTS_INLINED."\n"
	       .'Replace WordPress JS Compressor: '.(XTOPT_COMPRESSION_JS_REPLACE_WP ? "yes\n" : "no\n")
	       .'MS Internet Explorer Version...: '.(XF_IS_IE ? XF_IE_MAJOR.'.'.XF_IE_MINOR : 'no IE, other Browser')."\n"
	       .'IE Conditional Scripts found...: '.XTOPT_NUM_CONDITIONALS_CONTAINED."\n"
	       .'IE Conditional Scripts removed.: '.XTOPT_NUM_CONDITIONALS_REMOVED."\n"
	       .'WordPress Initialization Time..: '.(XTOPT_TIME_START_WORDPRESS != -1.0 ? round(XTOPT_TIME_START_RENDERING - XTOPT_TIME_START_WORDPRESS, 3)." seconds\n" : "-n.a.-\n")
	       .'HTML Content Generation Time...: '.round(XTOPT_TIME_START_PROCESSING - XTOPT_TIME_START_RENDERING, 3)." seconds\n"
	       .'HTML Content Optimization Time.: '.round(XTOPT_TIME_START_SENDING - XTOPT_TIME_START_PROCESSING, 3)." seconds\n"
	       .'Total Time (except compression): '.(XTOPT_TIME_START_WORDPRESS != -1.0 ? round(XTOPT_TIME_START_SENDING - XTOPT_TIME_START_WORDPRESS, 3)." seconds\n" : "-n.a.-\n")
	       .'Number of SQL queries total....: '.XTOPT_NUM_SQL_QUERIES."\n"
	       ."\n-->\n";
}

function xtopt_perform_unify_script_tags($content) {
	return preg_replace("/(<script[^>]*)(\/\s*>)/i", '$1></script>', $content);
}

function xtopt_perform_inline_small_scripts($content, &$scripts, &$conditionals) {
	if(XTOPT_COMPRESSION_JS_INLINE_SIZE == 0) {
		define('XTOPT_NUM_NUM_SCRIPTS_INLINED', 0);
		return $content;
	}

	$num_inlined_scripts = 0;
	for($i=0; $i<XTOPT_NUM_REORDERED_SCRIPTS; $i++) {
		if (empty($scripts[1][$i])) {
			//potential external script
			if(preg_match("/src=[\"']([^\"^']*\.js)(\?|[\"']|\s)/", $scripts[0][$i], $urls)) {
				//check if local one
				if (stripos($urls[1], XTOPT_SCRIPT_COMPRESSOR_SITE_URI) === 0) {
					$file = str_replace(XTOPT_SCRIPT_COMPRESSOR_SITE_URI.'/', ABSPATH, $urls[1]);
					if (file_exists($file) && filesize($file) < XTOPT_COMPRESSION_JS_INLINE_SIZE) {
						$scripts[0][$i] = "<script type=\"text/javascript\">\n//<![CDATA[\n//xtreme-inline: '".str_replace(ABSPATH,'',$file)."'\n".file_get_contents($file)."\n//]]>\n</script>";
						$num_inlined_scripts += 1;
					}
				}
			}
		}
	}
	define('XTOPT_NUM_NUM_SCRIPTS_INLINED', $num_inlined_scripts);
	return $content;
}

function xtopt_perform_compress_plugin_scripts($content, &$scripts) {
	if(XTOPT_COMPRESSION_JS_PLUGINS && XTOPT_COMPRESSION_JS_MODE) {
		for($i=0; $i<XTOPT_NUM_REORDERED_SCRIPTS; $i++) {
			if (empty($scripts[1][$i])) {
				//potential external script
				if(preg_match("/src=[\"']([^\"^']*\.js)(\?[^\"^']*[\"']|[\"'])/", $scripts[0][$i], $urls)) {
					//check if local one
					if (stripos($urls[1], WP_PLUGIN_URL) === 0) {
						$file = str_replace(WP_PLUGIN_URL, WP_PLUGIN_DIR, $urls[1]);
						if (file_exists($file)) {
							$compressor = XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_PLUGINS.'?c='.XTOPT_COMPRESSION_JS_MODE.'&amp;l='.XTOPT_COMPRESSION_LEVEL_JS.'&amp;plugin='.str_replace(WP_PLUGIN_DIR,'',$file).'&amp;ver='.md5(filemtime($file));
							$scripts[0][$i] = str_replace($urls[0],'src="'.$compressor.'"', $scripts[0][$i]);
						}
					}
				}
			}
		}
	}
	return $content;
}

function xtopt_perform_replace_scripts($content, &$scripts, &$conditionals) {
	//1st - replace all scripts now
	for($i=0; $i<XTOPT_NUM_REORDERED_SCRIPTS; $i++) {
		$content = str_replace($scripts[0][$i], "<$i>", $content);
	}
	//2nd detect conditionals
	if (preg_match("/<!\-\-\[if[^\]]*\]>/i", $content)) {

		$num_contained_conditionals = 0;
		$num_removed_conditionals = 0;

		if(preg_match_all("/<!\-\-\[(if[^\]]*)\]>\s*<(\d+)>\s*<!\[endif\]\-\->/i", $content, $conds)) {
			//wrap scripts back to conditions if found
			$num_contained_conditionals = count($conds[0]);
			define('XTOPT_NUM_CONDITIONALS_CONTAINED', $num_contained_conditionals);
			for($i=0; $i<$num_contained_conditionals; $i++) {
				if(XF_IS_IE && (XF_IE_MAJOR < XTOPT_COMPRESSION_JS_STRIP_IE_CONDITIONS)) {
					$conditionals[(int)$conds[2][$i]] = $conds[1][$i];
				}else{
					$scripts[0][(int)$conds[2][$i]] = ''; //entirely removed, only IE required
					$scripts[1][(int)$conds[2][$i]] = ''; //entirely removed, only IE required
					$num_removed_conditionals += 1;
				}
			}
		}
		define('XTOPT_NUM_CONDITIONALS_REMOVED', $num_removed_conditionals);
		if($num_contained_conditionals != $num_removed_conditionals) {
			//unnormal cleanup, keep it as is for ugly IE !
			//conditional scripts at footer stops IE from executing following normal scripts!
			$count = count($scripts[0]);
			for($i=0; $i<$count; $i++) {
				$content = str_replace("<$i>", $scripts[0][$i], $content);
			}
			$scripts[0] = array_pad(array(), XTOPT_NUM_REORDERED_SCRIPTS, ''); //nothing to paste later one
			$scripts[1] = array_pad(array(), XTOPT_NUM_REORDERED_SCRIPTS, ''); //nothing to paste later one
		}
		else {
			//normal cleanup of conditionals
			$content = preg_replace("/<!\-\-\[if[^\]]*\]>\s*<\d+>\s*<!\[endif\]\-\->/i", '', $content);
		}

	}else{
		define('XTOPT_NUM_CONDITIONALS_CONTAINED', 0);
		define('XTOPT_NUM_CONDITIONALS_REMOVED', 0);
	}
	return $content;
}

function xtopt_perform_cleanup_scripts($content, &$scripts, &$conditionals) {
	$injection = '';
	if(XTOPT_COMPRESSION_JS_MOVE_SCRIPTS) {
		$content = preg_replace("/<\d+>/i", '', $content);
		$injection = implode("\n",$scripts[0]);
	}else{
		for($i=0; $i<XTOPT_NUM_REORDERED_SCRIPTS; $i++) {
			$content = str_replace("<$i>", $scripts[0][$i], $content);
		}
	}
	return str_replace(XTOPT_MARKER_JS_BODY, $injection, $content);
}

function xtopt_perform_strip_whitespaces($content) {
	if(XTOPT_COMPRESSION_WHITESPACE) {
		$content = preg_replace( "/<\!\-\-[^>^<]*\-\->/", '', $content);
		$content = preg_replace( "/(>)\s+(<)/", "$1$2", $content);
	}
	return $content;
}

function xtopt_perform_html5_ie_compat($content) {
	if(XF_IS_IE)
		return str_replace(XTOPT_MARKER_JS_IE_HTML5, '<script type="text/javascript" src="'.XTOPT_HTML5_IE_SCRIPT.'"></script>', $content);
	return $content;
}

function xtopt_perform_replace_wp_compressor($content) {
	if(!XTOPT_COMPRESSION_JS_REPLACE_WP) return $content;
	return str_replace(XTOPT_SCRIPT_COMPRESSOR_URI_WP.'?', XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_WP.'?l='.XTOPT_COMPRESSION_LEVEL_JS.'&amp;', $content);
}

function xtopt_perform_compress_css($content) {
	if(XTOPT_COMPRESSION_CSS_MODE > 0) {
		if (preg_match_all("/<link[^>]*\/>/i", $content, $styles)) {
			for($i=0; $i<count($styles[0]); $i++) {
				if (preg_match("/rel=(\"|')stylesheet(\"|')/i", $styles[0][$i])){
					if(preg_match("#href=(\"|')".XTOPT_SCRIPT_COMPRESSOR_SITE_URI."/([^\"^']*)(\"|')#", $styles[0][$i], $urls)) {
						list($url, $params) = explode('?', $urls[2]);
						$file = ABSPATH.$url;
						if (preg_match('/\.css$/',$file) && file_exists($file)) {
							if(XTOPT_COMPRESSION_CSS_PLUGINS == false && stripos($file , 'xtreme-one') === false) continue;
							$url = XTOPT_SCRIPT_COMPRESSOR_URI_XTREME_CSS.'?c='.XTOPT_COMPRESSION_CSS_MODE.'&amp;l='.XTOPT_COMPRESSION_LEVEL_CSS.'&amp;load='.$url.'&amp;ver='.md5(filemtime($file));
							$new = preg_replace("/href=(\"|')[^\"^']+(\"|')/", "href='$url'", $styles[0][$i]);
							$content = str_replace($styles[0][$i], $new, $content);
						}
					}
				}
			}
		}
	}
	return $content;
}

function xtopt_perform_statistics($content) {
	if(!XTOPT_RENDER_PERF_STATS) return $content;
	return str_replace(XTOPT_MARKER_PERF_STATS, xtopt_get_statistics(), $content);
}

function xtopt_get_html_class_detection() {
	$result =
		<<<EOD
		<script type="text/javascript">
//enable css rules for active javascript
document.documentElement.className = document.documentElement.className.length ? document.documentElement.className + " js" : "js";
</script>

EOD;
	return $result;
}

function xtopt_perform_compression($content, $content_type) {
	$content = str_replace('<html_js_class>', xtopt_get_html_class_detection() , $content);
	//content type check required for ugly nextgen gallery REST API handling!
	if($content_type == 'text/html') {
		switch(XTOPT_COMPRESSION_HTML_MODE) {
			case 2:
				header('Vary: Accept-Encoding'); // Handle proxies
				header('Content-Encoding: deflate');
				$content = gzdeflate( $content, XTOPT_COMPRESSION_LEVEL_HTML );
				break;
			case 3:
				header('Vary: Accept-Encoding'); // Handle proxies
				header('Content-Encoding: gzip');
				$content = gzencode( $content, XTOPT_COMPRESSION_LEVEL_HTML );
				break;
			default:
				break;
		}
		if(!XTOPT_COMPRESSION_IS_SERVER_BASED) {
			header('Content-Length: '.strlen($content));
		}
	}
	return $content;
}

function xtopt_end_capture_output($content) {

	if ( ! defined( 'XTOPT_TIME_START_PROCESSING' ) )
		define( 'XTOPT_TIME_START_PROCESSING', microtime( TRUE ) );
	if ( ! defined( 'XTOPT_SIZE_ORIGINAL' ) )
		define( 'XTOPT_SIZE_ORIGINAL', strlen( $content ) );

	//begin optimizations
	$header_vars  = xtopt_capture_current_header_vars();
	$content_type = xtopt_get_current_content_type( $header_vars );
	if ($content_type == 'text/html') {
		$content = xtopt_perform_unify_script_tags($content);
		$scripts = array();
		if (preg_match_all("/<script[^>]*>([\s\S]*?)<\/script>/i", $content, $scripts)) {
			//scripts contained, processing now
			define('XTOPT_NUM_REORDERED_SCRIPTS', count($scripts[0]));
			$conditionals = array();
			$content = xtopt_perform_replace_scripts($content, $scripts, $conditionals);
			$content = xtopt_perform_inline_small_scripts($content, $scripts, $conditionals);
			$content = xtopt_perform_compress_plugin_scripts($content, $scripts);
			$content = xtopt_perform_cleanup_scripts($content, $scripts, $conditionals);
		}else{
			define('XTOPT_NUM_REORDERED_SCRIPTS', 0);
			define('XTOPT_NUM_CONDITIONALS_CONTAINED', 0);
			define('XTOPT_NUM_CONDITIONALS_REMOVED', 0);
			define('XTOPT_NUM_NUM_SCRIPTS_INLINED', 0);
		}
		//css handling
		$content = xtopt_perform_compress_css($content);

		$content = xtopt_perform_strip_whitespaces($content);
		$content = xtopt_perform_html5_ie_compat($content);
		$content = xtopt_perform_replace_wp_compressor($content);

	}else{
		define('XTOPT_NUM_REORDERED_SCRIPTS', 0);
		define('XTOPT_NUM_CONDITIONALS_CONTAINED', 0);
		define('XTOPT_NUM_CONDITIONALS_REMOVED', 0);
		define('XTOPT_NUM_NUM_SCRIPTS_INLINED', 0);
	}
	//end optimizations

	define('XTOPT_TIME_START_SENDING', microtime(true));
	$content = xtopt_perform_statistics($content);

	$content = xtopt_perform_compression($content, $content_type);
	return $content;
}