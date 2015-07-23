<?php

$md5 = false; $md5_current = md5(dirname(dirname(__FILE__)));
$url = false;

if(isset($_REQUEST['data'])) {
	$data = @base64_decode($_REQUEST['data']);
	list($md5, $url) = @explode('|', $data);
	$url = @rawurldecode($url);
	$url = str_replace('&amp;', '&', $url);
}

if($md5 != $md5_current)  {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	echo "You did not request valid gravatar.";
	exit();
}
	
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$if_modified_since = strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
	$now = time();
	if ($now - $if_modified_since < 60*60*24*7) {
		header('Status: 304 Not Modified');
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
}

//    Output handler
function output_handler($img) {
	global $url;
	$expires = 60*60*24*7;
	header( 'Expires: '. gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
	header( 'Etag: "'.md5($url).'"' );
//	header( 'Content-Disposition: Attachment;filename='.md5($url).'.png' ); 
	header( 'Content-type: image/png' );
    header(' Content-Length: ' . strlen($img));
    return $img;
}

// find size of any
$size = false;
if (preg_match('/s=(\d+)/', $url, $sizeparts)) {
	$size = (int)$sizeparts[1];
}

$im = @imagecreatefrompng($url);
if (!$im) { $im = @imagecreatefromjpeg($url); }
if (!$im) { $im = @imagecreatefromgif($url); }
if (!$im) {
	header('Status: 404 Not Found');
	header('HTTP/1.1 404 Not Found');
	exit();
}

//need to be resampled ?
if($size !== false) {
	if (imagesx($im) != $size) {
		$image_p = @imagecreatetruecolor($size, $size);
		@imagecopyresampled($image_p, $im, 0, 0, 0, 0, $size, $size, imagesx($im), imagesy($im));
		@imagedestroy($im);
		$im = $image_p;
		//reduction to 4096 colors
		@imagetruecolortopalette($im, false, 4096);
	}else {
		$size = false;
	}
}

//make it different
//TODO: make it more intelligent at next version, pixel modification is currently visible!
$x = rand(0, imagesx($im));
$y = rand(0, imagesx($im));
$col = @imagecolorat($im, $x, $y);
$col = $col +1;
@imagesetpixel( $im, $x, $y , $col);

ob_start("output_handler");
if($size !== false) 
	@imagepng($im, false, 9, PNG_ALL_FILTERS);
else
	@imagepng($im);
@imagedestroy($im);
ob_end_flush();

