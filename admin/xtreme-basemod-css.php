<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

require_once XF_ADMIN_DIR . '/writer.php';

class Xtreme_Basemod_CSS {

	protected $css;

	function  __construct() {
		$this->options = get_option(XF_OPTIONS);
		//no longer using direct XF_BLOG_ID
		$this->dir = XF_ABS_OUTPUT_DIR_THEME_BASED;
		$this->layouts = get_option(XF_LAYOUTS, array());
	}

	function basemod_css(){
		$this->css = '';
		/** @media all **/
		$this->css .= "@media all {\n";
		$this->css .= "	.ym-ie-clearing { display: none; }\n";
		$this->css .= "\n";
		$this->css .= "	/* (en) reset font size for all elements to standard (16 Pixel) */\n";
		$this->css .= "	/* (de) Alle Schriftgrößen auf Standardgröße (16 Pixel) zurücksetzen */\n";
		$this->css .= "	html * { font-size: 100.01%; }\n";
		$this->css .= "\n";
		$this->css .= "	/**\n  * (en) reset monospaced elements to font size 16px in all browsers\n";
		$this->css .= "	* (de) Schriftgröße von monospaced Elemente in allen Browsern auf 16 Pixel setzen\n  *\n";
		$this->css .= "	* @see: http://webkit.org/blog/67/strange-medium/\n  */\n";
		$this->css .= "	textarea, pre, code, kbd, samp, var, tt {\n";
		$this->css .= "		font-family: Consolas, \"Lucida Console\", \"Andale Mono\", \"Bitstream Vera Sans Mono\", \"Courier New\", Courier;\n";
		$this->css .= "	}\n";
		$this->css .= "\n";
		$this->css .= "	body {\n";
		$this->css .= "		font-size: " . $this->options['xc_general']['bodyfontsize']['value'] .$this->options['xc_general']['bodyfontsize']['unit']. ";\n";
		$this->css .= "	}\n";
		$this->css .= "\n";
		$this->css .= "}\n";
		/** @media screen, projection **/
		$this->css .= "@media screen, projection {\n";
		$this->css .= "	body {\n";
		$this->css .= "		overflow-y: scroll;\n";
		$this->css .= "		text-align: center;\n";
		$this->css .= "	}\n";

		$this->css .= "	/* Layout:Width, Background, Border | Layout:Breite, Hintergrund, Rahmen */\n";
		$this->css .= "	.ym-wrapper { text-align: left;";
		switch((int) $this->options['xc_general']['layoutalign']['value']) {
			case 0:
				$this->css .= "margin: 0;";
				break;
			case 1:
				$this->css .= "margin: 0 auto;";
				break;
			case 2:
				$this->css .= "margin: 0 0 0 auto;";
				break;
		}
		
		//fixed layout
		if( (int) $this->options['xc_general']['layout']['value'] === 0) {
			//fullpagelayout
			if( (int) $this->options['xc_general']['appearance']['value'] === 1) {
				$this->css .= " width: " . $this->options['xc_general']['width']['value'] . $this->options['xc_general']['width']['unit'].";";
				$this->css .= " }\n";
				$this->css .= "	.xf-fullpage { ";
				$this->css .= "min-width: " . $this->options['xc_general']['width']['value'] . $this->options['xc_general']['width']['unit'].";";
				$this->css .= " }\n";
				$this->css .= " /* fallback for missing media queries support */\n";
				$this->css .= " body > header, body > nav, body > main, body > footer {\n";
				$this->css .= "min-width: 760px;";
				$this->css .= " }\n";
			} else {
				$this->css .= " width: " . $this->options['xc_general']['width']['value'] . $this->options['xc_general']['width']['unit'].";";
				$this->css .= " }\n";
			}
			//flexible
		} else {
			//fullpagelayout
			if( (int) $this->options['xc_general']['appearance']['value'] === 1) {
				$this->css .= " max-width: " . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'].";";
				$this->css .= "  }\n";
  				/* fallback for missing media queries support*/
				$this->css .= " body > header, body > nav, body > main, body > footer {\n";
				$this->css .= "min-width: 760px;";
				$this->css .= " }\n";
				$this->css .= "	.xf-container { ";
				$this->css .= "min-width: " . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'].";";
				$this->css .= " }\n";

			} else {
				//bloglayout
				$this->css .= " min-width: " . $this->options['xc_general']['minwidth']['value'] . $this->options['xc_general']['minwidth']['unit'] .";";
				$this->css .= " max-width: " . $this->options['xc_general']['maxwidth']['value'] . $this->options['xc_general']['maxwidth']['unit'] .";";
				$this->css .= "  }\n";
			}
		}

		$this->css .= "\n";
		//$this->css .= " .header_title, .header_widget, .siteinfo_widget, .siteinfo_copyright {margin: 0; padding: 0;}\n";

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
			$w = $this->options['xc_header']['colwidth']['value'].$this->options['xc_header']['colwidth']['unit'];
			//$this->css .= "  .header_widget { float: ".$hw."; width: " . $w . "}\n";
			//$this->css .= "  .header_title { float: none; margin-".$hw.": ".$w."}\n";
			$this->css .= "	#header .ym-col1{ float: ".$hfloat."; width: " . $w . " }\n";
			$this->css .= "	#header .ym-col3 { float: none; margin-".$hfloat.": ".$w."; margin-" . $hmargin .": 0 }\n";

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
			$s = $this->options['xc_siteinfo']['colwidth']['value'].$this->options['xc_siteinfo']['colwidth']['unit'];
			$this->css .= "	#siteinfo .ym-col1{ float: ".$sfloat."; width: " . $s . "}\n";
			$this->css .= "	#siteinfo .ym-col3 { float: none; margin-".$sfloat.": ".$s."; margin-" . $smargin .": 0}\n";
		}
		$this->css .= "	/* xf-container are the toplevel containers */\n";
		$this->css .= "	.xf-container {\n";
		$this->css .= "		clear: both;\n";
		$this->css .= "		margin: 0;\n";
		//$this->css .= "    overflow: hidden;\n";
		$this->css .= "	}\n";
		$this->css .= "\n";

