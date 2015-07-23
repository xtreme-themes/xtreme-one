<?php 

	if (!isset($_REQUEST['ver']) || !isset($_REQUEST['load']) || !isset($_REQUEST['c'])) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		echo "Your script joining request is malformed, please contact your theme vendor.";
		exit();
	}
	
	$in_mode 		= (int)$_REQUEST['c'];
	$in_md5			= (string)$_REQUEST['ver'];
	$in_css			= (string)$_REQUEST['load'];
	$in_level		= isset( $_REQUEST[ 'l' ] ) ? (int)$_REQUEST['l'] : 1;
	$script			= '';
	
	$file = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/'.$in_css;

	if(!file_exists($file))  {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		echo "You did not request valid stylesheet.";
		exit();
	}
	
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (md5(filemtime($file)) == $in_md5)) {
		header('Status: 304 Not Modified');
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
	
	define('XF_COMPRESSION_LEVEL', max(1, min(9,$in_level)));
	
	//adjustment for xtreme stylesheets
	if (stripos($file, 'xtreme-one') !== false) {
		$script = str_replace('../../../','../',file_get_contents($file));
	}else {
		//harder replacement for images to do
		$script = file_get_contents($file);
		$prepend = '../../../../../'.dirname($in_css).'/';
		if(preg_match_all("/url\(([^)]+)\)/", $script, $images)) {
			//adjust them now
			for($i=0; $i<count($images); $i++) {
				$new = $prepend.trim($images[1][$i],"'\"");
				list($new, $crap) = explode('?', $new);
				$script = str_replace($images[0][$i], 'url('.$new.')', $script);
			}
		}
		$script = str_replace('; ',';',str_replace(' }','}',str_replace('{ ','{',str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$script)))));
	}

	// seconds, minutes, hours, days
	$expires = 60*60*24*14;
	header( 'Expires: '. gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
	header( 'Content-Type: text/css; charset=UTF-8' );

	if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && ($in_mode > 1) ) {
		
		//could be compressed ?
		if ( ($in_mode === 2) && ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') ) && function_exists('gzdeflate') ) {
			header('Content-Encoding: deflate');
			$script = gzdeflate( $script, XF_COMPRESSION_LEVEL );
		} elseif ( ($in_mode === 3) && ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ) && function_exists('gzencode') ) {
			header('Content-Encoding: gzip');
			$script = gzencode( $script, XF_COMPRESSION_LEVEL );
		}
		
	}
	
	header('Content-Length: '.strlen($script));
	echo $script;