<?php

class xtreme_xml_processor {
	function __construct() {}
	
	function restore_from_xml($version, $filename) {
		if(file_exists($filename)) {
			$xml = file_get_contents($filename);
			return $this->_convert_to_array($version, $xml);
		}
		return 4;
	}
	
	//giving a filename writes the file but don't echo
	//default: false -> produced file content will be sent as download immediately  and exit called
	function backup_as_xml($version, $options, $layouts, $templates, $widget_permissions, $burnings, $sidebars, $widgets, $filename = false) {
		
		$xml   = "\t<".XF_OPTIONS.">\n".$this->_convert_to_xml($options, false, "\t\t")."\t</".XF_OPTIONS.">\n";
		$xml  .= "\t<".XF_LAYOUTS.">\n".$this->_convert_to_xml($layouts, false, "\t\t")."\t</".XF_LAYOUTS.">\n";
		$xml  .= "\t<".XF_TEMPLATES.">\n".$this->_convert_to_xml($templates, false, "\t\t")."\t</".XF_TEMPLATES.">\n";
		$xml  .= "\t<".XF_WIDGET_PERMISSIONS.">\n".$this->_convert_to_xml($widget_permissions, false, "\t\t")."\t</".XF_WIDGET_PERMISSIONS.">\n";		
		$xml  .= "\t<".XF_WIDGET_BURNING_REG.">\n".$this->_convert_to_xml(XF_IS_MAIN_BLOG ? $burnings : array(), false, "\t\t")."\t</".XF_WIDGET_BURNING_REG.">\n";		
		$xml  .= "\t<sidebars-widgets>\n".$this->_convert_to_xml($sidebars, false, "\t\t")."\t</sidebars-widgets>\n";
		$xml  .= "\t<widgets>\n".$this->_convert_to_xml($widgets, true, "\t\t")."\t</widgets>\n";
		
		$xml  = "<?xml version=\"1.0\" encoding=\"".get_option('blog_charset')."\"?>\n"."<xtreme-one-config version=\"".$version."\" theme=\"".XF_ACTIVE_STYLESHEET."\" checksum=\"".md5($xml)."\">\n".$xml."</xtreme-one-config>\n";
		if ($filename) {
			//TODO: to check the file writing to location specified
		}else{
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=xtreme-one-config-'.XF_ACTIVE_STYLESHEET.'-'.date('Y-m-d').'.xml');
			header('Content-Length: '.strlen($xml));
			header('Content-type: text/xml; charset=' .get_option('blog_charset'), true);	
			echo $xml;
			exit();
		}
	}
	
	function _convert_to_array($version, $xml) {
	
		$parser = xml_parser_create();
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $parser, $xml, $tags );
		xml_parser_free( $parser );
	   
		$elements = array();
		$stack = array();
		foreach ( $tags as $tag )
		{
			if ( $tag['type'] == "complete" || $tag['type'] == "open" )
			{
				//restore non-exportable index based
				if (isset($tag['tag']) && preg_match('/^_idx_/',$tag['tag'])) {
					$tag['tag'] = (int)str_replace('_idx_', '', $tag['tag']);
				}
				//restore non-exportable file keys
				if (isset($tag['tag']) && preg_match('/^_php_/',$tag['tag'])){
					$tag['tag'] = str_replace('_php_', '', $tag['tag']).'.php';
				}
				
				$elements[$tag['tag']] = array();
			   
				if ( $tag['type'] == "open" )
				{    # push
					$stack[count($stack)] = &$elements;
					$elements = &$elements[$tag['tag']];
				}
				else{
					if(isset($tag['attributes'])) {
						switch($tag['attributes']['type']){
							case 'bool':
								$elements[$tag['tag']] = (isset( $tag['value'] ) ? ($tag['value'] == "true" ? true : false) : false);
								break;
							case 'integer':
								$elements[$tag['tag']] = (isset( $tag['value'] ) ? (int)$tag['value'] : 0);
								break;
							case 'double':
								$elements[$tag['tag']] = (isset( $tag['value'] ) ? (double)$tag['value'] : 0.0);
								break;
							case 'base64':
								$elements[$tag['tag']] = (isset( $tag['value'] ) ? base64_decode($tag['value']) : '');
								break;
							default:
								if ($tag['attributes']['type'] == 'array' && $tag['attributes']['size'] == 0) {
									$elements[$tag['tag']] = array();
								}else
									$elements[$tag['tag']] = (isset( $tag['value'] ) ? $tag['value'] : '');
						}
					}
					else{
						$elements[$tag['tag']] = (isset( $tag['value'] ) ? $tag['value'] : '');
					}
				}
			}
		   
			if ( $tag['type'] == "close" )
			{    # pop
				$elements = &$stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}		
		if(preg_match('/<xtreme-one-config version="([0-9\.]+)" theme="([A-Za-z0-9\.\-]+)" checksum="([0-9a-f]+)">/', $xml, $h)) {
			$ver = $h[1];
			$theme = $h[2];
			$hash = $h[3];
			$p = preg_split('/(<xtreme-one-config version="[0-9\.]+" theme="[A-Za-z0-9\.\-]+" checksum="[0-9a-f]+">\n|<\/xtreme-one-config>)/', $xml);
			if(version_compare( $ver, $version, '>' )) return 1;
			if ($ver != $version) {
				$elements['xtreme-one-config'][XF_VERSION_FIELD] = $ver;
			}
			if ($theme != XF_ACTIVE_STYLESHEET) return 2;
			if (md5($p[1]) != $hash) return 3;
		}
		else{		
			return 4;
		}
		return $elements['xtreme-one-config'];
	}
	
	function _convert_to_xml($arr, $base64strings, $indent = '') {
		$output = "";
		if (!is_array($arr) || empty($arr)) return $output;
		foreach($arr as $key => $val) {	
			if (is_numeric($key)) $key = "_idx_".$key; // <0 is not allowed
			if (preg_match('/\.php$/',$key)) $key = "_php_".str_replace('.php', '', $key);
			switch (gettype($val)) {
				case "array":
					$output .= $indent."<".htmlspecialchars($key)." type='array' size='".count($val)."'>\n".
					$this->_convert_to_xml($val, $base64strings, $indent."\t").$indent."</".htmlspecialchars($key).">\n"; break;
				case "boolean":
					$output .= $indent."<".htmlspecialchars($key)." type='bool'>".($val?"true":"false").
					"</".htmlspecialchars($key).">\n"; break;
				case "integer":
					$output .= $indent."<".htmlspecialchars($key)." type='integer'>".
					htmlspecialchars($val)."</".htmlspecialchars($key).">\n"; break;
				case "double":
					$output .= $indent."<".htmlspecialchars($key)." type='double'>".
					htmlspecialchars($val)."</".htmlspecialchars($key).">\n"; break;
				case "string":
					if ($base64strings === true && !empty($val)) {
						$b64 = base64_encode($val);
						$output .= $indent."<".htmlspecialchars($key)." type='base64' size='".strlen($b64)."'><![CDATA[".
						$b64."]]></".htmlspecialchars($key).">\n"; break;
					}else{
						$output .= $indent."<".htmlspecialchars($key)." type='string' size='".strlen($val)."'><![CDATA[".
						htmlspecialchars($val)."]]></".htmlspecialchars($key).">\n"; break;
					}
					break;
				default:
					$output .= $indent."<".htmlspecialchars($key)." type='unknown'>".gettype($val).
					"</".htmlspecialchars($key).">\n"; break;
			}
		}
		return $output;
	}	
}