		$data = array();
		foreach((array)$this->layouts as $layout => $val) {
			$data = $val;
			$name = $layout;
			$this->css .= $this->_calculate_columns($data, $name);
		}
		$this->css .= "	.ym-cbox-left, .ym-cbox-right, .ym-cbox { position: relative; }\n";
		$this->css .= "	.one .ym-cbox { padding: 0; width: 100%; }\n";		
		if (!xtreme_is_layout_2()) {
			$this->css .= "	.two-right .ym-cbox-left, .two-left .ym-cbox, .three-left-right .ym-cbox-left, .three-right-right .ym-cbox-left, .three-right-right .ym-cbox-right, .three-left-left .ym-cbox { padding: 0 0 0 20px }\n";
			$this->css .= "	.two-right .ym-cbox, .two-left .ym-cbox-left, .three-left-right .ym-cbox-right, .three-right-right .ym-cbox, .three-left-left .ym-cbox-left, .three-left-left .ym-cbox-right { padding: 0 20px 0 0 }\n";
			$this->css .= "	.three-left-right .ym-cbox { padding: 0 20px 0 20px }\n";
		} else {
			$this->css .= "	.two-right .ym-cbox-left, .two-left .ym-cbox, .three-left-right .ym-cbox-right, .three-right-right .ym-cbox-left, .three-left-left .ym-cbox { padding: 0 20px 0 0 }\n";
			$this->css .= "	.two-right .ym-cbox, .two-left .ym-cbox-left, .three-left-right .ym-cbox ,.three-right-right .ym-cbox, .three-left-left .ym-cbox-left { padding: 0 0 0 20px }\n";
			$this->css .= "	.three-left-right .ym-cbox-left, .three-right-right .ym-cbox-right, .three-left-left .ym-cbox-right { padding: 0 20px 0 20px }\n";
		}

		$this->css .= "}\n";
		/** end @medis screen, projection **/
		/** @media print **/
		$this->css .= "@media print {\n";
		$arr = array();
		if ( (int) $this->options['xc_print']['byline']['value'] === 0) {
			$arr[] = '.byline, .entry-meta';
		}
		if ( (int) $this->options['xc_print']['comments']['value'] === 0) {
			$arr[] = '#comments';
		}
		if ( (int) $this->options['xc_print']['authorbox']['value'] === 0) {
			$arr[] = '#authorbox';
		}

		$str = "";
		foreach ( $arr as $a) {
			$str .= $a . ',';
		}
		$out = "";
		if (count($arr) > 0) {
			$out = substr($str, 0, -1);
			$out = "	" . $out . " { display: none }\n";
		}
		$u = "";
		if ((int) $this->options['xc_print']['url_output']['value'] === 1) {
			$u .= "	a[href]:after {\n";
			$u .= "		content:\" <URL:\"attr(href)\">\";\n";
			$u .= "		color:#444;\n";
			$u .= "		background:inherit;\n";
			$u .= "		font-style:italic;\n";
			$u .= "		font-size:10pt;\n";
			$u .= "	}\n";
		}
		$this->css .= $out . $u;
		$this->css .= "}\n";

