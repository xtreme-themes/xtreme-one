<?php

if ( ! function_exists ('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once XF_ADMIN_DIR . '/writer.php';

/**
 * @property mixed is_layout2
 * @property mixed selector
 */
class Xtreme_Patch_CSS {

	protected $content;
	protected $css;

	function __construct() {
		$this->options = get_option(XF_OPTIONS, array());
		//no longer using direct XF_BLOG_ID
		$this->dir = XF_ABS_OUTPUT_DIR_THEME_BASED;
		$this->layouts = get_option(XF_LAYOUTS, array());
	}
	
	function patch_css() {
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.min' : '';

		$this->css = '';
		$this->css .= "@import url('../../../yaml/core/iehacks$suffix.css');\n";
		$this->css .= "@media all {\n";
		//YAML-changes
		$this->css .= "  * html .ym-col1, * html .ym-col2, * html .ym-col3 { position: relative; }\n";
		$this->css .= "  html .ym-ie-clearing {\n";
		$this->css .= "    position:static;\n";
		$this->css .= "    display:block;\n";
		$this->css .= "    \clear:both;\n";
		$this->css .= "    width: 100%;\n";
		$this->css .= "    font-size:0px;\n";
		$this->css .= "    margin: -2px 0 -1em 1px;\n";
		$this->css .= "  }\n";
		$this->css .= "  * html .ym-ie-clearing { margin: -2px 0 -1em 0; }\n";
		$this->css .= "  .ym-cbox { margin-bottom:-2px; }\n";
		$this->css .= "  .ym-col3 { position: relative; }\n";
		$this->css .= "  .xf-container, .ym-wbox { zoom: 1; }\n";
		$this->css .= "  * html .xf-container, * html .ym-wbox { width: 100%; wid\\th: auto; }\n";
		$this->css .= "}\n";

		$this->css .= "@media screen, projection\n{\n";
		$this->css .= "  .ym-col1, .ym-col2 { display: inline; }\n";
		$this->css .= "  * html .ym-cbox-left, * html .ym-cbox-right, * html .ym-cbox { word-wrap: break-word; }\n";
		$this->css .= "/* Layout-dependent adjustments | Layout-abhängige Anpassungen --------------------------------------- */\n";
		$this->css .= "/**\n* Bugfix for IE 3-Pixel-Jog Bug\n   *\n   * @bugfix\n   * @affected   IE 5.x/Win, IE6\n   * @css-for    IE 5.x/Win, IE6\n   * @valid      yes\n   */\n";
		$this->css .= " * html .ym-col3 {height: 1%;}        /* Activate hack for 3-Pixel-Jog Bug | Hack für 3-Pixel-Jog Bug aktivieren */\n";

		if ( (int) $this->options['xc_header']['columns']['value'] === 1) {
			switch ( (int) $this->options['xc_header']['position']['value']) {
				case 0:
					$hfloat = 'left';
					$hmargin = 'right';
					break;
				case 1:
					$hfloat = 'right';
					$hmargin = 'left';
					break;

			}
			$w = ($this->options['xc_header']['colwidth']['value']-3).$this->options['xc_header']['colwidth']['unit'];
			$this->css .= "* html #header .ym-col1{ margin-".$hmargin.": -3px; z-index:7 }\n";
			$this->css .= "* html #header .ym-col3 { margin-".$hfloat.": ".$w."; }\n";
			$this->css .= "#header .ym-cbox-left { z-index:7; }\n";
		}

		if ( (int) $this->options['xc_siteinfo']['columns']['value'] === 1) {
			switch ( (int) $this->options['xc_siteinfo']['position']['value']) {
				case 0:
					$sfloat = 'left';
					$smargin = 'right';
					break;
			case 1:
					$sfloat = 'right';
					$smargin = 'left';
					break;
			}
			$w = ($this->options['xc_siteinfo']['colwidth']['value']-3).$this->options['xc_siteinfo']['colwidth']['unit'];
			$this->css .= "* html #siteinfo .ym-col1{ margin-".$smargin.": -3px; }\n";
			$this->css .= "* html #siteinfo .ym-col3 { margin-".$sfloat.": ".$w."; }\n";
		}

		$data = array();
		foreach($this->layouts as $layout => $val) {
			$data = $val;
			$name = $layout;
			$this->css .= $this->_calculate_columns($data, $name);
		}

		if ( (int)$this->options['xc_general']['layout']['value'] == 1) {
			switch ((int)$this->options['xc_general']['appearance']['value']) {
				case 1:
					//fullpagedesign
					$this->css .= "  .ym-wrapper {\n";
					$this->css .= "    max-width: " . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . ";\n";
					$this->css .= "  }\n";
					$this->css .= "  .xf-container {\n";
					$this->css .= "    min-width: " . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . ";\n";
					$this->css .= "  }\n";
					break;
				case 0:
					//blogdesign
					$this->css .= "  .ym-wrapper {\n";
					$this->css .= "    min-width: " . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . ";\n";
					$this->css .= "    max-width: " . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . ";\n";
					$this->css .= "  }\n";
					break;
			}
		//pixel feste breite
		} elseif($this->options['xc_general']['layout']['value'] == 0 ) {
			$this->css .= "  .ym-wrapper {\n";
			$this->css .= "    width: " . $this->options['xc_general']['width']['value'] . $this->options['xc_general']['width']['unit'] . ";\n";
			$this->css .= "  }\n";
		}

		if ((int)$this->options['xc_general']['layout']['value'] === 1 ) {
			switch ((int)$this->options['xc_general']['appearance']['value']) {
				case 1:
					//fullpagedesign
					$this->css .= "  * html div.ym-wrapper {\n";
					$this->css .= "    width: " . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] .";\n";
					$this->css .= "    width: expression((document.documentElement && document.documentElement.clientHeight) ?";
					$this->css .= " ((document.documentElement.clientWidth < " . $this->options['xc_general']['minwidth']['value'] . ") ? \"". $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . "\" :";
					$this->css .= " ((document.documentElement.clientWidth > (" . $this->options['xc_general']['maxwidth']['value'] . " * 16 * (parseInt(this.parentNode.currentStyle.fontSize) / 100))) ?";
					$this->css .= " \"" . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . "\" : \"auto\" )) : ((document.body.clientWidth < " . $this->options['xc_general']['minwidth']['value'] . ") ? ";
					$this->css .= " \"" . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . "\" :";
					$this->css .= " ((document.body.clientWidth > (" . $this->options['xc_general']['maxwidth']['value'] . " * 16 * (parseInt(this.parentNode.currentStyle.fontSize) / 100))) ? ";
					$this->css .= " \"" . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . "\" : \"auto\" )));\n  }\n";
					break;
				case 0:
					//blogdesign
					$this->css .= "  * html div.ym-wrapper {\n";
					$this->css .= "    width: auto;\n";
					$this->css .= "    width: expression((document.documentElement && document.documentElement.clientHeight) ?";
					$this->css .= " (((document.documentElement.clientWidth > (" . $this->options['xc_general']['maxwidth']['value'] . " * ";
					$this->css .=  $this->_get_bodyfontsize_in_px($this->options['xc_general']['bodyfontsize']['value']). " * (parseInt(this.parentNode.currentStyle.fontSize) / 100))) ?";
					$this->css .= " \"" . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . "\" : \"auto\" )) : (((document.body.clientWidth >";
					$this->css .= " (" . $this->options['xc_general']['maxwidth']['value'] . " * 12 * (parseInt(this.parentNode.currentStyle.fontSize) / 100))) ? \"" . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] . "\" : \"auto\" )));\n  }\n";

					$this->css .= "  * html .xf-container {\n    width: auto;\n";
					$this->css .= "    width: expression((document.documentElement && document.documentElement.clientHeight) ?";
					$this->css .= " ((document.documentElement.clientWidth < " . $this->options['xc_general']['minwidth']['value'] . ") ? \"" . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . "\" : \"auto\") :";
					$this->css .= " ((document.body.clientWidth < " . $this->options['xc_general']['minwidth']['value'] . ") ? \"" . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] . "\" : \"auto\"));\n  }\n";
					break;
			}
		} elseif((int)$this->options['xc_general']['layout']['value'] === 0) {
			$this->css .= " * html .ym-wrapper { width: " . $this->options['xc_general']['width']['value'] . $this->options['xc_general']['width']['unit'] . ";}\n";
		}
		
		$this->css .= " .ym-wrapper { text-align: left;";
		switch((int) $this->options['xc_general']['layoutalign']['value']) {
			case 0:
				$this->css .= "margin: 0 auto 0 0;";
				break;
			case 1:
				$this->css .= "margin: 0 auto;";
				break;
			case 2:
				$this->css .= "margin: 0 0 0 auto;";
				break;
		}
		$this->css .= "}\n";
		
		$this->css .= "/**\n";
		$this->css .= "* Containing floats adjustment and stability fixes for Internet Explorer\n";
		$this->css .= "*\n";
		$this->css .= "* @workaround\n";
		$this->css .= "* @affected IE 5.x/Win, IE6, IE7\n";
		$this->css .= "* @css-for IE 5.x/Win, IE6, IE7\n";
		$this->css .= "* @valid no\n";
		$this->css .= "*/\n";
		$this->css .= "* html .jquery_tabs .content { z-index:-1 }\n";
		$this->css .= "* html .jquery_tabs { zoom:0; width:auto; position:relative }\n";
		$this->css .= "*+html .jquery_tabs { zoom:0; width:auto }\n";
		$this->css .= ".jquery_tabs * { zoom:0 }\n";

		/* IE6 Fancybox */

		$this->css .= "	.fancybox-ie6 #fancybox-close { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_close.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-left-ico { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_nav_left.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-right-ico { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_nav_right.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-title-over { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_title_over.png', sizingMethod='scale'); zoom: 1; }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-title-float-left { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_title_left.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-title-float-main { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_title_main.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-title-float-right { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_title_right.png', sizingMethod='scale'); }\n";
		$this->css .= "	.fancybox-ie6 #fancybox-bg-w, .fancybox-ie6 #fancybox-bg-e, .fancybox-ie6 #fancybox-left, .fancybox-ie6 #fancybox-right, #fancybox-hide-sel-frame {\n";
		$this->css .= "		height: expression(this.parentNode.clientHeight + 'px');\n";
		$this->css .= "	}\n";
		$this->css .= "	#fancybox-loading.fancybox-ie6 {\n";
		$this->css .= "		position: absolute; margin-top: 0;\n";
		$this->css .= "		top: expression( (-20 + (document.documentElement.clientHeight ? document.documentElement.clientHeight/2 : document.body.clientHeight/2 ) + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop )) + 'px');\n";
		$this->css .= "	}\n";
		$this->css .= "	#fancybox-loading.fancybox-ie6 div	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../../../images/fancy_loading.png', sizingMethod='scale'); }\n";
		
		/* IE rgba() hack */
		$this->css .= "	.flex-caption {background:none; -ms-filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#4C000000,endColorstr=#4C000000);";
		$this->css .= " filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#4C000000,endColorstr=#4C000000); zoom: 1 }\n";
		$this->css .= "	.flexslider, .flexslider .slides, .flex-container { zoom: 1 }\n";
		$this->css .= "	.flex-control-nav li { zoom: 1; display: inline }\n";
		
		$this->css .= "}\n/* end @media screen, projection*/\n";
		$this->css .= "@media print {\n";
		$this->css .= "  .ym-col3 { height: 1%; }\n";
		$this->css .= "}\n";//end media print

		return $this->css;
	}

	function _calculate_columns($data, $name) {
		$this->content = "";
		$this->selector = " * html ." .$name. " #main";
		$this->is_layout2 = xtreme_is_layout_2();
		switch ($data['columnlayout']['value']) {
			// 1column
			case 0:
				if ($this->is_layout2) {
					$this->content .= $this->selector . " .ym-col1{ margin-right: 0; margin-left: 0; }\n";
				} else {
					$this->content .= $this->selector . " .ym-col3 { margin-right: 0; margin-left: 0; }\n";
				}
				break;
			// 2columns right
			case 1:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value']-3 . $data['layout_2_col1width']['unit'];
					$this->content .= $this->selector . " .ym-col1{ margin-right: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-left: " . $col1_width  . "}\n";
				} else {
					$col1_width = $data['col1width']['value']-3 . $data['col1width']['unit'];
					$this->content .= $this->selector . " .ym-col1{ margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-right: " . $col1_width  . "}\n";
				}
				break;
			// 2columns left
			case 2:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value']-3 . $data['layout_2_col1width']['unit'];
					$this->content .= $this->selector . " .ym-col1{ margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-right: " . $col1_width  . "; }\n";
				} else {
					$col1_width = $data['col1width']['value']-3 . $data['col1width']['unit'];
					$this->content .= $this->selector . " .ym-col1{ margin-right: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-left: " . $col1_width  . "; }\n";
		
				}
				break;
			// 3columns left/right
			case 3:
				if ($this->is_layout2) {
					$this->content .= "";
				} else {
					$col1_width = $data['col1width']['value']-3 . $data['col1width']['unit'];
					$col2_width = $data['col2width']['value']-3 . $data['col2width']['unit'];
					$this->content .= $this->selector . " .ym-col1{ margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col2 { margin-right: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-right: " . $col1_width  . "; margin-left: " . $col2_width  . "; }\n";
				}
				break;
			// 3columns right/right
			case 4:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value']-3;
					$col2_width = $data['layout_2_col2width']['value'];
					$this->content .= $this->selector . " .ym-col2 { margin-right: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-left: " . ($col1_width + $col2_width) . $data['layout_2_col1width']['unit'] . "; }\n";
				} else {
					$col1_width = $data['col1width']['value']-3;
					$col2_width = $data['col2width']['value']-3;
					$this->content .= $this->selector . " .ym-col1{ margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col2 { margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-right: " . ($col1_width + $col2_width) . $data['col1width']['unit'] . "; }\n";
				}
				break;
			// 3columns left/left
			case 5:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value']-3;
					$col2_width = $data['layout_2_col2width']['value'];
					$this->content .= $this->selector . " .ym-col2 { margin-left: -3px; }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-right: " . ($col1_width + $col2_width) . $data['layout_2_col1width']['unit'] . "; }\n";
				} else {
					$col1_width = $data['col1width']['value'];
					$col2_width = $data['col2width']['value'];
					$this->content .= $this->selector . " .ym-col1{ margin-right: -3px }\n";
					$this->content .= $this->selector . " .ym-col2 { margin-right: -3px }\n";
					$this->content .= $this->selector . " .ym-col3 { margin-left: " . ($col1_width + $col2_width) . $data['col1width']['unit'] . "; }\n";
				}
				break;
			}
		return $this->content;
	}

	function _get_bodyfontsize_in_px($value) {
		$newvalue = round((((float)$value * 16)/100),1);
		return $newvalue;
	}

	function write(){
		$writer = new xtreme_file_writer();
		$writer->write_file('xtreme_patch.css', $this->patch_css(), $this->dir);
	}
}
