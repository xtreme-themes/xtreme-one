<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once XF_ADMIN_DIR . '/writer.php';

class Xtreme_Editor_Style {

	protected $css;

	function  __construct() {
		$this->options = get_option(XF_OPTIONS);
		$this->dir = XF_ABS_OUTPUT_DIR_THEME_BASED;

	}
	
	function editor_css() {
		$bodyfontsize = $this->options['xc_general']['bodyfontsize']['value'];
		$this->css = "";
		$this->css .= "html .mceContentBody { max-width: 650px }\n";
		$this->css .= "* {\n";
		$this->css .= "	font-size: ".$this->get_bodyfontsize_in_px($bodyfontsize)."px;\n";
		$this->css .= "	font-family: Helvetica, Verdana, Arial, sans-serif;\n";
		$this->css .= "	line-height: 1.5em;\n";
		$this->css .= "	margin: 0;\n";
		$this->css .= "	padding: 0;\n";
		$this->css .= "}\n";
		$this->css .= "body {\n";
		$this->css .= "	font-size: ".$this->get_bodyfontsize_in_px($bodyfontsize)."px;\n";
		$this->css .= "	font-family: Helvetica, Verdana, Arial, sans-serif;\n";
		$this->css .= "	line-height: 1.5em;\n";
		$this->css .= "	color: #444;\n";
		$this->css .= "}\n";
		$this->css .= "h1, h2, h3, h4, h5, h6 {\n";
		$this->css .= "	font-family: Calibri, 'Trebuchet MS', Tahoma, Helvetica, Verdana, sans-serif;\n";
		$this->css .= "	font-weight: normal;\n";
		$this->css .= "	margin: 0;\n";
		$this->css .= "}\n";
		$this->css .= "h1 {\n";
		$this->css .= "	font-size: 2.5em;\n";
		$this->css .= "	line-height: 0.6em;\n";
		$this->css .= "	margin-bottom: 0.6em;\n";
		$this->css .= "}\n";
		$this->css .= "h2 {\n";
		$this->css .= "	font-size: 2em;\n";
		$this->css .= "	line-height: 1em;\n";
		$this->css .= "	margin-bottom: 0.75em;\n";
		$this->css .= "}\n";
		$this->css .= "h3 {\n";
		$this->css .= "	font-size: 1.5em;\n";
		$this->css .= "	line-height: 1em;\n";
		$this->css .= "	margin-bottom: 1em;\n";
		$this->css .= "}\n";
		$this->css .= "h4 {\n";
		$this->css .= "	font-size: 1.334em;\n";
		$this->css .= "	line-height: 1.125em;\n";
		$this->css .= "	margin-bottom: 1.125em;\n";
		$this->css .= "}\n";
		$this->css .= "h5, h6 {\n";
		$this->css .= "	font-size: 1.1667em;\n";
		$this->css .= "	line-height: 1.286em;\n";
		$this->css .= "	margin-bottom: 1.286em;\n";
		$this->css .= "}\n";
		$this->css .= "p, ul, ol, dl, dd, dt, li, blockquote, pre {\n";
		$this->css .= "	font-size: 1em;\n";
		$this->css .= "	line-height: 1.5em;\n";
		$this->css .= "	margin-bottom: 1.5em;\n";
		$this->css .= "}\n";
		$this->css .= "ul ul, ol ul, ol ol, li { margin-bottom: 0 }\n";
		$this->css .= "ul, ol, dl { margin-left: 20px }\n";
		$this->css .= "ul { list-style-type: disc }\n";
		$this->css .= "ul ul { list-style-type: circle }\n";
		$this->css .= "ol { list-style-type: decimal }\n";
		$this->css .= "ol ol { list-style-type: lower-latin }\n";
		$this->css .= "li { margin-left: 20px }\n";
		$this->css .= "dt { font-weight:bold }\n";
		$this->css .= "dd { margin:0 0 1.5em 20px }\n";
		$this->css .= "blockquote, blockquote p, cite, q {\n";
		$this->css .= "	font-family: Georgia, 'Times New Roman', Times, serif;\n";
		$this->css .= "	font-style: italic;\n";
		$this->css .= "}\n";
		$this->css .= "blockquote { margin-left: 20px; margin-right: 20px; color: #666 }\n";
		$this->css .= "pre, code, kbd, tt, samp, var { font-size: 1em }\n";
		$this->css .= "pre, code { color: #000 }\n";
		$this->css .= "kbd, samp, var, tt { color: #666; font-weight: bold }\n";
		$this->css .= "var, dfn { font-style:italic }\n";
		$this->css .= "acronym, abbr {\n";
		$this->css .= "	border-bottom: 1px #aaa dotted;\n";
		$this->css .= "	font-variant: small-caps;\n";
		$this->css .= "	letter-spacing: .07em;\n";
		$this->css .= "}\n";
		$this->css .= "sub, sup { font-size: 0.9166em; line-height: 0 }\n";
		$this->css .= "hr {\n";
		$this->css .= "	color: #fff;\n";
		$this->css .= "	background: transparent;\n";
		$this->css .= "	margin: 0 0 1.5em 0;\n";
		$this->css .= "	padding: 0 0 1.5em 0;\n";
		$this->css .= "	border: 0;\n";
		$this->css .= "	border-bottom: 1px #eee solid\n";
		$this->css .= "}\n";
		$this->css .= "a { color: #333; background: transparent; text-decoration: none }\n";
		$this->css .= "a:visited { color: #036 }\n";
		$this->css .= "a:focus { text-decoration: none; color: #000 }\n";
		$this->css .= "a:hover, a:active { color: #182e7a; text-decoration: none }\n";
		$this->css .= ".info { background: #f8f8f8; color: #666; padding: 10px; margin-bottom: 1.5em; font-size: 0.9167em }\n";
		$this->css .= ".warning { background: #fee; color: #400; border: 1px #844 solid; padding: 10px; margin-bottom: 1.5em }\n";
		$this->css .= ".alignleft {\n";
		$this->css .= "	display: inline;\n";
		$this->css .= "	float: left;\n";
		$this->css .= "	margin-bottom: 1em;\n";
		$this->css .= "	margin-right: 20px;\n";
		$this->css .= "}\n";
		$this->css .= ".alignright {\n";
		$this->css .= "	display: inline;\n";
		$this->css .= "	float: right;\n";
		$this->css .= "	margin-bottom: 1em;\n";
		$this->css .= "	margin-left: 20px;\n";
		$this->css .= "}\n";
		$this->css .= ".aligncenter {\n";
		$this->css .= "	display: block;\n";
		$this->css .= "	margin: 0 auto 1em;\n";
		$this->css .= "	text-align: center;\n";
		$this->css .= "}\n";
 		$this->css .= ".alignnone {\n";
		$this->css .= "	margin: 0 0 1em;\n";
		$this->css .= "}\n";  
 		$this->css .= ".ym-contain-dt {\n";
		$this->css .= "	width: 100%;\n";
		$this->css .= "	display: table;\n";
		$this->css .= "}\n";
		$this->css .= ".ym-full { width: 100% }\n";
		$this->css .= ".fixed { table-layout: fixed }\n";
		$this->css .= "table { width: auto; border-collapse: collapse; margin-bottom: 1.5em; border-top: 1px #888 solid; border-bottom: 1px #888 solid }\n";
		$this->css .= "table caption { text-transform: uppercase }\n";
		$this->css .= "th,td { padding: 0.5em }\n";
		$this->css .= "thead th { color: #000; border-bottom: 1px #800 solid }\n";
		$this->css .= "tbody th { background: #e0e0e0; color: #333 }\n";	
		$this->css .= "tbody th { border-bottom: 1px solid #fff; text-align: left }\n";
		$this->css .= "tbody td { border-bottom: 1px solid #eee }\n";	
 		$this->css .= ".wp-caption {\n";
		$this->css .= "	background: transparent;\n";
		$this->css .= "	border: none;\n";
		$this->css .= "	margin-bottom: 1.5em;\n";
		$this->css .= "	padding: 0;\n";
		$this->css .= "}\n";
		$this->css .= ".wp-caption-text { font-size: 0.9167em }\n";
		
		return $this->css;
	}
	
	function get_bodyfontsize_in_px($value) {
		$newvalue = round((((float)$value * 16)/100),1);
		return $newvalue;
	}

	function write(){
		$writer = new xtreme_file_writer();
		$writer->write_file('editor-style.css', $this->editor_css(), $this->dir);
	}
}