<?php

class Xtreme_Fancybox_Manager {

	function __construct() {
		$support = get_theme_support('xtreme-fancybox');
		$this->compatibility = (array)$support[0]['compatibility'];
		$this->posttypes = (array)$support[0]['posttypes'];
		$this->archives = (array)$support[0]['archives'];
		$this->specials = (array)$support[0]['specials'];
		$this->image_regexp = '/\.(jpg|gif|png|bmp|jpeg)(.*)?$/i';
		if (!is_admin()) {
			add_filter('the_posts', array(&$this, 'on_the_posts'));
		}
	}
	
	function _is_content_affected(&$content) {
		$result = false;
		
		//do we have to support a gallery and have at least one inside?
		if ($this->specials['gallery'] && preg_match('/(.?)\[(gallery)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/', $content)) {
			$result = true;
		}
		//check for nextgen galleries
		global $ngg;
		if (is_object($ngg) && isset($ngg->options['thumbEffect']) && $ngg->options['thumbEffect'] == 'custom') {
			if (isset($this->specials['nggallery']) && $this->specials['nggallery'] && preg_match('/(.?)\[(nggallery)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/', $content)) {
				$result = true;
			}
			if (isset($this->specials['ngalbum']) && $this->specials['ngalbum'] && preg_match('/(.?)\[(album)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/', $content)) {
				$result = true;
			}
	  }
		
		//let's check all anchors.
		$anchors = array();
		preg_match_all('/<a[^>]+>/',$content, $anchors);
		foreach($anchors[0] as $anchor) {
			preg_match('/class="([^"]+)"/',$anchor, $class_term);
			preg_match('/href="([^"]+)"/',$anchor, $href_term);
			preg_match('/rel="([^"]+)"/',$anchor, $rel_term);
			list($cf, $ci) = (count($class_term) != 2 ? array('','') : $class_term);
			$ca = count($class_term) != 2 ? array() : explode(' ', $ci);
			list($hf, $hi) = (count($href_term) != 2 ? array('','') : $href_term);
			list($rf, $ri) = (count($rel_term) != 2 ? array('','') : $rel_term);
			$rr = sanitize_key($ri);
			$hit = false;
			
			if(in_array('fancybox', $this->compatibility) && in_array('fancybox', $ca)) { 
				//fancybox - nothing to do but script required
				$result = true;
			}elseif(in_array('colorbox', $this->compatibility) && stripos($ci, 'colorbox-') !== false) { 
				//colorbox 
				foreach($ca as $class) {
					if(stripos($class, 'colorbox-') !== false) {
						$rr = sanitize_key(str_replace('colorbox', '', $class)).$rr;
					}
				}
				$hit = true;
			}elseif(in_array('highslide', $this->compatibility) && in_array('highslide', $ca)) { 
				//highslide 
				$hit = true;
			}elseif(in_array('thickbox', $this->compatibility) && in_array('thickbox', $ca)) { 
				//thickbox 
				$hit = true;
			}elseif(in_array('shutter', $this->compatibility) && (stripos($ci, 'shutter') !== false)) {
				if(in_array('shutter', $ca) || in_array('shutterset', $ca)) { 
					//shutter simple
					$hit = true;
				}else {
					if(stripos($ci, 'shutterset_') !== false) {
						//shutter complex
						foreach($ca as $class) {
							if(stripos($class, 'shutterset_') !== false) {
								$rr = sanitize_key(str_replace('shutterset', '', $class)).$rr;
							}
						}
						$hit = true;
					}
				}
			}elseif(in_array('lightbox', $this->compatibility) && stripos($ri, 'lightbox') !== false) { 
				//lightbox
				$hit = true;
			}elseif(in_array('prettyphoto', $this->compatibility) && stripos($ri, 'prettyPhoto') !== false) { 
				//pretty photo
				$hit = true;
			}elseif(in_array('shadowbox', $this->compatibility) && stripos($ri, 'shadowbox') !== false) { 
				//shadowbox
				$hit = true;
			}elseif(in_array('autodetect', $this->compatibility) && preg_match($this->image_regexp, $anchor) ) {
				//all anchors with image href
				$hit = true;
			}
			
			if($hit) {
				$result = true;
				$ca[] = 'xf-fancybox';					
				$a = false;
				if($rr!=$ri) {
					$a = str_replace($rf, 'rel="'.$rr.'"', $anchor);
				}
				if (empty($cf)) {
					$a = rtrim(($a !== false ? $a : $anchor), '>').' class="'.implode(' ', $ca).'">';
				}else{
					$a = str_replace($cf, 'class="'.implode(' ', $ca).'"', ($a !== false ? $a : $anchor));
				}					
				$content = str_replace($anchor, $a, $content);
			}
		}
		return $result;
	}
		
	function _is_script_injection_required(&$posts) {
		$result = false;
			
		if (count($posts) == 0) 
			return $result;
				
		//do we have to skip archives?
		if(is_archive() && count($posts) != 0 && !in_array($posts[0]->post_type, $this->archives))
			return $result;
		
		//--- WordPress bugfix: wp_query may not have set the 'post' member yet for front page test!
		global $wp_query;
		$temp = null;
		if (!isset($wp_query->post)) {
			$temp = $wp_query->post = $posts[0];
		}
		//front page too?
		if (is_front_page() && !$this->specials['frontpage']) {
			if (!is_null($temp)) $wp_query->post = null;
			return $result;
		}
		if (!is_null($temp)) $wp_query->post = null;
		//--- End of WordPress bugfix
					
		//patch the supported viewers and detect necessary script injection
		foreach($posts as $post) {
			$result |= $this->_is_content_affected($post->post_content);
		}	
	
		return $result;
	}
	
	function on_the_posts($posts) {
		if($this->_is_script_injection_required($posts)) {
			global $xtreme_script_manager;
			$xtreme_script_manager->ensure_FancyBox();
		}
		return $posts;
	}
			
}

$GLOBALS['xtreme_fancybox_manager'] = new Xtreme_Fancybox_Manager();
