<?php

class Xtreme_Script_Collection {
	
	function __construct($ext, $prepend = '') {

		$this->dep_tree = array(
			'jquery' 					=> array(),
			'yaml-focusfix'			=> array(),
			'xtreme-superfish'			=> array('jquery'),
			'xtreme-mobilefish'			=> array('jquery'),
			'xtreme-syncheight' 		=> array('jquery'),
			'xtreme-accessible-tabs'		=> array('jquery','xtreme-syncheight'),
			'xtreme-widget-groups'		=> array('jquery','xtreme-syncheight','xtreme-accessible-tabs'),
			'xtreme-jqfancy-slider'		=> array('jquery'),
			'xtreme-coin-slider'		=> array('jquery'),
			'xtreme-carousel'			=> array('jquery'),
			'xtreme-featurelist'		=> array('jquery'),
			'xtreme-flexslider'			=> array('jquery'),
										
			//conditionally by theme support
			'xtreme-fancybox-easing'	=> array('jquery'),
			'xtreme-fancybox-wheel'		=> array('jquery'),
			'xtreme-fancybox'			=> array('jquery','xtreme-fancybox-easing','xtreme-fancybox-wheel'),
			'xtreme-lazy-gravatars'		=> array('jquery'),
			
			'xtreme-low-barrier'		=> array('jquery')
		);
		
		$this->script_files = array(
			'yaml-focusfix'			=> $prepend.'/yaml-focusfix.js',
			'xtreme-superfish' 			=> $prepend.'/jquery/superfish/superfish'.$ext,
			'xtreme-mobilefish'			=> $prepend.'/jquery/superfish/mobilefish'.$ext,
			'xtreme-syncheight'			=> $prepend.'/jquery/syncheight/jquery.syncheight'.$ext,
			'xtreme-accessible-tabs'		=> $prepend.'/jquery/accessible-tabs/jquery.tabs'.$ext,
			'xtreme-widget-groups'		=> $prepend.'/jquery/xtreme-one/jquery.widget.groups'.$ext,
			'xtreme-jqfancy-slider'		=> $prepend.'/jquery/jqFancySlider/jqFancyTransitions.1.8'.$ext,
			'xtreme-coin-slider'		=> $prepend.'/jquery/coinSlider/coin-slider'.$ext,
			'xtreme-carousel'			=> $prepend.'/jquery/carouFredSel/jquery.carouFredSel-5.5.0'.$ext,			
			'xtreme-featurelist'		=> $prepend.'/jquery/featurelist/jquery.featureList-1.0.1'.$ext,
			'xtreme-fancybox-easing'		=> $prepend.'/jquery/fancybox/jquery.easing-1.3'.$ext,
			'xtreme-fancybox-wheel'		=> $prepend.'/jquery/fancybox/jquery.mousewheel-3.0.6'.$ext,
			'xtreme-fancybox'			=> $prepend.'/jquery/fancybox/jquery.fancybox-1.3.4'.$ext,
			'xtreme-lazy-gravatars'		=> $prepend.'/jquery/gravatars/jquery.lazy-gravatars'.$ext,
			'xtreme-flexslider'			=> $prepend.'/jquery/flexslider/jquery.flexslider'.$ext,
			'xtreme-low-barrier'		=> $prepend.'/jquery/xtreme-one/jquery.low-barrier'.$ext,
		);
	}
}