		return $this->css;
	}

	function _calculate_columns($data, $name) {
		$this->content = "";
		$this->selector = "." .$name. " #main";
		$this->textalign = array('left', 'center', 'right');
		$this->is_layout2 = xtreme_is_layout_2();
		switch ($data['columnlayout']['value']) {
			// 1column
			case 0:
				if ($this->is_layout2) {
					$this->content .= "\t" . $this->selector . " .ym-col1{ margin-right: 0; margin-left: 0; width: 100%;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
				} else {
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: 0; margin-left: 0; width: 100%;";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
			// 2columns right
			case 1:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value'] . $data['layout_2_col1width']['unit'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-left: " . $col1_width  . "; margin-right: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col3txtalign']['value']] . " }\n";
				} else {
					$col1_width = $data['col1width']['value'] . $data['col1width']['unit'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: right;";
					$this->content .= " text-align: " . $this->textalign[$data['col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: " . $col1_width  . "; margin-left: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
			// 2columns left
			case 2:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value'] . $data['layout_2_col1width']['unit'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: right;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-left: 0; margin-right: " . $col1_width  . ";";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col3txtalign']['value']] . " }\n";
				} else {
					$col1_width = $data['col1width']['value'] . $data['col1width']['unit'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: 0; margin-left: " . $col1_width  . ";";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
			// 3columns left/right
			//Scheiße, was solls. wir lassen nur % zu, da gibt es keine Probleme
			case 3:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value'] . $data['layout_2_col1width']['unit'];
					$col2_width = $data['layout_2_col2width']['value'] . $data['layout_2_col2width']['unit'];
					$neg_margin = ($data['layout_2_col1width']['value'] + $data['layout_2_col2width']['value']) . $data['layout_2_col1width']['unit'];
					//fixed layout
					// ACHTUNG!!! das padding von (ehemals) yamlpage muß auch noch berücksichtigt werden, wir haben aber keinen wert.
					//if( (int) $this->options['xc_general']['layout']['value'] === 0) {
						//$full =  $this->options['xc_general']['width']['value'];
						//$width = $full-$data['layout_2_col1width']['value']-$data['layout_2_col2width']['value']."px";
					//} else {
					//flexible layout
						$width = (100-$data['layout_2_col1width']['value']-$data['layout_2_col2width']['value'])."%";
					//}
					$this->content .= "\t" . $this->selector . " { width: 100%; float: left }\n";
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: left; margin-left: ".$col2_width .";";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . "; float: left; margin-left: -". $neg_margin .";";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 {float:right; width: " . $width  . ";margin-right: 0; margin-left: -5px;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col3txtalign']['value']] . " }\n";
				} else {
					$col1_width = $data['col1width']['value'] . $data['col1width']['unit'];
					$col2_width = $data['col2width']['value'] . $data['col2width']['unit'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . "; float: right;";
					$this->content .= " text-align: " . $this->textalign[$data['col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . "; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: " . $col1_width  . "; margin-left: " . $col2_width  . ";";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
			// 3columns right/right
			case 4:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value'];
					$col2_width = $data['layout_2_col2width']['value'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . $data['layout_2_col1width']['unit'] . "; margin: 0; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . $data['layout_2_col2width']['unit'] . "; float: left; margin: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-left: " . ($col1_width + $col2_width) . $data['layout_2_col1width']['unit'] . "; margin-right: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col3txtalign']['value']] . " }\n";
				} else {
					$col1_width = $data['col1width']['value'];
					$col2_width = $data['col2width']['value'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . $data['col1width']['unit'] . "; float: right;";
					$this->content .= " text-align: " . $this->textalign[$data['col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . $data['col2width']['unit'] . "; float: right;";
					$this->content .= " text-align: " . $this->textalign[$data['col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: " . ($col1_width + $col2_width) . $data['col1width']['unit'] . "; margin-left: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
			// 3columns left/left
			case 5:
				if ($this->is_layout2) {
					$col1_width = $data['layout_2_col1width']['value'];
					$col2_width = $data['layout_2_col2width']['value'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . $data['layout_2_col1width']['unit'] . "; float: right; margin: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . $data['layout_2_col2width']['unit'] . "; float: right; margin: 0;";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-left: 0; margin-right: " . ($col1_width + $col2_width) . $data['layout_2_col1width']['unit'] . ";";
					$this->content .= " text-align: " . $this->textalign[$data['layout_2_col3txtalign']['value']] . " }\n";
				} else {
					$col1_width = $data['col1width']['value'];
					$col2_width = $data['col2width']['value'];
					$this->content .= "\t" . $this->selector . " .ym-col1{ width: " . $col1_width . $data['col1width']['unit'] . "; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['col1txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col2 { width: " . $col2_width . $data['col2width']['unit'] . "; float: left;";
					$this->content .= " text-align: " . $this->textalign[$data['col2txtalign']['value']] . " }\n";
					$this->content .= "\t" . $this->selector . " .ym-col3 { margin-right: 0; margin-left: " . ($col1_width + $col2_width) . $data['col1width']['unit'] . ";";
					$this->content .= " text-align: " . $this->textalign[$data['col3txtalign']['value']] . " }\n";
				}
				break;
		}

		return $this->content;
	}

	function write(){
		$writer = new xtreme_file_writer();
		$writer->write_file('xtreme_basemod.css', $this->basemod_css(), $this->dir);
	}
}