if(!defined('XF_VERSION')) {

	define('XF_FRONT_SCRIPTS', '.min.js');
	
	if (!isset($_REQUEST['ver']) || !(isset($_REQUEST['load']) || isset($_REQUEST['plugin'])) || !isset($_REQUEST['c'])) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		echo "Your script joining request is malformed, please contact your theme vendor.";
		exit();
	}

	$in_mode 	= (int)$_REQUEST['c'];
	$in_level	= isset($_REQUEST['l']) ? (int)$_REQUEST['l'] : 6;
	$in_md5		= (string)$_REQUEST['ver'];
	$in_js		= isset($_REQUEST['load']) ? (string)$_REQUEST['load'] : '';
	$in_js_plug = isset($_REQUEST['plugin']) ? (string)$_REQUEST['plugin'] : '';
	$script 	= '';

	if(isset($_REQUEST['load'])) {
		$catalog = new Xtreme_Script_Collection(XF_FRONT_SCRIPTS);

		$avail = array_keys($catalog->script_files);
		$to_join = explode(',', $in_js);
		$intersect = array_intersect($avail, $to_join);
		$remain = array();
		$files = array();
		$times = array();
		$i=0;
		foreach($intersect as $token) {
			$file = dirname(__FILE__).$catalog->script_files[$token];
			if(file_exists($file)) {
				$remain[$i] = $token;
				$files[$i] = $file;
				$times[$i] = filemtime($file);
				$i++;
			}
		}

		if (count($remain) == 0) {
			header('Status: 403 Forbidden');
			header('HTTP/1.1 403 Forbidden');
			echo "You did not request valid scripts.";
			exit();
		}


		$md5 = md5(implode(',',$remain).'|'.implode(',',$times));
		if ($in_md5 == $md5 && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			header('Status: 304 Not Modified');
			header('HTTP/1.1 304 Not Modified');
			exit();
		}

		foreach($files as $file) {	
			$ct = file_get_contents($file);	
			if ($in_mode >= 1) {
				//remove leading header comments
				if (preg_match("/^\s*\/\*\*/", $ct)) {
					$pos = stripos($ct, "**/");
					if ($pos !== false && $pos > 2) {
						$ct = substr($ct, $pos+3);
					}
				}
			}
			$script .= $ct.";\n";
		}
		
		if ($in_mode == 1) {		
			//simple (and safe) minifications
			$script = preg_replace('/(\/\*[\s\S]*?\*\/)(\r|\n)+/', '', $script);
			$script = preg_replace("/(\n|\r)+/", "\n", $script);
			$script = preg_replace("/ [ ]+/", " ", $script);
			$script = preg_replace("/\t[\t]+/", "\t", $script);
		}
		elseif($in_mode > 1) {
			$script = preg_replace("/(\n|\r)+/", "\n", $script);
		}
		
	} else {
		//plugin based
		$file = dirname(dirname(dirname(dirname(__FILE__)))).'/plugins'.$in_js_plug;
		if (!file_exists($file)) {
			header('Status: 403 Forbidden');
			header('HTTP/1.1 403 Forbidden');
			echo "You did not request valid scripts.";
			exit();
		}
		
		$md5 = md5(filemtime($file));
		if ($in_md5 == $md5 && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			header('Status: 304 Not Modified');
			header('HTTP/1.1 304 Not Modified');
			exit();
		}
		$script = file_get_contents($file);
		
		if ($in_mode == 1) {
			//simple (and safe) minifications
			
			$script = preg_replace('/(\/\*[\s\S]*?\*\/)(\r|\n)+/', '', $script);
			$script = preg_replace("/(\n|\r)+/", "\n", $script);
			$script = preg_replace("/ [ ]+/", " ", $script);
			$script = preg_replace("/\t[\t]+/", "\t", $script);
		}		
	}
	
	define('XF_COMPRESSION_LEVEL', max(1, min(9,$in_level)));

	// seconds, minutes, hours, days
	$expires = 60*60*24*14;
	header( 'Expires: '. gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
	header( 'Content-Type: application/x-javascript; charset=UTF-8' );
			
	if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && ($in_mode > 1) ) {
		
		//could be compressed ?
		if ( ($in_mode === 2) &&( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') ) && function_exists('gzdeflate') ) {
			header('Content-Encoding: deflate');
			$script = gzdeflate( $script, XF_COMPRESSION_LEVEL );
		} elseif ( ($in_mode === 3) && ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ) && function_exists('gzencode') ) {
			header('Content-Encoding: gzip');
			$script = gzencode( $script, XF_COMPRESSION_LEVEL );
		}
		
	}
	header('Content-Length: '.strlen($script));
	echo $script;
}